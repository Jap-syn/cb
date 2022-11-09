<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_EnterpriseClaimHistory(加盟店請求履歴)テーブルへのアダプタ
 */
class TableEnterpriseClaimHistory
{
    protected $_name = 'T_EnterpriseClaimHistory';
    protected $_primary = array('EntClaimSeq');
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
     * 加盟店請求履歴データを取得する(有効フラグ＝有効データに限る)
     *
     * @param int $entClaimSeq 加盟店請求履歴SEQ
     * @return ResultInterface
     */
    public function find($entClaimSeq)
    {
        $sql = " SELECT * FROM T_EnterpriseClaimHistory WHERE ValidFlg = 1 AND EntClaimSeq = :EntClaimSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EntClaimSeq' => $entClaimSeq,
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
        $sql  = " INSERT INTO T_EnterpriseClaimHistory (EnterpriseId, PayingControlSeq, ClaimDate, ClaimAmount, PaymentAllocatedAmount, PaymentAllocatedFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :EnterpriseId ";
        $sql .= " , :PayingControlSeq ";
        $sql .= " , :ClaimDate ";
        $sql .= " , :ClaimAmount ";
        $sql .= " , :PaymentAllocatedAmount ";
        $sql .= " , :PaymentAllocatedFlg ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EnterpriseId' => $data['EnterpriseId'],
                ':PayingControlSeq' => $data['PayingControlSeq'],
                ':ClaimDate' => $data['ClaimDate'],
                ':ClaimAmount' => isset($data['ClaimAmount']) ? $data['ClaimAmount'] : 0,
                ':PaymentAllocatedAmount' => isset($data['PaymentAllocatedAmount']) ? $data['PaymentAllocatedAmount'] : 0,
                ':PaymentAllocatedFlg' => isset($data['PaymentAllocatedFlg']) ? $data['PaymentAllocatedFlg'] : 0,
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
     * @param int $entClaimSeq 加盟店請求履歴SEQ
     * @return ResultInterface
     */
    public function saveUpdate($data, $entClaimSeq)
    {
        $row = $this->find($entClaimSeq)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_EnterpriseClaimHistory ";
        $sql .= " SET ";
        $sql .= "     EnterpriseId = :EnterpriseId ";
        $sql .= " ,   PayingControlSeq = :PayingControlSeq ";
        $sql .= " ,   ClaimDate = :ClaimDate ";
        $sql .= " ,   ClaimAmount = :ClaimAmount ";
        $sql .= " ,   PaymentAllocatedAmount = :PaymentAllocatedAmount ";
        $sql .= " ,   PaymentAllocatedFlg = :PaymentAllocatedFlg ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE EntClaimSeq = :EntClaimSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EntClaimSeq' => $entClaimSeq,
                ':EnterpriseId' => $row['EnterpriseId'],
                ':PayingControlSeq' => $row['PayingControlSeq'],
                ':ClaimDate' => $row['ClaimDate'],
                ':ClaimAmount' => $row['ClaimAmount'],
                ':PaymentAllocatedAmount' => $row['PaymentAllocatedAmount'],
                ':PaymentAllocatedFlg' => $row['PaymentAllocatedFlg'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }
}
