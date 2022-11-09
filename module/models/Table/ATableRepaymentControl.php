<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * AT_RepaymentControl(返金管理_会計)テーブルへのアダプタ
 */
class ATableRepaymentControl
{
    protected $_name = 'AT_RepaymentControl';
    protected $_primary = array('RepaySeq');
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
     * 注文_会計データを取得する
     *
     * @param int $repaySeq 返金SEQ
     * @return ResultInterface
     */
    public function find($repaySeq)
    {
        $sql = " SELECT * FROM AT_RepaymentControl WHERE RepaySeq = :RepaySeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':RepaySeq' => $repaySeq,
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
        $sql  = " INSERT INTO AT_RepaymentControl (RepaySeq, DailySummaryFlg) VALUES (";
        $sql .= "   :RepaySeq ";
        $sql .= " , :DailySummaryFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':RepaySeq' => $data['RepaySeq'],
                ':DailySummaryFlg' => isset($data['DailySummaryFlg']) ? $data['DailySummaryFlg'] : 0,
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
    }

    /**
     * 指定されたレコードを更新する。
     *
     * @param array $data 更新内容
     * @param int $repaySeq 返金SEQ
     * @return ResultInterface
     */
    public function saveUpdate($data, $repaySeq)
    {
        $row = $this->find($repaySeq)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE AT_RepaymentControl ";
        $sql .= " SET ";
        $sql .= "     DailySummaryFlg = :DailySummaryFlg ";
        $sql .= " WHERE RepaySeq = :RepaySeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':RepaySeq' => $repaySeq,
                ':DailySummaryFlg' => $row['DailySummaryFlg'],
        );

        return $stm->execute($prm);
    }
}
