<?php
namespace cbadmin\Controller;

use Coral\Base\BaseHtmlUtils;
use Coral\Coral\Controller\CoralControllerAction;
use cbadmin\Application;
use models\Table\TablePayingControl;
use models\Table\TableEnterpriseClaimed;
use models\View\ViewChargeConfirm;

class MonthlyController extends CoralControllerAction
{
	protected $_componentRoot = './application/views/components';

	/**
	 * アプリケーションオブジェクト
	 * @var Application
	 */
	private $app;

	/**
	 * Controllerを初期化する
	 */
	public function _init()
	{
        $this->app = Application::getInstance();
        $this->view->assign('userInfo', $this->app->authManagerAdmin->getUserInfo());

        $this->addStyleSheet('../css/default02.css');
        $this->addStyleSheet('../css/cbadmin/monthly/list/default.css');
        $this->addJavaScript('../js/prototype.js');

        $this->setPageTitle("後払い.com - 月次明細データ作成");
	}

	/**
	 * 月次明細データ作成フォームを表示する。
	 */
	public function listAction()
	{
        $mdlvcc = new ViewChargeConfirm($this->app->dbAdapter);
        $mdlpc = new TablePayingControl($this->app->dbAdapter);

        // 月度
        $d = date('Y-m-01');
        $d = date('Y-m-d', strtotime($now . ' -1 month'));

        // 事業者向け月次明細の作成はCBもOEMも混在で全てが処理される
        $datas = $mdlvcc->getMonthlyClaimedConfirm($d);
        // count関数対策
        $datasCount = 0;
        if (!empty($datas)) {
            $datasCount = count($datas);
        }
        for ($i = 0 ; $i < $datasCount ; $i++)
        {
            // 前月からの不足持ち越し額を取得する。
            $carryOver = $mdlpc->getCarryOverLastMonth($datas[$i]['EnterpriseId'], $d);
            $datas[$i]['CarryOverFromLastMonth'] = $carryOver;

            // 持越し分を支払総額に反映する。
            $datas[$i]['DecisionPaymentOrg'] = (int)$datas[$i]['DecisionPaymentOrg'] + $carryOver;
        }

        $this->view->assign('FixedMonthKanji', date('Y年m月度', strtotime($d)));
        $this->view->assign('FixedMonth', $d);
        $this->view->assign('list', $datas);

        return $this->view;
	}

// Del By Takemasa(NDC) 20151014 Stt 廃止
// 	/**
// 	 * 月次明細データの作成を行う。
// 	 */
// 	public function fixAction()
// Del By Takemasa(NDC) 20151014 End 廃止

	/**
	 * 作成済み月次明細データ閲覧
	 */
	public function fixedlistAction()
	{
        $params = $this->getParams();

        $today = date('Y-m-d');
        $fixedDate = (isset($params['fd'])) ? $params['fd'] : date('Y-m-01', strtotime($now . ' -1 month'));

        $mdlec = new TableEnterpriseClaimed($this->app->dbAdapter);
        $datas = $mdlec->getEnterpriseClaimed($fixedDate);

        $fixedMonthTag = BaseHtmlUtils::SelectTag(
            'fd',
            array(
                    date('Y-m-01', strtotime($today)) => date('Y年m月度　', strtotime($today)),
                    date('Y-m-01', strtotime($today . ' -1 month')) => date('Y年m月度　', strtotime($today . ' -1 month')),
                    date('Y-m-01', strtotime($today . ' -2 month')) => date('Y年m月度　', strtotime($today . ' -2 month')),
                    date('Y-m-01', strtotime($today . ' -3 month')) => date('Y年m月度　', strtotime($today . ' -3 month')),
                    date('Y-m-01', strtotime($today . ' -4 month')) => date('Y年m月度　', strtotime($today . ' -4 month')),
                    date('Y-m-01', strtotime($today . ' -5 month')) => date('Y年m月度　', strtotime($today . ' -5 month')),
                    date('Y-m-01', strtotime($today . ' -6 month')) => date('Y年m月度　', strtotime($today . ' -6 month')),
                    date('Y-m-01', strtotime($today . ' -7 month')) => date('Y年m月度　', strtotime($today . ' -7 month')),
                    date('Y-m-01', strtotime($today . ' -8 month')) => date('Y年m月度　', strtotime($today . ' -8 month')),
                    date('Y-m-01', strtotime($today . ' -9 month')) => date('Y年m月度　', strtotime($today . ' -9 month')),
                    date('Y-m-01', strtotime($today . ' -10 month')) => date('Y年m月度　', strtotime($today . ' -10 month')),
                    date('Y-m-01', strtotime($today . ' -11 month')) => date('Y年m月度　', strtotime($today . ' -11 month')),
            ),
            $fixedDate
        );

        $this->view->assign('fixedMonthTag', $fixedMonthTag);
        $this->view->assign('list', $datas);

        return $this->view;
	}

	/**
	 * ①請求書兼納品書データ閲覧
	 */
	public function summaryAction()
	{
        $params = $this->getParams();

        $eid = $params['eid'];
        $ym  = $params['ym'];

        $sql = <<<EOQ
SELECT '' AS term
,      ec.OrderCount
,      ec.OrderAmount
,      ec.SettlementFee
,      ec.ClaimFee
,      ec.StampFee
,      ec.MonthlyFee
,      ec.CarryOverMonthlyFee
,      ec.CancelRepaymentAmount
,      ec.FfTransferFee
,      ec.AdjustmentAmount
,      ec.PayBackAmount
,      ec.ClaimAmount
,      ec.PaymentAmount
FROM   T_EnterpriseClaimed ec
WHERE  1 = 1
AND    ec.EnterpriseId = :EnterpriseId
AND    ec.FixedMonth = :FixedMonth
EOQ;

        // SQL実行と期間設定
        $ri = $this->app->dbAdapter->query($sql)->execute(array(':EnterpriseId' => $eid, ':FixedMonth' => $ym));
        $data = ResultInterfaceToArray($ri);
        $termStr = $this->makeTermStr($ym);
        // count関数対策
        $dataCount = 0;		
        if (!empty($data)) {		
            $dataCount = count($data);		
        }		
        for ($i=0; $i<$dataCount; $i++) {
            $data[$i]['term']  = $termStr;
        }

        $this->view->assign('list', $data);
        $this->view->assign('link', $this->makeLinkTab($eid, $ym));
        $this->view->assign('dataInformation', $this->makeInformationStr($eid, $ym));

        return $this->view;
	}

	/**
	 * ②お取引明細データ閲覧
	 */
	public function chargelistAction()
	{
        $params = $this->getParams();

        $eid = $params['eid'];
        $ym  = $params['ym'];

        $sql = <<<EOQ
SELECT sit.siteId
,      sit.SiteNameKj
,      o.OrderId
,      o.Ent_OrderId
,      c.NameKj
,      o.ReceiptOrderDate
,      (SELECT MAX(Deli_JournalIncDate) FROM T_OrderItems WHERE OrderSeq = o.OrderSeq) AS MaxDeliJournalIncDate
,      pc.FixedDate
,      pas.UseAmount
,      pas.SettlementFee
,      pas.ClaimFee
,      IFNULL(sf.StampFee, 0) AS StampFee
,      (
         pas.UseAmount +
         (-1 * pas.SettlementFee) +
         (-1 * pas.ClaimFee) +
         (-1 * IFNULL(sf.StampFee, 0))
       ) AS sagaku
,      o.OrderSeq
FROM   T_PayingControl pc
       INNER JOIN T_Order o ON (o.Chg_Seq = pc.Seq)
       LEFT OUTER JOIN T_StampFee sf ON (sf.OrderSeq = o.OrderSeq)
       INNER JOIN T_PayingAndSales pas ON (pas.OrderSeq = o.OrderSeq)
       INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq)
       INNER JOIN T_Site sit ON (sit.SiteId = o.SiteId)
WHERE  1 = 1
AND    pc.EnterpriseId = :EnterpriseId
AND    o.EnterpriseId = :EnterpriseId
AND    pc.ChargeCount > 0
AND    pc.AddUpFlg = 1
AND    pc.AddUpFixedMonth = :FixedMonth
ORDER BY SiteId, OrderSeq
EOQ;

        // SQL実行
        $ri = $this->app->dbAdapter->query($sql)->execute(array(':EnterpriseId' => $eid, ':FixedMonth' => $ym));

        $this->view->assign('list', $this->_grouping(ResultInterfaceToArray($ri), 'SiteNameKj'));
        $this->view->assign('link', $this->makeLinkTab($eid, $ym));
        $this->view->assign('dataInformation', $this->makeInformationStr($eid, $ym));

        return $this->view;
	}

	/**
	 * ③印紙代明細データ閲覧
	 */
	public function stamplistAction()
	{
        $params = $this->getParams();

        $eid = $params['eid'];
        $ym  = $params['ym'];

        $sql = <<<EOQ
SELECT sit.siteId
,      sit.SiteNameKj
,      o.OrderId
,      o.Ent_OrderId
,      c.NameKj
,      o.ReceiptOrderDate
,      (SELECT MAX(Deli_JournalIncDate) FROM T_OrderItems WHERE OrderSeq = o.OrderSeq) AS MaxDeliJournalIncDate
,      pc.FixedDate
,      o.UseAmount
,      sf.StampFee
,      o.OrderSeq
FROM   T_PayingControl pc
       INNER JOIN T_StampFee sf ON (sf.PayingControlSeq = pc.Seq)
       INNER JOIN T_Order o ON (o.OrderSeq = sf.OrderSeq)
       INNER JOIN T_PayingAndSales pas ON (pas.OrderSeq = o.OrderSeq)
       INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq)
       INNER JOIN T_Site sit ON (sit.SiteId = o.SiteId)
WHERE  1 = 1
AND    pc.EnterpriseId = :EnterpriseId
AND    o.EnterpriseId = :EnterpriseId
AND    pc.AddUpFixedMonth = :FixedMonth
AND    pc.AddUpFlg = 1
AND    sf.ClearFlg = 1
ORDER BY SiteId, OrderSeq
EOQ;

        // SQL実行
        $ri = $this->app->dbAdapter->query($sql)->execute(array(':EnterpriseId' => $eid, ':FixedMonth' => $ym));

        $this->view->assign('list', $this->_grouping(ResultInterfaceToArray($ri), 'SiteNameKj'));
        $this->view->assign('link', $this->makeLinkTab($eid, $ym));
        $this->view->assign('dataInformation', $this->makeInformationStr($eid, $ym));

        return $this->view;
	}

	/**
	 * ④ｷｬﾝｾﾙ返金明細データ閲覧
	 */
	public function cancellistAction()
	{
        $params = $this->getParams();

        $eid = $params['eid'];
        $ym  = $params['ym'];

        $sql = <<<EOQ
SELECT sit.siteId
,      sit.SiteNameKj
,      o.OrderId
,      o.Ent_OrderId
,      c.NameKj
,      o.ReceiptOrderDate
,      pc.FixedDate
,      cncl.CancelDate
,      o.UseAmount
,      cncl.RepayTotal
,      o.OrderSeq
FROM   T_PayingControl pc
       INNER JOIN T_Cancel cncl ON (cncl.PayingControlSeq = pc.Seq)
       INNER JOIN T_Order o ON (o.OrderSeq = cncl.OrderSeq)
       INNER JOIN T_PayingAndSales pas ON (pas.OrderSeq = o.OrderSeq)
       INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq)
       INNER JOIN T_Site sit ON (sit.SiteId = o.SiteId)
WHERE  1 = 1
AND    pc.EnterpriseId = :EnterpriseId
AND    o.EnterpriseId = :EnterpriseId
AND    pc.AddUpFixedMonth = :FixedMonth
AND    pc.AddUpFlg = 1
AND    cncl.KeepAnAccurateFlg = 1
AND    cncl.RepayTotal <> 0
AND    cncl.ValidFlg = 1
ORDER BY SiteId, OrderSeq
EOQ;

        // SQL実行
        $ri = $this->app->dbAdapter->query($sql)->execute(array(':EnterpriseId' => $eid, ':FixedMonth' => $ym));

        $this->view->assign('list', $this->_grouping(ResultInterfaceToArray($ri), 'SiteNameKj'));
        $this->view->assign('link', $this->makeLinkTab($eid, $ym));
        $this->view->assign('dataInformation', $this->makeInformationStr($eid, $ym));

        return $this->view;
	}

	/**
	 * ⑤調整額明細データ閲覧
	 */
	public function adjustmentlistAction()
	{
        $params = $this->getParams();

        $eid = $params['eid'];
        $ym  = $params['ym'];

        $sql = <<<EOQ
SELECT pc.FixedDate
,      aa.OrderId
,      o.Ent_OrderId
,      c.NameKj
,      o.ReceiptOrderDate
,      (SELECT KeyContent FROM M_Code WHERE CodeId = 89 AND KeyCode = aa.ItemCode) AS kamoku
,      aa.AdjustmentAmount
,      aa.SerialNumber
FROM   T_PayingControl pc
       INNER JOIN T_EnterpriseClaimed ec ON (ec.EnterpriseId = pc.EnterpriseId AND ec.FixedMonth = pc.AddUpFixedMonth)
       INNER JOIN T_AdjustmentAmount aa ON (aa.PayingControlSeq = pc.Seq)
       LEFT OUTER JOIN T_Order o ON (o.OrderSeq = aa.OrderSeq)
       LEFT OUTER JOIN T_Customer c ON (c.OrderSeq = aa.OrderSeq)
WHERE  1 = 1
AND    ec.EnterpriseId = :EnterpriseId
AND    ec.FixedMonth = :FixedMonth
ORDER BY SerialNumber
EOQ;

        // SQL実行
        $ri = $this->app->dbAdapter->query($sql)->execute(array(':EnterpriseId' => $eid, ':FixedMonth' => $ym));

        $this->view->assign('list', ResultInterfaceToArray($ri));
        $this->view->assign('link', $this->makeLinkTab($eid, $ym));
        $this->view->assign('dataInformation', $this->makeInformationStr($eid, $ym));

        return $this->view;
	}

	/**
	 * ⑥立替精算戻し明細データ閲覧
	 */
	public function paybacklistAction()
	{
        $params = $this->getParams();

        $eid = $params['eid'];
        $ym  = $params['ym'];

        $sql = <<<EOQ
SELECT sit.siteId
,      sit.SiteNameKj
,      o.OrderId
,      o.Ent_OrderId
,      c.NameKj
,      o.ReceiptOrderDate
,      (SELECT MAX(Deli_JournalIncDate) FROM T_OrderItems WHERE OrderSeq = o.OrderSeq) AS MaxDeliJournalIncDate
,      pc.FixedDate
,      pas.UseAmount
,      pas.SettlementFee
,      pas.ClaimFee
,      IFNULL(sf.StampFee, 0) AS StampFee
,      pbc.PayBackAmount
,      o.OrderSeq
FROM   T_PayingControl pc
       INNER JOIN T_EnterpriseClaimed ec ON (ec.EnterpriseId = pc.EnterpriseId AND ec.FixedMonth = pc.AddUpFixedMonth)
       INNER JOIN T_PayingBackControl pbc ON (pbc.PayingControlSeq = pc.Seq)
       INNER JOIN T_PayingAndSales pas ON (pas.OrderSeq = pbc.OrderSeq)
       INNER JOIN T_Order o ON (o.OrderSeq = pbc.OrderSeq)
       LEFT OUTER JOIN T_StampFee sf ON (sf.OrderSeq = o.OrderSeq)
       INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq)
       INNER JOIN T_Site sit ON (sit.SiteId = o.SiteId)
WHERE  1 = 1
AND    ec.EnterpriseId = :EnterpriseId
AND    ec.FixedMonth = :FixedMonth
ORDER BY SiteId, OrderId
EOQ;

        // SQL実行
        $ri = $this->app->dbAdapter->query($sql)->execute(array(':EnterpriseId' => $eid, ':FixedMonth' => $ym));

        $this->view->assign('list', $this->_grouping(ResultInterfaceToArray($ri), 'SiteNameKj'));
        $this->view->assign('link', $this->makeLinkTab($eid, $ym));
        $this->view->assign('dataInformation', $this->makeInformationStr($eid, $ym));

        return $this->view;
	}

	/**
	 * SQL実行結果の配列を、指定のカラム名でグルーピングした
	 * 連想配列として詰めなおす
	 *
	 * @param array $list クエリ実行結果の配列
	 * @param string $key_name グルーピングキーとなる、$listの1要素中のカラム名
	 * @return array
	 */
	private function _grouping($list, $key_name) {
	    $results = array();
	    foreach($list as $row) {
	        $key = $row[$key_name];
	        if( is_array($results[$key]) ) {
	            $results[$key][] = $row;
	        } else {
	            $results[$key] = array($row);
	        }
	    }
	    return $results;
	}

	/**
	 * 書式化されたインフォメーション文字列の生成
	 *
	 * 以下の様に書式化された文字列を戻す
	 * ・"2014年11月度(2014.11.01～2014.11.30) AT00000001 株式会社ネットビー＠test01"
	 *
	 * @param string $enterpriseId 加盟店ID(T_Enterprise.EnterpriseId)
	 * @param string $ym 年月日
	 * @return string 書式化インフォメーション文字列
	 */
	private function makeInformationStr($enterpriseId, $ym)
	{
        $sql = " SELECT LoginId, EnterpriseNameKj FROM T_Enterprise WHERE EnterpriseId = :EnterpriseId ";
        $row = $this->app->dbAdapter->query($sql)->execute(array(':EnterpriseId' => $enterpriseId))->current();
        $loginId = $row['LoginId'];
        $enterpriseNameKj = $row['EnterpriseNameKj'];

        return (date('Y年m月度', strtotime($ym)) . '(' . $this->makeTermStr($ym) . ') ' . $loginId . ' ' . $enterpriseNameKj);
	}

	/**
	 * 書式化された期間文字列を戻す
	 *
	 * 以下の様に書式化された文字列を戻す(入力$ym="2015-04-01"のとき)
	 * ・"2015.04.01～2015.04.30"
	 *
	 * @param string $ym 年月日
	 * @return string 書式化期間文字列
	 */
	private function makeTermStr($ym)
	{
        $ymstt = date('Y.m.d', strtotime($ym));
        $nextmonth01 = date('Y-m-01', strtotime($ym . ' +1 month'));
        $ymend = date('Y.m.d', strtotime($nextmonth01 . ' -1 day'));

        return ($ymstt . '～' . $ymend);
	}

	/**
	 * リンク情報の生成
	 *
	 * @param string $enterpriseId 加盟店ID
	 * @param string $ym 年月日
	 * @return array リンク情報
	 */
	private function makeLinkTab($enterpriseId, $ym)
	{
	    $link = array();
	    $link['summary'] = ('monthly/summary/eid/' . $enterpriseId . '/ym/' . $ym);
	    $link['chargelist'] = ('monthly/chargelist/eid/' . $enterpriseId . '/ym/' . $ym);
	    $link['stamplist'] = ('monthly/stamplist/eid/' . $enterpriseId . '/ym/' . $ym);
	    $link['cancellist'] = ('monthly/cancellist/eid/' . $enterpriseId . '/ym/' . $ym);
	    $link['adjustmentlist'] = ('monthly/adjustmentlist/eid/' . $enterpriseId . '/ym/' . $ym);
	    $link['paybacklist'] = ('monthly/paybacklist/eid/' . $enterpriseId . '/ym/' . $ym);

        return $link;
	}
}

