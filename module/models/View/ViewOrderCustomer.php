<?php
namespace models\View;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * V_OrderCustomerビュー
 */
class ViewOrderCustomer
{
    protected $_name = 'V_OrderCustomer';
    protected $_primary = 'OrderSeq';
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
     * 指定の注文データを取得する。
     *
     * @param int $orderSeq
     * @return ResultInterface
     */
    public function find($orderSeq)
    {
        $sql = " SELECT * FROM V_OrderCustomer WHERE OrderSeq = :OrderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $orderSeq,
        );

        return $stm->execute($prm);
    }

    /**
     * 指定ステータスの注文データを取得する。
     *
     * @param int $dataStatus データステータス
     * @return ResultInterface
     */
    public function findByDs($dataStatus)
    {
        $sql = " SELECT voc.*, e.DispDecimalPoint, e.UseAmountFractionClass, o.T_OrderClass FROM V_OrderCustomer voc INNER JOIN T_Enterprise e ON (e.EnterpriseId = voc.EnterpriseId ) INNER JOIN T_Order o ON (o.OrderSeq = voc.OrderSeq) WHERE voc.DataStatus = :DataStatus AND voc.Cnl_Status = 0 ORDER BY OrderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':DataStatus' => $dataStatus,
        );

        return $stm->execute($prm);
    }

    /**
     * 社内与信実行待ち注文データを取得する。
     *
     * @return ResultInterface
     */
    public function findByDs11()
    {
        return $this->findByDs(11);
    }

    /**
     * 社内与信確定待ち注文データを取得する。
     *
     * @return ResultInterface
     */
    public function findByDs15()
    {
        return $this->findByDs(15);
    }

    /**
     * 社内与信保留の注文データを取得する。
     *
     * @return ResultInterface
     */
    public function findByDs21()
    {
        return $this->findByDs(21);
    }

//     /**
//      * DMIインポート待ちを取得する。
//      *
//      * @return ResultInterface
//      */
//     public function findByDs25()
//     {
//         return $this->findByDs(25);
//     }

//     /**
//      * DMI与信確定待ち（インポート・エクスポート待ち）を取得する。
//      *
//      * @return ResultInterface
//      */
//     public function findByDs21and25()
//     {
//         $sql = " SELECT * FROM V_OrderCustomer WHERE DataStatus IN (21,25) AND Cnl_Status = 0 ORDER BY OrderSeq ";
//         return $this->_adapter->query($sql)->execute(null);
//     }

    /**
     * 伝票番号入力待ち注文データを取得する。
     *
     * @return ResultInterface
     */
    public function findByDs31()
    {
        return $this->findByDs(31);
    }

    /**
     * 請求書印刷待ち注文データを取得する。
     *
     * @return ResultInterface
     */
    public function findByDs41()
    {
        return $this->findByDs(41);
    }

    /**
     * 指定された入金確認待ち注文データを取得する。
     *
     * @return ResultInterface
     */
    public function findByDs51Simple($oseq)
    {
        $sql =
            "SELECT
                    ORD.OrderSeq,
                    ORD.OrderId,
                    CUS.NameKj,
                    ORD.UseAmount,
                    ORD.Clm_F_LimitDate,
                    ORD.Clm_L_ClaimDate,
                    ORD.Clm_L_ClaimPattern,
                    ORD.Clm_L_LimitDate,
                    ORD.Clm_L_DamageInterestAmount,
                    ORD.Clm_L_ClaimFee,
                    ORD.Clm_L_AdditionalClaimFee,
                    CUS.Incre_ArAddr
                FROM
                    T_Order ORD STRAIGHT_JOIN
                    T_Customer CUS ON ORD.OrderSeq = CUS.OrderSeq LEFT OUTER JOIN
                    M_GeneralPurpose MGP ON ( (MGP.Code =
                                   CASE WHEN `CUS`.Incre_ArTel = 5 OR `CUS`.Incre_ArAddr = 5 THEN  5
                                        WHEN `CUS`.Incre_ArTel = 4 OR `CUS`.Incre_ArAddr = 4 THEN  4
                                        WHEN `CUS`.Incre_ArTel = 3 OR `CUS`.Incre_ArAddr = 3 THEN  3
                                        WHEN `CUS`.Incre_ArTel = 2 OR `CUS`.Incre_ArAddr = 2 THEN  2
                                        WHEN `CUS`.Incre_ArTel = 1 OR `CUS`.Incre_ArAddr = 1 THEN  1
                                        ELSE -1 END )
                                        AND MGP.Class = 4 )
                WHERE
                    DataStatus = 51 AND Cnl_Status = 0 AND ORD.OrderSeq = :OrderSeq";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $oseq,
        );

        return $stm->execute($prm);
    }

    /**
     * 入金確認待ち注文データを取得する。(viewを利用しない高速版)
     *
     * @param $delayDate 300日前の日付
     * @param $delay 0:300日未満 1:300日以上
     * @param $entcustseq 加盟店顧客番号
     * @param $orderseq 注文番号
     * @param $mancustid 管理顧客番号
     * @return ResultInterface
     */
    public function findByDs51($delayDate = null, $delay = null, $entcustseq = null, $orderseq = null, $mancustid = null)
    {
        $baseQuery = sprintf(
            "SELECT DISTINCT
                    po.OrderSeq
                ,   po.OrderId
                ,   c.NameKj
                ,   po.UseAmount
                ,   mc.Class2 AS IncreArCaption
                ,   mc.KeyContent AS IncreArLongCaption
                ,   cc.F_LimitDate
                ,   cc.ClaimDate
                ,   cc.ClaimPattern
                ,   (SELECT Class2 FROM M_Code WHERE CodeId = 12 AND KeyCode = cc.ClaimPattern) AS ClaimPatternShortStr
                ,   cc.LimitDate
                ,   cc.DamageInterestAmount
                ,   cc.ClaimFee
                ,   cc.AdditionalClaimFee
                ,   c.Incre_ArAddr
                ,   c.CustomerId
                ,   cc.ClaimAmount
            FROM    T_Order o
            LEFT OUTER JOIN T_ClaimControl cc ON (o.P_OrderSeq = cc.OrderSeq)
            INNER JOIN T_Order po ON po.OrderSeq = o.P_OrderSeq
            INNER JOIN T_Customer c ON (po.OrderSeq = c.OrderSeq)
            LEFT OUTER JOIN M_Code mc ON (mc.KeyCode = CASE
                                                            WHEN c.Incre_ArTel = 5 OR c.Incre_ArAddr = 5 THEN 5
                                                            WHEN c.Incre_ArTel = 4 OR c.Incre_ArAddr = 4 THEN 4
                                                            WHEN c.Incre_ArTel = 3 OR c.Incre_ArAddr = 3 THEN 3
                                                            WHEN c.Incre_ArTel = 2 OR c.Incre_ArAddr = 2 THEN 2
                                                            WHEN c.Incre_ArTel = 1 OR c.Incre_ArAddr = 1 THEN 1
                                                            ELSE -1
                                                       END
                                          AND mc.CodeId = 4)
            INNER JOIN T_EnterpriseCustomer ec ON (c.EntCustSeq = ec.EntCustSeq)
            INNER JOIN T_ManagementCustomer mcu ON (ec.ManCustId = mcu.ManCustId)
            WHERE   o.DataStatus = 51
            AND     o.Cnl_Status = 0
            ");

        $query = '';
        if($delayDate != null && !$delay) {
            // 300日以上
            $query = $baseQuery.sprintf(" AND cc.LimitDate > '%s' ORDER BY OrderSeq ASC", $delayDate);
        }
        else if($delayDate != null && $delay) {
            // 300日未満
            $query = $baseQuery.sprintf(" AND cc.LimitDate <= '%s' ORDER BY OrderSeq ASC", $delayDate);
        }
        else if($entcustseq != null ){
            //顧客詳細からの遷移された場合EntCustSeq
            $query = $baseQuery.sprintf(" AND cc.EntCustSeq = '%s' ORDER BY OrderSeq ASC", $entcustseq);
        }
        else if($orderseq != null ){
            // 注文SEQが通知されるとき
            $query = $baseQuery.sprintf(" AND o.OrderSeq = %s ORDER BY OrderSeq ASC", $orderseq);
        }
        else if($mancustid != null){
            // 管理顧客番号が通知されるとき
            $query = $baseQuery.sprintf(" AND mcu.ManCustId = '%s' ORDER BY OrderSeq ASC", $mancustid);
        }
        else {
            // 全て
            $query = $baseQuery.sprintf(" ORDER BY OrderSeq ASC");
        }

        return $this->_adapter->query($query)->execute(null);
    }

    /**
     * クローズされた注文データを取得する。
     * クローズされたデータの場合のみ、キャンセル分も含む。
     *
     * @return ResultInterface
     */
    public function findByDs91()
    {
        $sql = " SELECT * FROM V_OrderCustomer WHERE DataStatus = 91 ORDER BY OrderSeq ";
        return $this->_adapter->query($sql)->execute(null);
    }

    /**
     * 指定条件（AND）のデータを取得する。
     *
     * @param array $conditionArray 検索条件を格納した連想配列
     * @return ResultInterface
     */
    public function findOrderCustomer($conditionArray)
    {
        $prm = array();
        $sql  = " SELECT * FROM V_OrderCustomer WHERE 1 = 1 ";
        foreach ($conditionArray as $key => $value) {
            $sql .= (" AND " . $key . " = :" . $key);
            $prm += array(':' . $key => $value);
        }

        $stm = $this->_adapter->query($sql);

        return $stm->execute($prm);
    }


// Del By Takemasa(NDC) 20150629 Stt 同等実装対応により不要化
//     /**
//      * 再請求待ちデータを取得する。
//      *
//      * @param string $from 開始期限 'yyyy-MM-dd'書式で通知
//      * @param string $to 終了期限 'yyyy-MM-dd'書式で通知
//      * @param int $tdays 検索対象日付（1：前回請求期限 2：初回請求期限）
//      * @param int $claimPattern (0：指定なし　2：再請求1　3：再請求2　4：再請求3～）
//      * @param int $isDone (1：しているもの　0：していないもの）
//      * @return ResultInterface
//      */
//     public function findReClaimTarget($from = null, $to = null, $tdays = 1, $claimPattern = 0, $isDone = 1)
//     {
//         $baseQuery = "SELECT VOC.* FROM V_OrderCustomer VOC WHERE VOC.DataStatus = 51 AND Cnl_Status = 0 AND (LetterClaimStopFlg IS NULL OR LetterClaimStopFlg = 0) AND "
//             . "(SELECT COUNT(*) FROM T_ClaimHistory WHERE OrderSeq = VOC.OrderSeq AND PrintedFlg = 0) = 0 ";
//
//         $baseQuery .= $this->getExtendedWhereByClaimPattern($claimPattern, $isDone);
//
//         if ($tdays == 1)
//         {
//             $tdaysField = 'Clm_L_LimitDate';
//         }
//         else
//         {
//             $tdaysField = 'Clm_F_LimitDate';
//         }
//
//         if ($from == null && $to == null)
//         {
//             $query = $baseQuery . "ORDER BY OrderSeq";
//         }
//         else if ($from != null && $to != null)
//         {
//             $query = $baseQuery . sprintf("AND %s BETWEEN '%s' AND '%s' ", $tdaysField, $from, $to) . "ORDER BY OrderSeq";
//         }
//         else if ($from != null)
//         {
//             $query = $baseQuery . sprintf("AND %s >= '%s' ", $tdaysField, $from) . "ORDER BY OrderSeq";
//         }
//         else
//         {
//             $query = $baseQuery . sprintf("AND %s <= '%s' ", $tdaysField, $to) . "ORDER BY OrderSeq";
//         }
//
//         return $this->_adapter->query($query)->execute(null);
//     }
// Del By Takemasa(NDC) 20150629 End 同等実装対応により不要化

    /**
     * 再請求待ちデータの件数を取得する。
     *
     * @param string $from 開始期限 'yyyy-MM-dd'書式で通知
     * @param string $to 終了期限 'yyyy-MM-dd'書式で通知
     * @param int $tdays 検索対象日付（1：前回請求期限 2：初回請求期限）
     * @param int $claimPattern (0：指定なし　2：再請求1　3：再請求2　4：再請求3～）
     * @param int $isDone (1：しているもの　0：していないもの）
     * @return int
     */
    public function findReClaimTargetCount($from = null, $to = null, $tdays = 1, $claimPattern = 0, $isDone = 1)
    {
        $baseQuery=<<<EOQ
SELECT COUNT(DISTINCT ORD.P_OrderSeq) AS CNT
FROM   T_Order ORD
       INNER JOIN T_ClaimControl CC ON (CC.OrderSeq = ORD.P_OrderSEq)
WHERE  ORD.DataStatus IN (51, 61)
AND    ORD.Cnl_Status = 0
AND    (ORD.LetterClaimStopFlg IS NULL OR ORD.LetterClaimStopFlg = 0)
EOQ;
        if ($tdays == 1)
        {
            $tdaysField = 'CC.LimitDate';
        }
        else
        {
            $tdaysField = 'CC.F_LimitDate';
        }

        if ($from == null && $to == null)
        {
            $query = $baseQuery;
        }
        else if ($from != null && $to != null)
        {
            $query = $baseQuery . sprintf("AND %s BETWEEN '%s' AND '%s' ", $tdaysField, $from, $to);
        }
        else if ($from != null)
        {
            $query = $baseQuery . sprintf("AND %s >= '%s' ", $tdaysField, $from);
        }
        else
        {
            $query = $baseQuery . sprintf("AND %s <= '%s' ", $tdaysField, $to);
        }

        return (int)$this->_adapter->query($query)->execute(null)->current()['CNT'];
    }

// Del By Takemasa(NDC) 20150629 Stt 同等実装対応により不要化
//     /**
//      * 再請求待ちデータの絞り込みに使用するWHERE句の拡張部分を作成する。
//      *
//      * @param int $claimPattern (0：指定なし　2：再請求1　3：再請求2　4：再請求3～）
//      * @param int $isDone (1：しているもの　0：していないもの）
//      * @return string WHERE句（ANDを含む）
//      */
//     private function getExtendedWhereByClaimPattern($claimPattern, $isDone)
//     {
//         $where = '
//             AND
//             (SELECT
//                 COUNT(*)
//             FROM
//                 T_ClaimHistory
//             WHERE
//                 OrderSeq = VOC.OrderSeq AND
//                 PrintedFlg = 1 AND
//                 ClaimPattern = %d
//             ) %s 0
//         ';
//
//         if ($claimPattern == 0)
//         {
//             $result = '';
//         }
//         else
//         {
//             if ($isDone == 1)
//             {
//                 $result = sprintf($where, $claimPattern, '>');
//             }
//             else
//             {
//                 $result = sprintf($where, $claimPattern, '=');
//             }
//         }
//
//         return $result;
//     }
// Del By Takemasa(NDC) 20150629 End 同等実装対応により不要化

    /**
     * 指定条件文字列（WHERE句）によるデータを取得する。
     *
     * @param string $whereStr WHERE句
     * @param string $orderStr ORDER句
     * @return ResultInterface
     */
    public function findOrderCustomerByWhereStr($whereStr, $orderStr)
    {
        $sql = " SELECT * FROM V_OrderCustomer WHERE " . $whereStr . " " . " ORDER BY " . $orderStr;
        return $this->_adapter->query($sql)->execute(null);
    }

    /**
     * OrderSeqによるデータ取得。
     *
     * Mod By Takemasa(NDC) 20150108 第二引数[$isArray]は無視し必ずResultInterfaceを戻す
     *
     * @see ビューを使うとすごく遅くなる・・・
     * @return ResultInterface
     */
    public function findOrderCustomerByOrderSeq($oseq, $isArray = false)
    {
        $sql = " SELECT * FROM V_OrderCustomer WHERE OrderSeq = :OrderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $oseq,
        );

        return $stm->execute($prm);
        }

    /**
     * 請求取りまとめサマリー取得（事業者単位）
     *
     * @return ResultInterface
     */
    public function getMergeTargetSummaryOnEnterprise()
    {
        $query = "
            SELECT
                ORD.EnterpriseId,
                CUS.RegNameKj,
                CUS.RegPhone,
                COUNT(*) AS CNT
            FROM
                T_Order ORD INNER JOIN T_Customer CUS ON (ORD.OrderSeq = CUS.OrderSeq)
                INNER JOIN T_Enterprise ENT ON (ORD.EnterpriseId = ENT.EnterpriseId)
            WHERE
                ENT.CombinedClaimMode = 1 AND
                ORD.CombinedClaimTargetStatus IN (1, 2) AND
                ORD.DataStatus = 41 AND
                ORD.Cnl_Status = 0
            GROUP BY
                ORD.EnterpriseId,
                CUS.RegNameKj,
                CUS.RegPhone
                ";

        return $this->_adapter->query($query)->execute(null);
    }

    /**
     * 請求取りまとめサマリー取得（サイト単位）
     *
     * @return ResultInterface
     */
    public function getMergeTargetSummaryOnSite()
    {
        $query = "
            SELECT
                ORD.SiteId,
                CUS.RegNameKj,
                CUS.RegPhone,
                COUNT(*) AS CNT
            FROM
                T_Order ORD INNER JOIN T_Customer CUS ON (ORD.OrderSeq = CUS.OrderSeq)
                INNER JOIN T_Enterprise ENT ON (ORD.EnterpriseId = ENT.EnterpriseId)
                INNER JOIN T_Site SIT ON (ORD.SiteId = SIT.SiteId)
            WHERE
                ENT.CombinedClaimMode = 2 AND
                SIT.CombinedClaimFlg = 1 AND
                ORD.CombinedClaimTargetStatus IN (1, 2) AND
                ORD.DataStatus = 41 AND
                ORD.Cnl_Status = 0
            GROUP BY
                ORD.SiteId,
                CUS.RegNameKj,
                CUS.RegPhone
                ";

        return $this->_adapter->query($query)->execute(null);
    }

    /**
     * まとめ対象のOrderSeqを取得（事業者単位）
     *
     * @param int $enterpriseId 事業者ID
     * @param string $regNameKj 正規化氏名
     * @param string $regPhone 正規化電話
     * @return ResultInterface OrderSeqリスト
     */
    public function getMergeTargetOrderSeqsOnEnterprise($enterpriseId, $regNameKj, $regPhone)
    {
        $query = "
            SELECT
                ORD.OrderSeq
            FROM
                T_Order ORD INNER JOIN T_Customer CUS ON (ORD.OrderSeq = CUS.OrderSeq)
                INNER JOIN T_Enterprise ENT ON (ORD.EnterpriseId = ENT.EnterpriseId)
            WHERE
                ENT.CombinedClaimMode = 1 AND
                ORD.CombinedClaimTargetStatus IN (1, 2) AND
                ORD.DataStatus = 41 AND
                ORD.Cnl_Status = 0 AND
                ORD.EnterpriseId = :EnterpriseId AND
                CUS.RegNameKj = :RegNameKj AND
                CUS.RegPhone = :RegPhone
                AND (
                    ORD.LetterClaimStopFlg IS null
                    OR ORD.LetterClaimStopFlg = 0
                )
            ORDER BY
                ORD.OrderSeq
            ";

        $stm = $this->_adapter->query($query);

        $prm = array(
                ':EnterpriseId' => $enterpriseId,
                ':RegNameKj' => $regNameKj,
                ':RegPhone' => $regPhone,
        );

        return $stm->execute($prm);
    }

    /**
     * まとめ対象のOrderSeqを取得（サイト単位）
     *
     * @param int $siteId サイトID
     * @param string $regNameKj 正規化氏名
     * @param string $regPhone 正規化電話
     * @return ResultInterface OrderSeqリスト
     */
    public function getMergeTargetOrderSeqsOnSite($siteId, $regNameKj, $regPhone)
    {
        $query = "
            SELECT
                ORD.OrderSeq
            FROM
                T_Order ORD INNER JOIN T_Customer CUS ON (ORD.OrderSeq = CUS.OrderSeq)
                INNER JOIN T_Enterprise ENT ON (ORD.EnterpriseId = ENT.EnterpriseId)
                INNER JOIN T_Site SIT ON (ORD.SiteId = SIT.SiteId)
            WHERE
                ENT.CombinedClaimMode = 2 AND
                SIT.CombinedClaimFlg = 1 AND
                ORD.CombinedClaimTargetStatus IN (1, 2) AND
                ORD.DataStatus = 41 AND
                ORD.Cnl_Status = 0 AND
                ORD.SiteId = :SiteId AND
                CUS.RegNameKj = :RegNameKj AND
                CUS.RegPhone = :RegPhone
                AND (
                    ORD.LetterClaimStopFlg IS null
                    OR ORD.LetterClaimStopFlg = 0
                )
            ORDER BY
                ORD.OrderSeq
            ";

        $stm = $this->_adapter->query($query);

        $prm = array(
                ':SiteId' => $siteId,
                ':RegNameKj' => $regNameKj,
                ':RegPhone' => $regPhone,
        );

        return $stm->execute($prm);
    }

    /**
     * まとめ対象の事業者毎の注文情報を取得
     * @param enterpriseid
     * @return ResultInterface
     */
    public function getMergeOrderByEnterprise($entId) {

        $query = "
            SELECT
                ORD.OrderSeq,
                ORD.OrderId,
                ORD.ReceiptOrderDate,
                ORD.Ent_OrderId,
                CUS.NameKj,
                CUS.PostalCode,
                CUS.UnitingAddress,
                CUS.Phone,
                CUS.CustomerId
            FROM
                T_Order ORD INNER JOIN T_Customer CUS ON (ORD.OrderSeq = CUS.OrderSeq)
                INNER JOIN T_EnterpriseCustomer EC ON EC.EntCustSeq = CUS.EntCustSeq
                INNER JOIN T_ManagementCustomer MC ON MC.ManCustId = EC.ManCustId
            WHERE
                ORD.CombinedClaimTargetStatus in (1,2)  AND
                ORD.DataStatus = 41 AND
                ORD.Cnl_Status = 0 AND
                ORD.EnterpriseId = :EnterpriseId
                AND (
                    ORD.LetterClaimStopFlg IS null
                    OR ORD.LetterClaimStopFlg = 0
                )
                AND MC.RemindStopFlg = 0
            GROUP BY
                ORD.OrderSeq
                ";

        $stm = $this->_adapter->query($query);

        $prm = array(
                ':EnterpriseId' => $entId,
        );

        return $stm->execute($prm);
    }

    /**
     * まとめ対象の事業者毎の注文情報を取得
     * @param enterpriseid
     * @return ResultInterface
     */
    public function getMergeOrderBySite($entId, $siteId) {

        $query = "
            SELECT
                ORD.OrderSeq,
                ORD.OrderId,
                ORD.ReceiptOrderDate,
                ORD.Ent_OrderId,
                CUS.NameKj,
                CUS.PostalCode,
                CUS.UnitingAddress,
                CUS.Phone,
                CUS.CustomerId
            FROM
                T_Order ORD INNER JOIN T_Customer CUS ON (ORD.OrderSeq = CUS.OrderSeq)
                INNER JOIN T_EnterpriseCustomer EC ON EC.EntCustSeq = CUS.EntCustSeq
                INNER JOIN T_ManagementCustomer MC ON MC.ManCustId = EC.ManCustId
            WHERE
                ORD.CombinedClaimTargetStatus in (1,2)  AND
                ORD.DataStatus = 41 AND
                ORD.Cnl_Status = 0 AND
                ORD.EnterpriseId = :EnterpriseId AND
                ORD.SiteId = :SiteId
                AND (
                    ORD.LetterClaimStopFlg IS null
                    OR ORD.LetterClaimStopFlg = 0
                )
                AND MC.RemindStopFlg = 0
            GROUP BY
                ORD.OrderSeq ";

        $stm = $this->_adapter->query($query);

        $prm = array(
                ':EnterpriseId' => $entId,
                ':SiteId' => $siteId,
        );

        return $stm->execute($prm);
    }

    /**
     * まとめ対象の事業者毎の注文情報を取得
     * @param enterpriseid
     * @return ResultInterface
     */
    public function getMergeOrderByEnterprise2($entId) {

        $query = "
            SELECT
                ORD.OrderSeq,
                ORD.OrderId,
                ORD.ReceiptOrderDate,
                ORD.Ent_OrderId,
                CUS.NameKj,
                CUS.PostalCode,
                CUS.UnitingAddress,
                CUS.Phone,
                CUS.CustomerId
            FROM
                T_Order ORD INNER JOIN T_Customer CUS ON (ORD.OrderSeq = CUS.OrderSeq)
                INNER JOIN T_EnterpriseCustomer EC ON EC.EntCustSeq = CUS.EntCustSeq
                INNER JOIN T_ManagementCustomer MC ON MC.ManCustId = EC.ManCustId
            WHERE
                ORD.CombinedClaimTargetStatus in (1,2)  AND
                ORD.DataStatus = 41 AND
                ORD.Cnl_Status = 0 AND
                ORD.EnterpriseId = :EnterpriseId
                AND (
                    ORD.LetterClaimStopFlg IS null
                    OR ORD.LetterClaimStopFlg = 0
                )
                AND MC.RemindStopFlg = 0
            GROUP BY
                CUS.NameKj,
                CUS.Phone,
                ORD.OrderSeq
                ";

        $stm = $this->_adapter->query($query);

        $prm = array(
                ':EnterpriseId' => $entId,
        );

        return $stm->execute($prm);
    }

    /**
     * まとめ対象の事業者毎の注文情報を取得
     * @param enterpriseid
     * @return ResultInterface
     */
    public function getMergeOrderBySite2($entId, $siteId) {

        $query = "
            SELECT
                ORD.OrderSeq,
                ORD.OrderId,
                ORD.ReceiptOrderDate,
                ORD.Ent_OrderId,
                CUS.NameKj,
                CUS.PostalCode,
                CUS.UnitingAddress,
                CUS.Phone,
                CUS.CustomerId
            FROM
                T_Order ORD INNER JOIN T_Customer CUS ON (ORD.OrderSeq = CUS.OrderSeq)
                INNER JOIN T_EnterpriseCustomer EC ON EC.EntCustSeq = CUS.EntCustSeq
                INNER JOIN T_ManagementCustomer MC ON MC.ManCustId = EC.ManCustId
            WHERE
                ORD.CombinedClaimTargetStatus in (1,2)  AND
                ORD.DataStatus = 41 AND
                ORD.Cnl_Status = 0 AND
                ORD.EnterpriseId = :EnterpriseId AND
                ORD.SiteId = :SiteId
                AND (
                    ORD.LetterClaimStopFlg IS null
                    OR ORD.LetterClaimStopFlg = 0
                )
                AND MC.RemindStopFlg = 0
            GROUP BY
                CUS.NameKj,
                CUS.Phone,
                ORD.OrderSeq ";

        $stm = $this->_adapter->query($query);

        $prm = array(
                ':EnterpriseId' => $entId,
                ':SiteId' => $siteId,
        );

        return $stm->execute($prm);
    }

    /**
     * まとめキャンセル対象の事業者毎の注文情報を取得
     * @param enterpriseid
     * @return ResultInterface
     */
    public function getMergeOrderCancelByEnterprise($entId) {

        $query = <<<EOQ
SELECT  DISTINCT o.OrderSeq
    ,   o.OrderId
    ,   o.ReceiptOrderDate
    ,   o.Ent_OrderId
    ,   c.NameKj
    ,   c.PostalCode
    ,   c.UnitingAddress
    ,   c.Phone
    ,   cd.CombinedDictateGroup
    ,   cd.ErrorMsg
    ,   c.CustomerId
FROM    T_Order o
        INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq)
        INNER JOIN T_CombinedDictate cd ON (cd.OrderSeq = o.OrderSeq)
        INNER JOIN T_EnterpriseCustomer ec ON ec.EntCustSeq = c.EntCustSeq
        INNER JOIN T_ManagementCustomer mc ON mc.ManCustId = ec.ManCustId
WHERE   o.CombinedClaimTargetStatus IN (11, 12)
AND     o.DataStatus = 41
AND     o.Cnl_Status = 0
AND     (o.LetterClaimStopFlg IS NULL OR o.LetterClaimStopFlg <> 1)
AND     mc.RemindStopFlg = 0
AND     cd.CombinedStatus = 0
AND     (o.OutOfAmends IS NULL OR o.OutOfAmends <> 1)
AND     o.EnterpriseId = :EnterpriseId
EOQ;

        $stm = $this->_adapter->query($query);

        $prm = array(
                ':EnterpriseId' => $entId,
        );

        return $stm->execute($prm);
    }

    /**
     * まとめキャンセル対象の事業者毎の注文情報を取得
     * @param enterpriseid
     * @return ResultInterface
     */
    public function getMergeOrderCancelBySite($entId, $siteId) {

        $query = <<<EOQ
SELECT  DISTINCT o.OrderSeq
    ,   o.OrderId
    ,   o.ReceiptOrderDate
    ,   o.Ent_OrderId
    ,   c.NameKj
    ,   c.PostalCode
    ,   c.UnitingAddress
    ,   c.Phone
    ,   cd.CombinedDictateGroup
    ,   cd.ErrorMsg
    ,   c.CustomerId
FROM    T_Order o
        INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq)
        INNER JOIN T_CombinedDictate cd ON (cd.OrderSeq = o.OrderSeq)
        INNER JOIN T_EnterpriseCustomer ec ON ec.EntCustSeq = c.EntCustSeq
        INNER JOIN T_ManagementCustomer mc ON mc.ManCustId = ec.ManCustId
WHERE   o.CombinedClaimTargetStatus IN (11, 12)
AND     o.DataStatus = 41
AND     o.Cnl_Status = 0
AND     (o.LetterClaimStopFlg IS NULL OR o.LetterClaimStopFlg <> 1)
AND     mc.RemindStopFlg = 0
AND     cd.CombinedStatus = 0
AND     (o.OutOfAmends IS NULL OR o.OutOfAmends <> 1)
AND     o.EnterpriseId = :EnterpriseId
AND     o.SiteId = :SiteId
EOQ;

        $stm = $this->_adapter->query($query);

        $prm = array(
                ':EnterpriseId' => $entId,
                ':SiteId' => $siteId,
        );

        return $stm->execute($prm);
    }

    /**
     * 注文がまとめ対象か判定
     * @param orderseq
     * @return bool
     */
    public function isMergeOrder($oseq) {

        $query = "
            SELECT
                ORD.OrderSeq
            FROM
                T_Order ORD INNER JOIN T_Customer CUS ON (ORD.OrderSeq = CUS.OrderSeq)
                INNER JOIN T_EnterpriseCustomer EC ON EC.EntCustSeq = CUS.EntCustSeq
                INNER JOIN T_ManagementCustomer MC ON MC.ManCustId = EC.ManCustId
            WHERE
                ORD.CombinedClaimTargetStatus in (1,2)  AND
                ORD.DataStatus = 41 AND
                ORD.Cnl_Status = 0 AND
                ORD.OrderSeq = :OrderSeq
                AND (
                    ORD.LetterClaimStopFlg IS null
                    OR ORD.LetterClaimStopFlg = 0
                )
                AND MC.RemindStopFlg = 0
                ";

        $stm = $this->_adapter->query($query);

        $prm = array(
                ':OrderSeq' => $oseq,
        );

        $ri = $stm->execute($prm);

        return $ri->count() > 0 ? true : false;
    }

    /**
     * 注文がまとめキャンセル対象か判定
     * @param orderseq
     * @return bool
     */
    public function isMergeOrderCancel($oseq) {

        $query = <<<EOQ
SELECT  DISTINCT o.OrderSeq
FROM    T_Order o
        INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq)
        INNER JOIN T_CombinedDictate cd ON (cd.OrderSeq = o.OrderSeq)
        INNER JOIN T_EnterpriseCustomer ec ON ec.EntCustSeq = c.EntCustSeq
        INNER JOIN T_ManagementCustomer mc ON mc.ManCustId = ec.ManCustId
WHERE   o.CombinedClaimTargetStatus IN (11, 12)
AND     o.DataStatus = 41
AND     o.Cnl_Status = 0
AND     (o.LetterClaimStopFlg IS NULL OR o.LetterClaimStopFlg <> 1)
AND     mc.RemindStopFlg = 0
AND     cd.CombinedStatus = 0
AND     (o.OutOfAmends IS NULL OR o.OutOfAmends <> 1)
AND     o.OrderSeq = :OrderSeq
EOQ;

        $stm = $this->_adapter->query($query);

        $prm = array(
                ':OrderSeq' => $oseq,
        );

        $ri = $stm->execute($prm);

        return $ri->count() > 0 ? true : false;
    }
}
