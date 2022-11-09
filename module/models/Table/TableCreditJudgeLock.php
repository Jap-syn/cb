<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_CreditJudgeLock(社内与信プロセス排他制御)テーブルへのアダプタ
 */
class TableCreditJudgeLock
{
    protected $_name = 'T_CreditJudgeLock';
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
     * 不正アクセス情報データを取得する
     *
     * @param int $seq SEQ
     * @return ResultInterface
     */
    public function find($seq)
    {
        $sql  = " SELECT * FROM T_CreditJudgeLock WHERE Seq = :Seq ";

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
        $sql  = " INSERT INTO T_CreditJudgeLock (CreditThreadNo, CreditThreadName, CreditJudgeLock, UpdateDate) VALUES (";
        $sql .= "   :CreditThreadNo ";
        $sql .= " , :CreditThreadName ";
        $sql .= " , :CreditJudgeLock ";
        $sql .= " , :UpdateDate ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CreditThreadNo' => isset($data['CreditThreadNo']) ? $data['CreditThreadNo'] : 0,
                ':CreditThreadName' => $data['CreditThreadName'],
                ':CreditJudgeLock' => isset($data['CreditJudgeLock']) ? $data['CreditJudgeLock'] : 0,
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

        $sql  = " UPDATE T_CreditJudgeLock ";
        $sql .= " SET ";
        $sql .= "     CreditThreadNo = :CreditThreadNo ";
        $sql .= " ,   CreditThreadName = :CreditThreadName ";
        $sql .= " ,   CreditJudgeLock = :CreditJudgeLock ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
                ':CreditThreadNo' => $row['CreditThreadNo'],
                ':CreditThreadName' => $row['CreditThreadName'],
                ':CreditJudgeLock' => $row['CreditJudgeLock'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
        );

        return $stm->execute($prm);
    }

    /**
     * 社内与信プロセス排他制御リスト取得(ドロップダウン用)
     *
     * @return array 社内与信プロセス排他制御リスト
     */
    public function getThreadNoList()
    {
        $retval = array();

        $ri = $this->_adapter->query(" SELECT CreditThreadNo, CreditThreadName FROM T_CreditJudgeLock ")->execute(null);
        foreach ($ri as $row) {
            $retval[$row['CreditThreadNo']] = $row['CreditThreadName'];
        }

        return $retval;
    }

    /**
     * 社内与信処理の排他制御のためのロックを取得する。
     * @param int $creditThreadNo 与信スレッドNo
     * @return int ロックを取得できれば1以上の実数。　それ以外は0
     */
    public function getLock($creditThreadNo)
    {
        $lockId = date('YmdHis');

        $sql = " UPDATE T_CreditJudgeLock SET CreditJudgeLock = :CreditJudgeLock, UpdateDate = :UpdateDate WHERE CreditThreadNo = :CreditThreadNo AND CreditJudgeLock = 0 ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CreditJudgeLock' => $lockId,
                ':CreditThreadNo' => $creditThreadNo,
                ':UpdateDate' => date('Y-m-d H:i:s'),
        );

        $ri = $stm->execute($prm);

        return ($ri->getAffectedRows() > 0) ? $lockId : 0;
    }

    /**
     * ロックをリリースする。
     * @param int $creditThreadNo 与信スレッドNo
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function releaseLock($creditThreadNo)
    {
        $sql = " UPDATE T_CreditJudgeLock SET CreditJudgeLock = 0, UpdateDate = :UpdateDate WHERE CreditThreadNo = :CreditThreadNo AND CreditJudgeLock > 0 ";
        return $this->_adapter->query($sql)->execute(array(':CreditThreadNo' => $creditThreadNo,':UpdateDate' => date('Y-m-d H:i:s')));
    }

	/**
	 * 自動与信実行中か否かを取得する
	 *
	 * @return string 実行中のスレッドNoリスト。空文字:実行中でない、空文字以外：実行中のスレッドあり
	 */
	public function getProcessing()
	{
        $sql = " SELECT GROUP_CONCAT(CONCAT('No', CreditThreadNo + 1) SEPARATOR '/') AS CreditThreadNo FROM T_CreditJudgeLock WHERE CreditJudgeLock > 0 ";
	    $cur = $this->_adapter->query($sql)->execute(null)->current();
	    return ($cur) ? $cur['CreditThreadNo'] : '';
	}

}
