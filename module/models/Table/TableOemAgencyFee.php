<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_OemAgencyFee(OEM代理店手数料)テーブルへのアダプタ
 */
class TableOemAgencyFee
{
    protected $_name = 'T_OemAgencyFee';
    protected $_primary = array('Seq');
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
     * OEM代理店手数料データを取得する(有効フラグ＝有効データに限る)
     *
     * @param int $seq シーケンス
     * @return ResultInterface
     */
    public function find($seq)
    {
        $sql = " SELECT * FROM T_OemAgencyFee WHERE ValidFlg = 1 AND Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
        );

        return $stm->execute($prm);
    }

    /**
     * 新しいレコードをインサートする。
     *
     * @param array $data インサートする連想配列
     * @return プライマリキーのバリュー
     */
    public function saveNew($data)
    {
        $sql  = " INSERT INTO T_OemAgencyFee (OrderSeq, OemId, EnterpriseId, SiteId, AgencyId, OccDate, UseAmount, AgencyFeeRate, AgencyDivideFeeRate, AgencyFee, AddUpFlg, CancelAddUpFlg, AddUpFixedMonth, OemClaimedSeq, CancelFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :OrderSeq ";
        $sql .= " , :OemId ";
        $sql .= " , :EnterpriseId ";
        $sql .= " , :SiteId ";
        $sql .= " , :AgencyId ";
        $sql .= " , :OccDate ";
        $sql .= " , :UseAmount ";
        $sql .= " , :AgencyFeeRate ";
        $sql .= " , :AgencyDivideFeeRate ";
        $sql .= " , :AgencyFee ";
        $sql .= " , :AddUpFlg ";
        $sql .= " , :CancelAddUpFlg ";
        $sql .= " , :AddUpFixedMonth ";
        $sql .= " , :OemClaimedSeq ";
        $sql .= " , :CancelFlg ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $data['OrderSeq'],
                ':OemId' => $data['OemId'],
                ':EnterpriseId' => $data['EnterpriseId'],
                ':SiteId' => $data['SiteId'],
                ':AgencyId' => $data['AgencyId'],
                ':OccDate' => $data['OccDate'],
                ':UseAmount' => $data['UseAmount'],
                ':AgencyFeeRate' => $data['AgencyFeeRate'],
                ':AgencyDivideFeeRate' => $data['AgencyDivideFeeRate'],
                ':AgencyFee' => $data['AgencyFee'],
                ':AddUpFlg' => $data['AddUpFlg'],
                ':CancelAddUpFlg' => $data['CancelAddUpFlg'],
                ':AddUpFixedMonth' => $data['AddUpFixedMonth'],
                ':OemClaimedSeq' => $data['OemClaimedSeq'],
                ':CancelFlg' => $data['CancelFlg'],
                ':RegistDate' => date('Y-m-d H:i:s'),
                ':RegistId' => $data['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $data['UpdateId'],
                ':ValidFlg' => isset($data['ValidFlg']) ? $data['ValidFlg'] : 1,
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
    }

    /**
     * 指定されたレコードを更新する。
     *
     * @param array $data 更新内容
     * @param int $seq シーケンス
     * @return ResultInterface
     */
    public function saveUpdate($data, $seq)
    {
        $row = $this->find($seq)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_OemAgencyFee ";
        $sql .= " SET ";
        $sql .= "     OrderSeq = :OrderSeq ";
        $sql .= " ,   OemId = :OemId ";
        $sql .= " ,   EnterpriseId = :EnterpriseId ";
        $sql .= " ,   SiteId = :SiteId ";
        $sql .= " ,   AgencyId = :AgencyId ";
        $sql .= " ,   OccDate = :OccDate ";
        $sql .= " ,   UseAmount = :UseAmount ";
        $sql .= " ,   AgencyFeeRate = :AgencyFeeRate ";
        $sql .= " ,   AgencyDivideFeeRate = :AgencyDivideFeeRate ";
        $sql .= " ,   AgencyFee = :AgencyFee ";
        $sql .= " ,   AddUpFlg = :AddUpFlg ";
        $sql .= " ,   CancelAddUpFlg = :CancelAddUpFlg ";
        $sql .= " ,   AddUpFixedMonth = :AddUpFixedMonth ";
        $sql .= " ,   OemClaimedSeq = :OemClaimedSeq ";
        $sql .= " ,   CancelFlg = :CancelFlg ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
                ':OrderSeq' => $row['OrderSeq'],
                ':OemId' => $row['OemId'],
                ':EnterpriseId' => $row['EnterpriseId'],
                ':SiteId' => $row['SiteId'],
                ':AgencyId' => $row['AgencyId'],
                ':OccDate' => $row['OccDate'],
                ':UseAmount' => $row['UseAmount'],
                ':AgencyFeeRate' => $row['AgencyFeeRate'],
                ':AgencyDivideFeeRate' => $row['AgencyDivideFeeRate'],
                ':AgencyFee' => $row['AgencyFee'],
                ':AddUpFlg' => $row['AddUpFlg'],
                ':CancelAddUpFlg' => $row['CancelAddUpFlg'],
                ':AddUpFixedMonth' => $row['AddUpFixedMonth'],
                ':OemClaimedSeq' => $row['OemClaimedSeq'],
                ':CancelFlg' => $row['CancelFlg'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }

    /**
     * 指定された条件でレコードを更新する。
     *
     * @param array $data 更新内容
     * @param array $conditionArray
     */
    public function saveUpdateWhere($data, $conditionArray)
    {
        $prm = array();
        $sql  = " SELECT * FROM T_OemAgencyFee WHERE 1 = 1 ";
        foreach ($conditionArray as $key => $value) {
            $sql .= (" AND " . $key . " = :" . $key);
            $prm += array(':' . $key => $value);
        }

        $stm = $this->_adapter->query($sql);

        $ri = $stm->execute($prm);

        foreach ($ri AS $row) {
            foreach ($data as $key => $value) {
                if (array_key_exists($key, $row)) {
                    $row[$key] = $value;
                }
            }

            // 指定されたレコードを更新する
            $this->saveUpdate($row, $row['Seq']);
        }
    }
}
