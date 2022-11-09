<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;

/**
 * T_SystemStatusテーブルへのアダプタ
 */
class TableSystemStatus
{
	protected $_name = 'T_SystemStatus';
	protected $_primary = array('CreditJudgeLock');		// 暫定対応
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
	 * 社内与信処理の排他制御のためのロックを取得する。
	 *
	 * @return int ロックを取得できれば1以上の実数。　それ以外は0
	 */
	public function getLock()
	{
        $lockId = date('YmdHis');

        $sql = " UPDATE T_SystemStatus SET CreditJudgeLock = :CreditJudgeLock WHERE CreditJudgeLock = 0 ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CreditJudgeLock' => $lockId,
        );

        $ri = $stm->execute($prm);

        return ($ri->getAffectedRows() > 0) ? $lockId : 0;
	}

	/**
	 * ロックをリリースする。
	 */
	public function releaseLock()
	{
	    $sql = " UPDATE T_SystemStatus SET CreditJudgeLock = 0 WHERE CreditJudgeLock > 0 ";
        return $this->_adapter->query($sql)->execute(null);
	}

	/**
	 * 与信結果メール送信プロセスの排他制御のためのロックを取得する。
	 *
	 * @return int ロックを取得できれば1以上の実数。　それ以外は0
	 */
	public function getCjMailLock()
	{
        $lockId = date('YmdHis');

        $sql = "UPDATE T_SystemStatus SET CjMailLock = :CjMailLock WHERE CjMailLock = 0 ";
        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CjMailLock' => $lockId,
        );
        $ri = $stm->execute($prm);

        return ($ri->getAffectedRows() > 0) ? $lockId : 0;
	}

	/**
	 * 与信結果メール送信プロセスロックをリリースする。
	 */
	public function releaseCjMailLock()
	{
	    $sql = " UPDATE T_SystemStatus SET CjMailLock = 0 WHERE CjMailLock > 0 ";
        return $this->_adapter->query($sql)->execute(null);
	}

	/**
	 * 自動与信実行中か否かを取得する
	 *
	 * @return boolearn true:実行中である false:実行中ではない
	 */
	public function isProcessing()
	{
	    $sql = " SELECT COUNT(*) AS CNT FROM T_SystemStatus WHERE CreditJudgeLock > 0 ";
        return ((int)$this->_adapter->query($sql)->execute(null)->current()['CNT'] > 0) ? true : false;
	}

	/**
	 * 与信サービス状態結果XMLを取得する
	 *
	 * @return string|null サービスがNG|応答なしの場合null
	 */
	public function getCjServiceStatus()
	{
	    $sql = " SELECT CjServiceStatus FROM T_SystemStatus ";
	    return $this->_adapter->query($sql)->execute(null)->current();
	}

	/**
	 * 与信サービス状態結果XMLを設定する
	 *
	 * @return string|null サービスがNG|応答なしの場合null
	 */
	public function setCjServiceStatus($data)
	{
	    $sql = " UPDATE T_SystemStatus SET CjServiceStatus = :CjServiceStatus ";
	    $prm = array(
	        ':CjServiceStatus' => $data
	    );
	    $this->_adapter->query($sql)->execute($prm);
	}
}
