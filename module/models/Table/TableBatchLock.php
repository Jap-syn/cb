<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
T_BatchLock(バッチ排他制御)テーブルへのアダプタ
 */
class TableBatchLock
{
    protected $_name = 'T_BatchLock';
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
     * バッチ排他制御情報データを取得する
     *
     * @param int $seq SEQ
     * @return ResultInterface
     */
    public function find($seq)
    {
        $sql  = " SELECT * FROM T_BatchLock WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
        );

        return $stm->execute($prm);
    }

    /**
     * IDとThreadNo一致するバッチ排他制御情報を取得する
     * @param int $batchId
     * @param int $threadNo
     * @return ResultInterface
     */
    public function findId($batchId, $threadNo)
    {
        $sql  = " SELECT * FROM T_BatchLock WHERE BatchId = :BatchId AND ThreadNo = :ThreadNo";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':BatchId' => $batchId,
                ':ThreadNo' => $threadNo,
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
        $sql  = " INSERT INTO T_BatchLock (BatchId, ThreadNo, BatchName, BatchLock, UpdateDate) VALUES (";
        $sql .= "   :BatchId ";
        $sql .= " , :ThreadNo ";
        $sql .= " , :BatchName ";
        $sql .= " , :BatchLock ";
        $sql .= " , :UpdateDate ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':BatchId' => isset($data['BatchId']) ? $data['BatchId'] : 0,
                ':ThreadNo' => isset($data['ThreadNo']) ? $data['ThreadNo'] : 0,
                ':BatchName' =>  $data['BatchName'],
                ':BatchLock' => isset($data['BatchLock']) ? $data['BatchLock'] : 0,
                ':UpdateDate' => date('Y-m-d H:i:s'),
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
    }

    /**
     * 指定されたレコードを更新する。
     *
     * @param array $data 更新内容
     * @param int $seq SEQ
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

        $sql  = " UPDATE T_BatchLock ";
        $sql .= " SET ";
        $sql .= "     BatchId = :BatchId ";
        $sql .= " ,   ThreadNo = :ThreadNo ";
        $sql .= " ,   BatchName = :BatchName ";
        $sql .= " ,   BatchLock = :BatchLock ";
        $sql .= "     WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
                ':BatchId' =>  $row['BatchId'],
                ':ThreadNo' =>  $row['ThreadNo'],
                ':BatchName' => $row['BatchName'],
                ':BatchLock' => $row['BatchLock'],
        );

        return $stm->execute($prm);
    }

    /**
     * バッチ排他制御のためのロックを取得する。
     * @param int $BatchId バッチID
     * @param int $ThreadNo スレッドNo
     * @return int ロックを取得できれば1以上の実数。　それ以外は0
     */
    public function getLock($BatchId = 1, $ThreadNo = 1)
    {
        $lockId = date('YmdHis');

        $sql = " UPDATE T_BatchLock SET BatchLock = :BatchLock, UpdateDate = :UpdateDate WHERE  BatchId = :BatchId AND ThreadNo = :ThreadNo AND BatchLock = 0 ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':BatchLock' => $lockId,
                ':BatchId' => $BatchId,
                ':ThreadNo' => $ThreadNo,
                ':UpdateDate' => date('Y-m-d H:i:s'),
        );

        $ri = $stm->execute($prm);

        return ($ri->getAffectedRows() > 0) ? $lockId : 0;
    }

    /**
     * ロックをリリースする。
     * @param int $BatchId バッチID
     * @param int $ThreadNo スレッドNo
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function releaseLock($BatchId = 1, $ThreadNo = 1)
    {
        $sql = " UPDATE T_BatchLock SET BatchLock = 0, UpdateDate = :UpdateDate WHERE BatchId = :BatchId AND ThreadNo = :ThreadNo AND BatchLock > 0 ";
        return $this->_adapter->query($sql)->execute(array(':BatchId' => $BatchId,':ThreadNo' => $ThreadNo,':UpdateDate' => date('Y-m-d H:i:s')));
    }

    /**
     * バッチ排他制御情報データを取得する
     *
     * @param int $BatchId バッチID
     * @param int $ThreadNo スレッドNo
     * @return ResultInterface
     */
    public function findBatchId($BatchId = 1, $ThreadNo = 1)
    {
        $sql  = " SELECT * FROM T_BatchLock WHERE BatchId = :BatchId AND ThreadNo = :ThreadNo ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':BatchId' => $BatchId,
                ':ThreadNo' => $ThreadNo,
        );

        return $stm->execute($prm)->current();
    }
}