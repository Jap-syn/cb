<?php

namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_SitePaymentテーブルへのアダプタ
 */
class TableSitePayment
{
    protected $_name = 'T_Site';
    protected $_primary = array('SiteId');
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
    public function getAll($siteId)
    {
        $sql = " SELECT * FROM T_SitePayment WHERE SiteId = :SiteId AND ValidFlg = 1 ORDER BY PaymentId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
            ':SiteId' => $siteId,
        );

        return $stm->execute($prm);
    }

    /**
     * 新しいレコードをインサートする。
     *
     * @param int $siteId サイトID
     * @param int $registId 登録者ID
     * @param array $data インサートする連想配列
     * @return プライマリキーのバリュー？
     */
    public function save($siteId, $registId, $data)
    {
        $insert = " INSERT INTO T_SitePayment (SiteId, PaymentId, UseFlg, ApplyDate, UseStartDate, ApplyFinishDate, UseStartFixFlg, RegistDate, RegistId, UpdateDate, UpdateId) VALUES (";
        $insert .= "   :SiteId ";
        $insert .= " , :PaymentId ";
        $insert .= " , :UseFlg ";
        $insert .= " , :ApplyDate ";
        $insert .= " , :UseStartDate ";
        $insert .= " , :ApplyFinishDate ";
        $insert .= " , :UseStartFixFlg ";
        $insert .= " , :RegistDate ";
        $insert .= " , :RegistId ";
        $insert .= " , :UpdateDate ";
        $insert .= " , :UpdateId ";
        $insert .= " )";
        $insstm = $this->_adapter->query($insert);

        $update = " UPDATE T_SitePayment SET ";
        $update .= "   SiteId=:SiteId ";
        $update .= " , PaymentId=:PaymentId ";
        $update .= " , UseFlg=:UseFlg ";
        $update .= " , ApplyDate=:ApplyDate ";
        $update .= " , UseStartDate=:UseStartDate ";
        $update .= " , ApplyFinishDate=:ApplyFinishDate ";
        $update .= " , UseStartFixFlg=:UseStartFixFlg ";
        $update .= " , ValidFlg=1 ";
        $update .= " , UpdateDate=:UpdateDate ";
        $update .= " , UpdateId=:UpdateId ";
        $update .= " WHERE SitePaymentId=:SitePaymentId";
        $updstm = $this->_adapter->query($update);

        $get_sql = " SELECT PaymentId FROM T_SitePayment WHERE SiteId = :SiteId";
        $getstm = $this->_adapter->query($get_sql);
        $record_work = ResultInterfaceToArray($getstm->execute(array(':SiteId' => $siteId)));
        $records = array();
        foreach ($record_work as $work) {
            $records[$work['PaymentId']] = 1;
        }

        $select = " SELECT SitePaymentId,ApplyFinishDate FROM T_SitePayment WHERE SiteId = :SiteId AND PaymentId = :PaymentId";
        $selstm = $this->_adapter->query($select);
        foreach ($data as $key => $payment) {
            if (array_key_exists($key, $records)) {
                unset($records[$key]);
            }

            $prm = array(
                ':SiteId' => $siteId,
                ':PaymentId' => $key,
                ':UseFlg' => is_null($payment['UseFlg']) ? 0 : $payment['UseFlg'],
                ':ApplyDate' => ($payment['ApplyDate'] == '') ? null : $payment['ApplyDate'],
                ':UseStartDate' => ($payment['UseStartDate'] == '') ? null : $payment['UseStartDate'],
                ':UseStartFixFlg' => is_null($payment['UseStartFixFlg']) ? 0 : $payment['UseStartFixFlg'],
                ':RegistDate' => date('Y-m-d H:i:s'),
                ':RegistId' => $registId,
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $registId,
            );

            $mdl = $selstm->execute(array(':SiteId' => $siteId, ':PaymentId' => $key))->current();
            // 利用開始確定の場合は、本日の日付を申請完了日に設定する
            if ($payment['UseStartFixFlg'] == 1) {
                $prm[':ApplyFinishDate'] = date('Y-m-d');
            } else {
                $prm[':ApplyFinishDate'] = null;
            }
            if (!$mdl) {
                $insstm->execute($prm);
            } else {
//                // 画面の利用開始確定チェック ＝ ON かつ サイト支払方法．申請完了日＝NULLの場合は、本日の日付を申請完了日に設定する。
//                if ($payment['UseStartFixFlg'] == 1) {
//                    $this->_adapter->query('UPDATE T_SitePayment SET ApplyFinishDate=:ApplyFinishDate WHERE SitePaymentId=:SitePaymentId AND ApplyFinishDate IS NULL')->execute(array(':ApplyFinishDate'=>date('Y-m-d'), ':SitePaymentId'=>$mdl['SitePaymentId']));
//                }
                $prm[':SitePaymentId'] = $mdl['SitePaymentId'];
                unset($prm[':RegistDate']);
                unset($prm[':RegistId']);
                $updstm->execute($prm);
            }
        }

        // マスタ存在しないものは、無効にする
        if (sizeof($records) > 0) {
            $updstm2 = $this->_adapter->query('UPDATE T_SitePayment SET ValidFlg=0 WHERE SiteId = :SiteId AND PaymentId = :PaymentId');
            foreach ($records as $key => $val) {
                $prm = array(
                    ':SiteId' => $siteId,
                    ':PaymentId' => $key,
                );
                $updstm2->execute($prm);
            }
        }
    }

    public function saveAnotherSite($siteId, $updateId, $data)
    {
        $sql = " UPDATE T_SitePayment SET ";
        $sql .= "   UseStartDate=:UseStartDate ";
        $sql .= " , UseStartFixFlg=1 ";
        $sql .= " , UpdateDate=:UpdateDate ";
        $sql .= " , UpdateId=:UpdateId ";
        $sql .= " WHERE UseStartFixFlg=0 ";
        $sql .= " AND PaymentId=:PaymentId ";
        $sql .= " AND SiteId IN (SELECT SiteId FROM T_Site WHERE ReceiptAgentId=:ReceiptAgentId AND SubscriberCode=:SubscriberCode) ";
        $sql .= " AND SiteId != :SiteId ";
        $stm = $this->_adapter->query($sql);

        $prm = array(
            ':UseStartDate' => ($data['UseStartDate'] == '') ? null : $data['UseStartDate'],
            ':UpdateDate' => date('Y-m-d H:i:s'),
            ':UpdateId' => $updateId,
            ':PaymentId' => $data['PaymentId'],
            ':SiteId' => $siteId,
            ':ReceiptAgentId' => $data['ReceiptAgentId'],
            ':SubscriberCode' => $data['SubscriberCode'],
        );
        $stm->execute($prm);
    }
}
