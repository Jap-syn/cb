<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * AT_EnterpriseReceiptHistory(加盟店入金履歴_会計)テーブルへのアダプタ
 */
class ATableEnterpriseReceiptHistory
{
    protected $_name = 'AT_EnterpriseReceiptHistory';
    protected $_primary = array('EntRcptSeq');
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
     * 加盟店入金履歴_会計データを取得する
     *
     * @param int $entRcptSeq 加盟店入金SEQ
     * @return ResultInterface
     */
    public function find($entRcptSeq)
    {
        $sql = " SELECT * FROM AT_EnterpriseReceiptHistory WHERE EntRcptSeq = :EntRcptSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EntRcptSeq' => $entRcptSeq,
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
        $sql  = " INSERT INTO AT_EnterpriseReceiptHistory (EntRcptSeq, ReceiptAmountRece, ReceiptAmountDue, ReceiptAmountSource, DailySummaryFlg) VALUES (";
        $sql .= "   :EntRcptSeq ";
        $sql .= " , :ReceiptAmountRece ";
        $sql .= " , :ReceiptAmountDue ";
        $sql .= " , :ReceiptAmountSource ";
        $sql .= " , :DailySummaryFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EntRcptSeq' => $data['EntRcptSeq'],
                ':ReceiptAmountRece' => isset($data['ReceiptAmountRece']) ? $data['ReceiptAmountRece'] : 0,
                ':ReceiptAmountDue' => isset($data['ReceiptAmountDue']) ? $data['ReceiptAmountDue'] : 0,
                ':ReceiptAmountSource' => $data['ReceiptAmountSource'],
                ':DailySummaryFlg' => isset($data['DailySummaryFlg']) ? $data['DailySummaryFlg'] : 0,
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
    }

    /**
     * 指定されたレコードを更新する。
     *
     * @param array $data 更新内容
     * @param int $entRcptSeq 加盟店入金SEQ
     * @return ResultInterface
     */
    public function saveUpdate($data, $entRcptSeq)
    {
        $row = $this->find($entRcptSeq)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE AT_EnterpriseReceiptHistory ";
        $sql .= " SET ";
        $sql .= "     ReceiptAmountRece = :ReceiptAmountRece ";
        $sql .= " ,   ReceiptAmountDue = :ReceiptAmountDue ";
        $sql .= " ,   ReceiptAmountSource = :ReceiptAmountSource ";
        $sql .= " ,   DailySummaryFlg = :DailySummaryFlg ";
        $sql .= " WHERE EntRcptSeq = :EntRcptSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EntRcptSeq' => $entRcptSeq,
                ':ReceiptAmountRece' => $row['ReceiptAmountRece'],
                ':ReceiptAmountDue' => $row['ReceiptAmountDue'],
                ':ReceiptAmountSource' => $row['ReceiptAmountSource'],
                ':DailySummaryFlg' => $row['DailySummaryFlg'],
        );

        return $stm->execute($prm);
    }
}
