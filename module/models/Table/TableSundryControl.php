<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_SundryControl(雑収入・雑損失管理)テーブルへのアダプタ
 */
class TableSundryControl
{
    protected $_name = 'T_SundryControl';
    protected $_primary = array('SundrySeq');
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
     * 雑収入・雑損失管理データを取得する(有効フラグ＝有効データに限る)
     *
     * @param int $sundrySeq 雑収入・雑損失管理SEQ
     * @return ResultInterface
     */
    public function find($sundrySeq)
    {
        $sql = " SELECT * FROM T_SundryControl WHERE ValidFlg = 1 AND SundrySeq = :SundrySeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':SundrySeq' => $sundrySeq,
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
        $sql  = " INSERT INTO T_SundryControl (ProcessDate, SundryType, SundryAmount, SundryClass, OrderSeq, OrderId, ClaimId, Note, CheckingUseAmount, CheckingClaimFee, CheckingDamageInterestAmount, CheckingAdditionalClaimFee, DailySummaryFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :ProcessDate ";
        $sql .= " , :SundryType ";
        $sql .= " , :SundryAmount ";
        $sql .= " , :SundryClass ";
        $sql .= " , :OrderSeq ";
        $sql .= " , :OrderId ";
        $sql .= " , :ClaimId ";
        $sql .= " , :Note ";
        $sql .= " , :CheckingUseAmount ";
        $sql .= " , :CheckingClaimFee ";
        $sql .= " , :CheckingDamageInterestAmount ";
        $sql .= " , :CheckingAdditionalClaimFee ";
        $sql .= " , :DailySummaryFlg ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':ProcessDate' => $data['ProcessDate'],
                ':SundryType' => $data['SundryType'],
                ':SundryAmount' => $data['SundryAmount'],
                ':SundryClass' => $data['SundryClass'],
                ':OrderSeq' => $data['OrderSeq'],
                ':OrderId' => $data['OrderId'],
                ':ClaimId' => $data['ClaimId'],
                ':Note' => $data['Note'],
                ':CheckingUseAmount' => isset($data['CheckingUseAmount']) ? $data['CheckingUseAmount'] : 0,
                ':CheckingClaimFee' => isset($data['CheckingClaimFee']) ? $data['CheckingClaimFee'] : 0,
                ':CheckingDamageInterestAmount' => isset($data['CheckingDamageInterestAmount']) ? $data['CheckingDamageInterestAmount'] : 0,
                ':CheckingAdditionalClaimFee' => isset($data['CheckingAdditionalClaimFee']) ? $data['CheckingAdditionalClaimFee'] : 0,
                ':DailySummaryFlg' => isset($data['DailySummaryFlg']) ? $data['DailySummaryFlg'] : 0,
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
     * @param int $sundrySeq 雑収入・雑損失管理SEQ
     * @return ResultInterface
     */
    public function saveUpdate($data, $sundrySeq)
    {
        $row = $this->find($sundrySeq)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_SundryControl ";
        $sql .= " SET ";
        $sql .= "     ProcessDate = :ProcessDate ";
        $sql .= " ,   SundryType = :SundryType ";
        $sql .= " ,   SundryAmount = :SundryAmount ";
        $sql .= " ,   SundryClass = :SundryClass ";
        $sql .= " ,   OrderSeq = :OrderSeq ";
        $sql .= " ,   OrderId = :OrderId ";
        $sql .= " ,   ClaimId = :ClaimId ";
        $sql .= " ,   Note = :Note ";
        $sql .= " ,   CheckingUseAmount = :CheckingUseAmount ";
        $sql .= " ,   CheckingClaimFee = :CheckingClaimFee ";
        $sql .= " ,   CheckingDamageInterestAmount = :CheckingDamageInterestAmount ";
        $sql .= " ,   CheckingAdditionalClaimFee = :CheckingAdditionalClaimFee ";
        $sql .= " ,   DailySummaryFlg = :DailySummaryFlg ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE SundrySeq = :SundrySeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':SundrySeq' => $sundrySeq,
                ':ProcessDate' => $row['ProcessDate'],
                ':SundryType' => $row['SundryType'],
                ':SundryAmount' => $row['SundryAmount'],
                ':SundryClass' => $row['SundryClass'],
                ':OrderSeq' => $row['OrderSeq'],
                ':OrderId' => $row['OrderId'],
                ':ClaimId' => $row['ClaimId'],
                ':Note' => $row['Note'],
                ':CheckingUseAmount' => $row['CheckingUseAmount'],
                ':CheckingClaimFee' => $row['CheckingClaimFee'],
                ':CheckingDamageInterestAmount' => $row['CheckingDamageInterestAmount'],
                ':CheckingAdditionalClaimFee' => $row['CheckingAdditionalClaimFee'],
                ':DailySummaryFlg' => $row['DailySummaryFlg'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }
}
