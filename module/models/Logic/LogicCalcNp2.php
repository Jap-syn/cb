<?php
namespace models\Logic;

use Zend\Db\Adapter\Adapter;
use models\Table\TableSiteTotal;
use models\Table\TableUser;

/**
 * サイト別不払率算出/更新ロジック
 */
class LogicCalcNp2
{
    /**
     * アダプタ
     *
     * @var Adapter
     */
    protected $_adapter = null;

    /**
     * コンストラクタ
     *
     * @param Adapter $adapter アダプタ
     */
    public function __construct(Adapter $adapter)
    {
        $this->_adapter = $adapter;
    }

    /**
     * サイト別不払い率算出
     */
    public function calc()
    {
        $mdlst = new TableSiteTotal($this->_adapter);   // サイト別集計
        $mdlu  = new TableUser($this->_adapter);        // ユーザー

        // データ抽出範囲設定⇒検索
        $today = date('Y-m-d');

        $prm = array(
            ':fr1' => date('Y-m-d', strtotime($today . '  -15 day'))
        ,   ':to1' => date('Y-m-d', strtotime($today . '   -8 day'))
        ,   ':fr2' => date('Y-m-d', strtotime($today . '  -60 day'))
        ,   ':to2' => date('Y-m-d', strtotime($today . '  -31 day'))
        ,   ':fr3' => date('Y-m-d', strtotime($today . ' -120 day'))
        ,   ':to3' => date('Y-m-d', strtotime($today . '  -91 day'))
        ,   ':fr4' => date('Y-m-d', strtotime($today . ' -210 day'))
        ,   ':to4' => date('Y-m-d', strtotime($today . ' -181 day'))
        ,   ':fr5' => date('Y-m-d', strtotime($today . ' -390 day'))
        ,   ':to5' => date('Y-m-d', strtotime($today . ' -361 day'))
        ,   ':fr6' => date('Y-m-d', strtotime($today . ' -750 day'))
        ,   ':to6' => date('Y-m-d', strtotime($today . '  -91 day'))
        );
        $ri = $this->_adapter->query($this->getBaseSql())->execute($prm);

        // 基準保存Array生成
        $aryBase = array();
        $aryBase[] = array('type' => 1, 'cnt' => 0, 'cntall' => 0, 'sum' => 0, 'sumall' => 0, 'settlementfeesum' => 0);
        $aryBase[] = array('type' => 2, 'cnt' => 0, 'cntall' => 0, 'sum' => 0, 'sumall' => 0, 'settlementfeesum' => 0);
        $aryBase[] = array('type' => 3, 'cnt' => 0, 'cntall' => 0, 'sum' => 0, 'sumall' => 0, 'settlementfeesum' => 0);
        $aryBase[] = array('type' => 4, 'cnt' => 0, 'cntall' => 0, 'sum' => 0, 'sumall' => 0, 'settlementfeesum' => 0);
        $aryBase[] = array('type' => 5, 'cnt' => 0, 'cntall' => 0, 'sum' => 0, 'sumall' => 0, 'settlementfeesum' => 0);
        $aryBase[] = array('type' => 6, 'cnt' => 0, 'cntall' => 0, 'sum' => 0, 'sumall' => 0, 'settlementfeesum' => 0);
        $aryBase[] = array('type' => 'Summary', 'SettlementFeeRate' => 0, 'ProfitRate' => 0, 'ProfitAndLoss' => 0);

        $arySave = array(); // データベース保存用(ｻｲﾄID＆JSONｴﾝｺｰﾄﾞﾃﾞｰﾀ)
        $bfSiteId = -1; // １つ前のサイトID(初期値:-1)
        $aryTmp = null;
        foreach ($ri as $row) {
            $index = intval($row['type']) - 1;

            if ($row['SiteId'] == $bfSiteId) {
                // １つ前のサイトIDと同じ時
                $aryTmp[$index]['cnt'] = intval($row['cnt']);
                $aryTmp[$index]['cntall'] = intval($row['cntall']);
                $aryTmp[$index]['sum'] = intval($row['sum']);
                $aryTmp[$index]['sumall'] = intval($row['sumall']);
                $aryTmp[$index]['settlementfeesum'] = intval($row['settlementfeesum']);
            }
            else {
                // １つ前のサイトIDと異なる時
                if ($bfSiteId == -1) {
                    // (初回限定)
                    $aryTmp = $aryBase;// 初期化
                }
                else {
                    // サマリー生成＆データベース保存用へセット
                    $this->makeSummary($bfSiteId, $aryTmp);
                    $arySave[] = array('siteid' => $bfSiteId, 'nptotal' => \Zend\Json\Json::encode($aryTmp));

                    $aryTmp = $aryBase;// 初期化
                }
                // (共通処理)
                $aryTmp[$index]['cnt'] = intval($row['cnt']);
                $aryTmp[$index]['cntall'] = intval($row['cntall']);
                $aryTmp[$index]['sum'] = intval($row['sum']);
                $aryTmp[$index]['sumall'] = intval($row['sumall']);
                $aryTmp[$index]['settlementfeesum'] = intval($row['settlementfeesum']);
            }
            $bfSiteId = $row['SiteId'];
        }
        // (最終処理)
        // サマリー生成＆データベース保存用へセット
        $this->makeSummary($bfSiteId, $aryTmp);
        $arySave[] = array('siteid' => $bfSiteId, 'nptotal' => \Zend\Json\Json::encode($aryTmp));

        try {
            // トランザクション開始
            $this->_adapter->getDriver()->getConnection()->beginTransaction();

            // ユーザーＩＤ取得
            $opId = $mdlu->getUserId(99, 1);

            // サイト別集計テーブルTRUNCATE
            $this->_adapter->query(" TRUNCATE TABLE T_SiteTotal ")->execute(null);

            // サイト別集計テーブル登録
            foreach ($arySave as $row) {
                $mdlst->saveNew(array('SiteId' => $row['siteid'], 'NpTotal' => $row['nptotal'], 'RegistId' => $opId, 'UpdateId' => $opId));
            }

            $this->_adapter->getDriver()->getConnection()->commit();
        }
        catch(\Exception $e) {
            $this->_adapter->getDriver()->getConnection()->rollBack();
            throw $e;
        }
    }

    /**
     * 基礎SQLと取得
     *
     * @return string SQL文字列
     */
    protected function getBaseSql()
    {
return <<< EOQ
/* 1週間未払い(支払い期限切れ8日～15日) */
SELECT '1' AS type
,      sit.SiteId
,      SUM(CASE WHEN o.DataStatus = 51 THEN 1 ELSE 0 END) AS cnt           /* 不払い率(件数)分子 */
,      COUNT(o.OrderSeq) AS cntall                                         /* 不払い率(件数)分母 */
,      SUM(CASE WHEN o.DataStatus = 51 THEN o.UseAmount ELSE 0 END) AS sum /* 不払い率(金額)分子 */
,      SUM(o.UseAmount) AS sumall                                          /* 不払い率(金額)分母 */
,      SUM(pas.SettlementFee) AS settlementfeesum                          /* 総決済手数料 */
FROM   T_Site sit
       LEFT OUTER JOIN T_Order o ON (o.SiteId = sit.SiteId)
       LEFT OUTER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.P_OrderSeq)
       LEFT OUTER JOIN T_PayingAndSales pas ON (pas.OrderSeq = o.OrderSeq)
WHERE 1 = 1
AND   o.Cnl_Status = 0
AND   cc.F_LimitDate BETWEEN :fr1 AND :to1
GROUP BY sit.SiteId
UNION ALL
/* １ヵ月未払い(支払い期限切れ31日～60日) */
SELECT '2'
,      sit.SiteId
,      SUM(CASE WHEN o.DataStatus = 51 THEN 1 ELSE 0 END)
,      COUNT(o.OrderSeq)
,      SUM(CASE WHEN o.DataStatus = 51 THEN o.UseAmount ELSE 0 END)
,      SUM(o.UseAmount)
,      SUM(pas.SettlementFee)
FROM   T_Site sit
       LEFT OUTER JOIN T_Order o ON (o.SiteId = sit.SiteId)
       LEFT OUTER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.P_OrderSeq)
       LEFT OUTER JOIN T_PayingAndSales pas ON (pas.OrderSeq = o.OrderSeq)
WHERE 1 = 1
AND   o.Cnl_Status = 0
AND   cc.F_LimitDate BETWEEN :fr2 AND :to2
GROUP BY sit.SiteId
UNION ALL
/* ３ヵ月未払い(支払い期限切れ91日～120日) */
SELECT '3'
,      sit.SiteId
,      SUM(CASE WHEN o.DataStatus = 51 THEN 1 ELSE 0 END)
,      COUNT(o.OrderSeq)
,      SUM(CASE WHEN o.DataStatus = 51 THEN o.UseAmount ELSE 0 END)
,      SUM(o.UseAmount)
,      SUM(pas.SettlementFee)
FROM   T_Site sit
       LEFT OUTER JOIN T_Order o ON (o.SiteId = sit.SiteId)
       LEFT OUTER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.P_OrderSeq)
       LEFT OUTER JOIN T_PayingAndSales pas ON (pas.OrderSeq = o.OrderSeq)
WHERE 1 = 1
AND   o.Cnl_Status = 0
AND   cc.F_LimitDate BETWEEN :fr3 AND :to3
GROUP BY sit.SiteId
UNION ALL
/* ６ヵ月（半年）未払い(支払い期限切れ181日～210日) */
SELECT '4'
,      sit.SiteId
,      SUM(CASE WHEN o.DataStatus = 51 THEN 1 ELSE 0 END)
,      COUNT(o.OrderSeq)
,      SUM(CASE WHEN o.DataStatus = 51 THEN o.UseAmount ELSE 0 END)
,      SUM(o.UseAmount)
,      SUM(pas.SettlementFee)
FROM   T_Site sit
       LEFT OUTER JOIN T_Order o ON (o.SiteId = sit.SiteId)
       LEFT OUTER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.P_OrderSeq)
       LEFT OUTER JOIN T_PayingAndSales pas ON (pas.OrderSeq = o.OrderSeq)
WHERE 1 = 1
AND   o.Cnl_Status = 0
AND   cc.F_LimitDate BETWEEN :fr4 AND :to4
GROUP BY sit.SiteId
UNION ALL
/* １２ヵ月（1年）未払い(支払い期限切れ361日～390日) */
SELECT '5'
,      sit.SiteId
,      SUM(CASE WHEN o.DataStatus = 51 THEN 1 ELSE 0 END)
,      COUNT(o.OrderSeq)
,      SUM(CASE WHEN o.DataStatus = 51 THEN o.UseAmount ELSE 0 END)
,      SUM(o.UseAmount)
,      SUM(pas.SettlementFee)
FROM   T_Site sit
       LEFT OUTER JOIN T_Order o ON (o.SiteId = sit.SiteId)
       LEFT OUTER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.P_OrderSeq)
       LEFT OUTER JOIN T_PayingAndSales pas ON (pas.OrderSeq = o.OrderSeq)
WHERE 1 = 1
AND   o.Cnl_Status = 0
AND   cc.F_LimitDate BETWEEN :fr5 AND :to5
GROUP BY sit.SiteId
UNION ALL
/* 全体未払い(支払い期限切れ91日～750日) */
SELECT '6'
,      sit.SiteId
,      SUM(CASE WHEN o.DataStatus = 51 THEN 1 ELSE 0 END)
,      COUNT(o.OrderSeq)
,      SUM(CASE WHEN o.DataStatus = 51 THEN o.UseAmount ELSE 0 END)
,      SUM(o.UseAmount)
,      SUM(pas.SettlementFee)
FROM   T_Site sit
       LEFT OUTER JOIN T_Order o ON (o.SiteId = sit.SiteId)
       LEFT OUTER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.P_OrderSeq)
       LEFT OUTER JOIN T_PayingAndSales pas ON (pas.OrderSeq = o.OrderSeq)
WHERE 1 = 1
AND   o.Cnl_Status = 0
AND   cc.F_LimitDate BETWEEN :fr6 AND :to6
GROUP BY sit.SiteId
ORDER BY SiteId, type
EOQ;
    }

    /**
     * サマリー生成
     *
     * @param int $siteId サイトID
     * @param array $ary 配列
     */
    protected function makeSummary($siteId, &$ary)
    {
        // 決済手数料率の取得
        $settlementFeeRate =$this->_adapter->query(" SELECT SettlementFeeRate FROM T_Site WHERE SiteId = :SiteId "
                )->execute(array(':SiteId' => $siteId))->current()['SettlementFeeRate'];
        $settlementFeeRate = floatval($settlementFeeRate);

        // 手数料率＆収益率＆損益額
        $ary[6]['SettlementFeeRate'] = $settlementFeeRate;
        $ary[6]['ProfitAndLoss'] = intval($ary[2]['settlementfeesum'] - $ary[2]['sum']);
        if ($ary[2]['sumall'] != 0) {
            $ary[6]['ProfitRate'] = floatval(sprintf('%.3f', $ary[6]['ProfitAndLoss'] / $ary[2]['sumall'] * 100));
        }
    }

    /**
     * 加盟店不払い情報生成
     *
     * @param int $enterpriseId 加盟店ID
     * @return array 配列
     */
    public function makeEnterpriseNpList($enterpriseId)
    {
        // 初期化
        $val = array('cnt' => 0, 'cntall' => 0, 'sum' => 0, 'sumall' => 0, 'settlementfeesum' => 0);
        $sitenplist = array('siteid' => '', 'sitenamekj' => '', 'settlementfeerate' => 0, 'profitrate' => 0, 'profitandloss' => 0);
        for ($i=0; $i<6; $i++) {
            $sitenplist['type' . ($i + 1)] = $val;
        }

        // サイト別集計取得
        $sql = <<< EOQ
SELECT st.SiteId, st.NpTotal, sit.SettlementFeeRate
FROM   T_SiteTotal st
       INNER JOIN T_Site sit ON (sit.SiteId = st.SiteId)
       INNER JOIN T_Enterprise ent ON (ent.EnterpriseId = sit.EnterpriseId)
WHERE  ent.EnterpriseId = :EnterpriseId
EOQ;
        $ri = $this->_adapter->query($sql)->execute(array(':EnterpriseId' => $enterpriseId));

        // 該当なし時は初期化値を戻す
        if ($ri->count() == 0) {
            $sql = " SELECT MIN(SettlementFeeRate) AS minval, MAX(SettlementFeeRate) AS maxval FROM T_Site WHERE EnterpriseId = :EnterpriseId ";
            $row = $this->_adapter->query($sql)->execute(array(':EnterpriseId' => $enterpriseId))->current();
            $sitenplist['settlementfeerate'] = ($row['minval'] != $row['maxval']) ? '-' : floatval($row['minval']);
            return $sitenplist;
        }

        // サイト別集計データ分加算
        $isMixSettlementFeeRate = false;    // 決済手数料率が混在しているか？
        $referenceSettlementFeeRate = -999; // 決済手数料率初期値
        $totalprofitandloss = 0;            // 総損益額
        $totalsumall = 0;                   // 総件数
        foreach ($ri as $row_st) {
            // 決済手数料率の混在チェック
            if ($referenceSettlementFeeRate == -999) {
                $referenceSettlementFeeRate = floatval($row_st['SettlementFeeRate']);
            }
            else if (!$isMixSettlementFeeRate && ($referenceSettlementFeeRate != floatval($row_st['SettlementFeeRate']))) {
                $isMixSettlementFeeRate = true;
            }

            $aryNpTotals = \Zend\Json\Json::decode($row_st['NpTotal'], \Zend\Json\Json::TYPE_ARRAY);
            $sumTarget = 0;     // 集計対象(不払い金額)
            $sumallTarget = 0;  // 集計対象(利用額)
            $sumsettlementfeeTarget = 0;// 集計対象(決済手数料)
            $aryNpTotalsCount = 0;
            if (!empty($aryNpTotals)) {
                $aryNpTotalsCount = count($aryNpTotals);
            }
            for ($j=0; $j<$aryNpTotalsCount; $j++) {
                $row = $aryNpTotals[$j];

                if ($row['type'] != 'Summary') {
                    $sitenplist['type' . $row['type']]['cnt'] += $row['cnt'];
                    $sitenplist['type' . $row['type']]['cntall'] += $row['cntall'];
                    $sitenplist['type' . $row['type']]['sum'] += $row['sum'];
                    $sitenplist['type' . $row['type']]['sumall'] += $row['sumall'];
                    $sitenplist['type' . $row['type']]['settlementfeesum'] += $row['settlementfeesum'];
                }
                if ($row['type'] == '3') {
                    $sumTarget = $row['sum'];
                    $sumallTarget = $row['sumall'];
                    $sumsettlementfeeTarget = $row['settlementfeesum'];
                }
            }

            // 総損益額加算
            $totalprofitandloss += intval($sumsettlementfeeTarget - $sumTarget);
            // 総件数加算
            $totalsumall += $sumallTarget;
        }

        // 決済手数料率が混在している場合はハイフンを、そうでない時は(共通である)決済手数料率を設定
        $sitenplist['settlementfeerate'] = ($isMixSettlementFeeRate) ? '-' : $referenceSettlementFeeRate;
        $sitenplist['profitandloss'] = $totalprofitandloss;
        if ($totalsumall == 0) {
            $sitenplist['profitrate'] = floatval(sprintf('%.3f', 0));
        }
        else {
            $sitenplist['profitrate'] = floatval(sprintf('%.3f', $totalprofitandloss / $totalsumall * 100));
        }

        return $sitenplist;
    }
}