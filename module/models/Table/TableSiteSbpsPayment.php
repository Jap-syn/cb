<?php

namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_SiteSbpsPaymentテーブルへのアダプタ
 */
class TableSiteSbpsPayment
{
    protected $_name = 'T_SiteSbpsPayment';
    protected $_primary = array('SiteSbpsPaymentId');
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
     * サイトのすべての支払方法データを取得する
     *
     * @param int $siteId サイトID
     * @return ResultInterface
     */
    public function getAll($siteId, $isValid = true)
    {
        $sql = " SELECT * FROM " . $this->_name . " WHERE SiteId = :SiteId  ";
        if ($isValid) $sql .= " AND ValidFlg = 1 ";
        $sql .= " ORDER BY PaymentId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
            ':SiteId' => $siteId,
        );

        return $stm->execute($prm);
    }

    /**
     * Get all valid settings
     */
    public function getAllValidSite($valid = 1)
    {
        $sql = " SELECT DISTINCT SiteId FROM " . $this->_name . " WHERE ValidFlg = :ValidFlg ";
        $stm = $this->_adapter->query($sql);
        $prm = array(
            ':ValidFlg' => $valid,
        );
        return $stm->execute($prm);
    }

    protected function querySave()
    {
        $insert = " INSERT INTO " . $this->_name . " (SiteId, PaymentId, ContractorId, SettlementFeeRate, ClaimFeeBS, ClaimFeeDK, NumUseDay, ValidFlg, UseStartDate, RegistDate, RegistId, UpdateDate, UpdateId) VALUES (";
        $insert .= "  :SiteId ";
        $insert .= " , :PaymentId ";
        $insert .= " , :ContractorId ";
        $insert .= " , :SettlementFeeRate ";
        $insert .= " , :ClaimFeeBS ";
        $insert .= " , :ClaimFeeDK ";
        $insert .= " , :NumUseDay ";
        $insert .= " , :ValidFlg ";
        $insert .= " , :UseStartDate ";
        $insert .= " , :RegistDate ";
        $insert .= " , :RegistId ";
        $insert .= " , :UpdateDate ";
        $insert .= " , :UpdateId ";
        $insert .= " )";

        return $insert;
    }

    protected function queryUpdate()
    {
        $update = " UPDATE " . $this->_name . " SET ";
        $update .= "   ContractorId=:ContractorId ";
        $update .= " , SettlementFeeRate=:SettlementFeeRate ";
        $update .= " , ClaimFeeBS=:ClaimFeeBS ";
        $update .= " , ClaimFeeDK=:ClaimFeeDK ";
        $update .= " , NumUseDay=:NumUseDay ";
        $update .= " , SiteId=:SiteId ";
        $update .= " , PaymentId=:PaymentId ";
        $update .= " , ValidFlg=:ValidFlg ";
        $update .= " , UseStartDate=:UseStartDate ";
        $update .= " , UpdateDate=:UpdateDate ";
        $update .= " , UpdateId=:UpdateId ";
        $update .= " WHERE SiteSbpsPaymentId=:SiteSbpsPaymentId";

        return $update;
    }

    /**
     * 新しいレコードをインサートする。
     *
     * @param int $siteId サイトID
     * @param int $registId 登録者ID
     * @param array $data インサートする連想配列
     * @return プライマリキーのバリュー？
     */
    public function handle($siteId, $registId, $data)
    {

        $insstm = $this->_adapter->query($this->querySave());
        $updstm = $this->_adapter->query($this->queryUpdate());

        $get_sql = " SELECT PaymentId FROM " . $this->_name . " WHERE SiteId = :SiteId";
        $getstm = $this->_adapter->query($get_sql);
        $record_work = ResultInterfaceToArray($getstm->execute(array(':SiteId' => $siteId)));
        $records = array();
        foreach ($record_work as $work) {
            $records[$work['PaymentId']] = 1;
        }

        $select = " SELECT SiteSbpsPaymentId FROM " . $this->_name . " WHERE SiteId = :SiteId AND PaymentId = :PaymentId";
        $selstm = $this->_adapter->query($select);
        foreach ($data as $key => $payment) {
            if (array_key_exists($key, $records)) {
                unset($records[$key]);
            }

            $prm = array(
                ':ContractorId' => $payment['ContractorId'],
                ':SettlementFeeRate' => isset($payment['SettlementFeeRate']) && trim($payment['SettlementFeeRate']) != '' ? $payment['SettlementFeeRate'] : null,
                ':ClaimFeeBS' => isset($payment['ClaimFeeBS']) && trim($payment['ClaimFeeBS']) != '' ? $payment['ClaimFeeBS'] : null,
                ':ClaimFeeDK' => isset($payment['ClaimFeeDK']) && trim($payment['ClaimFeeDK']) != '' ? $payment['ClaimFeeDK'] : null,
                ':NumUseDay' => empty($payment['NumUseDay']) ? null : $payment['NumUseDay'],
                ':SiteId' => $siteId,
                ':PaymentId' => $key,
                ':ValidFlg' => is_null($payment['ValidFlg']) ? 0 : $payment['ValidFlg'],
                ':UseStartDate' => ($payment['UseStartDate'] == '') ? null : $payment['UseStartDate'],
                ':RegistDate' => date('Y-m-d H:i:s'),
                ':RegistId' => $registId,
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $registId,
            );

            $mdl = $selstm->execute(array(':SiteId' => $siteId, ':PaymentId' => $key))->current();
            if (!$mdl) {
                $insstm->execute($prm);
            } else {
                $prm[':SiteSbpsPaymentId'] = $mdl['SiteSbpsPaymentId'];
                unset($prm[':RegistDate']);
                unset($prm[':RegistId']);
                $updstm->execute($prm);
            }
        }

        // マスタ存在しないものは、無効にする
        if (sizeof($records) > 0) {
            $updstm2 = $this->_adapter->query("UPDATE " . $this->_name . " SET ValidFlg=0 WHERE SiteId = :SiteId AND PaymentId = :PaymentId");
            foreach ($records as $key => $val) {
                $prm = array(
                    ':SiteId' => $siteId,
                    ':PaymentId' => $key,
                );
                $updstm2->execute($prm);
            }
        }
    }


    public function getMaxNumUseDay($siteId, $claimDate)
    {
        $sql = "SELECT MAX(NumUseDay) as maxNumUseDay FROM " . $this->_name . " WHERE SiteId = :SiteId";
        $sql .= " AND ValidFlg = 1 AND UseStartDate <= :Now";
        if (!empty($claimDate)) {
            $sql .= " AND DATE_ADD(:ClaimDate, INTERVAL NumUseDay DAY) >= CURRENT_DATE()";
        }
        $stm = $this->_adapter->query($sql);
        $prm = array(
            ':SiteId' => $siteId,
            ':ClaimDate' => $claimDate,
            ':Now' => date('Y-m-d H:i:s')
        );

        return $stm->execute($prm)->current()['maxNumUseDay'];
    }
}
