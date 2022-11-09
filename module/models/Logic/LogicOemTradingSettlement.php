<?php
namespace models\Logic;

use Zend\Db\Adapter\Adapter;
use models\Table\TableOemClaimed;

/**
 * OEM取引別精算情報を扱うロジック
 */
class LogicOemTradingSettlement {

    /**
     * DBアダプタ
     *
     * @var Adapter
     */
    private $db;

    /**
     * コンストラクタ
     *
     * @param Adapter $dbAdapter DBアダプタ
     *
     */
    function __construct($dbAdapter)
    {
        $this->db = $dbAdapter;
    }

    /**
     * OEM取引別精算明細取得
     * @param int $oemClaimedSeq T_OemClaimedのSeq
     * @return array $data OEM取引別精算明細データ
     */
    public function getOemTradingSettlement($oemClaimedSeq) {
        $q = <<<EOQ
SELECT
    ORD.OrderId AS OrderId,
    ORD.Ent_OrderId AS Ent_OrderId,
    CUS.NameKj AS NameKj,
    ORD.ReceiptOrderDate AS ReceiptOrderDate,
    ITM.Deli_JournalIncDate AS Deli_JournalIncDate,
    ORD.Chg_FixedDate AS Chg_FixedDate,
    ORD.UseAmount /*+ ORD.Clm_L_DamageInterestAmount + ORD.Clm_L_ClaimFee*/ AS UseAmount,
    PAS.SettlementFee AS SettlementFeeTotal,
    PAS.SettlementFee - OSF.SettlementFee AS SettlementFeeCB,
    OSF.SettlementFee AS SettlementFeeOEM,
    CASE WHEN OCF.ClaimFeeType = 1 THEN PAS.ClaimFee ELSE 0 END AS ClaimFeeTypeTotal,
    CASE WHEN OCF.ClaimFeeType = 1 THEN PAS.ClaimFee - OCF.ClaimFee ELSE 0 END AS ClaimFeeTypeCB,
    CASE WHEN OCF.ClaimFeeType = 1 THEN OCF.ClaimFee ELSE 0 END AS ClaimFeeTypeOEM,
    CASE WHEN OCF.ClaimFeeType = 2 THEN PAS.ClaimFee ELSE 0 END AS ClaimFeeType2Total,
    CASE WHEN OCF.ClaimFeeType = 2 THEN PAS.ClaimFee - OCF.ClaimFee ELSE 0 END AS ClaimFeeType2CB,
    CASE WHEN OCF.ClaimFeeType = 2 THEN OCF.ClaimFee ELSE 0 END AS ClaimFeeType2OEM
FROM
    T_Order ORD INNER JOIN T_Customer CUS ON (ORD.OrderSeq = CUS.OrderSeq)
    INNER JOIN T_OrderItems ITM ON (ORD.OrderSeq = ITM.OrderSeq AND ITM.DataClass = 3)
    INNER JOIN T_PayingAndSales PAS ON (ORD.OrderSeq = PAS.OrderSeq)
    INNER JOIN T_OemSettlementFee OSF ON (ORD.OrderSeq = OSF.OrderSeq)
    INNER JOIN T_OemClaimFee OCF ON (ORD.OrderSeq = OCF.OrderSeq)
    INNER JOIN T_PayingControl PC ON (PC.Seq = PAS.PayingControlSeq)
WHERE
    PC.OemClaimedSeq = :OemClaimedSeq
EOQ;
        return ResultInterfaceToArray( $this->db->query($q)->execute(array(':OemClaimedSeq' => $oemClaimedSeq)) );
    }

    /**
     * OEM取引別精算CSVデータ取得
     * @param int $OemId
     * @return array $data OEM取引別精算明細データ
     */
    public function getOemTradingSettlementCsv($oemId,$from,$to) {

        $data = array();
        $oemSeqs = $this->getOemClaimedSeqs($oemId,$from,$to);

        if(empty($oemSeqs)){
            return $data;
        }


        $q = <<<EOQ
SELECT
    ORD.ReceiptOrderDate,
    ORD.OrderId,
    ORD.Ent_OrderId,
    CUS.NameKj,
    PAS.UseAmount,
    OSF.SettlementFee,
    PAS.ClaimFee,
    EP.EnterpriseNameKj,
    ST.SiteNameKj,
    ORD.SiteId,
    RC.ReceiptClass,
    ORD.EnterpriseId,
    EP.Plan as Plan,
    ORD.Cnl_Status,
    OSF.AppSettlementFeeRate
FROM
    T_Order ORD INNER JOIN T_Customer CUS ON (ORD.OrderSeq = CUS.OrderSeq)
    INNER JOIN T_PayingAndSales PAS ON (ORD.OrderSeq = PAS.OrderSeq)
    INNER JOIN T_PayingControl PC ON (PC.Seq = PAS.PayingControlSeq)
    INNER JOIN T_Site ST ON (ORD.SiteId = ST.SiteId)
    INNER JOIN T_Enterprise EP ON (ORD.EnterpriseId = EP.EnterpriseId)
    LEFT OUTER JOIN T_ClaimControl CC ON (ORD.OrderSeq = CC.OrderSeq)
    INNER JOIN T_OemClaimed OC ON (PC.OemClaimedSeq = OC.OemClaimedSeq)
    LEFT OUTER JOIN T_ReceiptControl RC ON (CC.LastReceiptSeq = RC.ReceiptSeq)
    INNER JOIN T_OemSettlementFee OSF ON (ORD.OrderSeq = OSF.OrderSeq)
WHERE
OC.OemClaimedSeq IN ( $oemSeqs )
EOQ;

        $data = ResultInterfaceToArray( $this->db->query( $q )->execute( null ) );
        return $data;
    }


    /**
     * OEMキャンセル情報取得
     * @param int $oemId OEMID
     * @param date $from　検索開始日
     * @param date $to 検索終了日
     * @param char $enterpriseId  事業者ID
     * @return array $data OEMキャンセル情報
     */
    public function getOemCancel($oemId,$from=null,$to=null,$enterpriseId=null) {

        $data = array();

        $oemSeqs = $this->getOemClaimedSeqs($oemId,$from,$to);

        if(empty($oemSeqs)){
            return $data;
        }

        // OEM精算仮締め対象外有無確認
        $sql = "SELECT Class1 FROM M_Code WHERE CodeId = 221 AND KeyCode = :OemId";
        $class1 = $this->db->query($sql)->execute(array(':OemId'=>$oemInfo['OemId']))->current()["Class1"];

        if($class1 == "1"){

        $q = <<<EOQ
SELECT
    ENT.EnterpriseId,
    ENT.EnterpriseNameKj AS EnterpriseName,
    SUM(CNL.RepayTotal) AS RepayTotal,
    SUM(IFNULL(OCF.ClaimFee,0) + OSF.SettlementFee) AS CbRefund,
    SUM(PAS.ClaimFee + PAS.SettlementFee - IFNULL(OCF.ClaimFee,0) - OSF.SettlementFee) AS OemRefund
FROM
    T_PayingControl PC
    INNER JOIN T_Cancel CNL ON (PC.Seq = CNL.PayingControlSeq)
    INNER JOIN T_PayingAndSales PAS ON (CNL.OrderSeq = PAS.OrderSeq/* AND PC.Seq = PAS.PayingControlSeq*/)
    LEFT OUTER JOIN T_OemClaimFee OCF ON (CNL.OrderSeq = OCF.OrderSeq)
    INNER JOIN T_OemSettlementFee OSF ON (CNL.OrderSeq = OSF.OrderSeq)
    INNER JOIN T_Oem OEM ON (PC.OemId = OEM.OemId)
    INNER JOIN T_Enterprise ENT ON (PC.EnterpriseId = ENT.EnterpriseId)
%s
GROUP BY
    ENT.EnterpriseId
EOQ;
        }else{
            $q = <<<EOQ
SELECT
    ENT.EnterpriseId,
    ENT.EnterpriseNameKj AS EnterpriseName,
    SUM(CNL.RepayTotal) AS RepayTotal,
    SUM(IFNULL(OCF.ClaimFee,0) + OSF.SettlementFee) AS CbRefund,
    SUM(PAS.ClaimFee + PAS.SettlementFee - IFNULL(OCF.ClaimFee,0) - OSF.SettlementFee) AS OemRefund
FROM
    T_PayingControl PC
    INNER JOIN T_Cancel CNL ON (PC.Seq = CNL.PayingControlSeq)
    INNER JOIN T_PayingAndSales PAS ON (CNL.OrderSeq = PAS.OrderSeq/* AND PC.Seq = PAS.PayingControlSeq*/)
    LEFT OUTER JOIN T_OemClaimFee OCF ON (CNL.OrderSeq = OCF.OrderSeq)
    INNER JOIN T_OemSettlementFee OSF ON (CNL.OrderSeq = OSF.OrderSeq)
    INNER JOIN T_Oem OEM ON (PC.OemId = OEM.OemId)
    INNER JOIN T_Enterprise ENT ON (PC.EnterpriseId = ENT.EnterpriseId)
    INNER JOIN AT_Order AO ON (CNL.OrderSeq = AO.OrderSeq AND AO.ExtraPayType IS NULL)
%s
GROUP BY
    ENT.EnterpriseId
EOQ;
        }
        $where = 'WHERE PC.OemClaimedSeq in ('.$oemSeqs.')';

        //事業者IDの検索条件があれば追加
        $prms = array();
        if(!is_null($enterpriseId)){
            $where .= (" AND PC.EnterpriseId = :EnterpriseId ");
            $prms += array(':EnterpriseId' => $enterpriseId);
        }
        // 2014.12.18 お金の動きがあるキャンセルフェイズに絞る
        $where .= ' AND CNL.CancelPhase IN (2,3,4) ';

        return ResultInterfaceToArray( $this->db->query(sprintf($q, $where))->execute($prms) );
    }

    /**
     * OEMキャンセル詳細情報取得
     * @param int $oemId
     * @param int $enterpriseId
     * @param date $from
     * @param date $to
     * @return array $data OEMキャンセル情報
     */
    public function getOemCancelDetail($oemId,$enterpriseId,$from=null,$to=null) {

        $data = array();
        $oemSeqs = $this->getOemClaimedSeqs($oemId,$from,$to);

        if(empty($oemSeqs)){
            return $data;
        }


$q = <<<EOQ
SELECT
    ORD.OrderId AS OrderId,
    ORD.Ent_OrderId AS Ent_OrderId,
    CUS.NameKj AS NameKj,
    ORD.ReceiptOrderDate AS ReceiptOrderDate,
    ORD.Chg_FixedDate AS Chg_FixedDate,
    CNL.CancelDate AS CancelDate,
    ORD.UseAmount AS UseAmount,
    CNL.RepayTotal AS RepayTotal,
    IFNULL(OCF.ClaimFee,0) + OSF.SettlementFee AS CbFee,
    PAS.ClaimFee + PAS.SettlementFee - IFNULL(OCF.ClaimFee,0) - OSF.SettlementFee AS OemFee
FROM
    T_PayingControl PC
    INNER JOIN T_Cancel CNL ON (PC.Seq = CNL.PayingControlSeq)
    INNER JOIN T_PayingAndSales PAS ON (CNL.OrderSeq = PAS.OrderSeq/* AND PC.Seq = PAS.PayingControlSeq*/)
    INNER JOIN T_Order ORD ON (CNL.OrderSeq = ORD.OrderSeq)
    INNER JOIN T_Customer CUS ON (ORD.OrderSeq = CUS.OrderSeq)
    LEFT OUTER JOIN T_OemClaimFee OCF ON (CNL.OrderSeq = OCF.OrderSeq)
    INNER JOIN T_OemSettlementFee OSF ON (CNL.OrderSeq = OSF.OrderSeq)
WHERE 1 = 1
%s
EOQ;
        $where = 'AND PC.OemClaimedSeq in ('.$oemSeqs.')';

        //事業者IDの検索条件があれば追加
        $prms = array();
        if(!is_null($enterpriseId)){
            $where .= (" AND PC.EnterpriseId = :EnterpriseId ");
            $prms += array(':EnterpriseId' => $enterpriseId);
        }
        // 2014.12.18 お金の動きがあるキャンセルフェイズに絞る
        $where .= ' AND CNL.CancelPhase IN (2,3,4) ';

        return ResultInterfaceToArray( $this->db->query(sprintf($q, $where))->execute($prms) );
    }
    /**
     * 対象期間のOemClaimedを取得する
     */
    protected function getOemClaimedSeqs($oemId,$from,$to){
        $oemSeqs = "";

        //T_OemClaimed
        $mdloc = new TableOemClaimed($this->db);

        $oem_claimed_data = $mdloc->findOemClaimed($oemId,$from,$to);

        if (!($oem_claimed_data->count() > 0)) {
            return $oemSeqs;
        }

        foreach($oem_claimed_data as $value){
            if(empty($oemSeqs)){
                $oemSeqs = $value['OemClaimedSeq'];
            }else{
                $oemSeqs .= ",".$value['OemClaimedSeq'];
            }

        }
        return $oemSeqs;
    }

    /**
     * OEM調整額情報取得
     * @param int $oemClaimedSeq
     * @return array $data OEM調整額情報
     */
    public function getOemAdjustmentAmount($oemClaimedSeq) {

        $q = <<<EOQ
            SELECT
                  ( SELECT KeyContent FROM M_Code WHERE CodeId = 89 AND KeyCode = OAA.ItemCode ) AS ItemCodeName
                 ,OAA.AdjustmentAmount
                 ,OAA.RegistDate
                 ,F_GetLoginUserName(OAA.RegistId) AS RegistName
            FROM  T_OemAdjustmentAmount AS OAA
            WHERE OAA.OemClaimedSeq = :OemClaimedSeq
EOQ;
        return ResultInterfaceToArray( $this->db->query($q)->execute(array(':OemClaimedSeq' => $oemClaimedSeq)) );
    }

}
