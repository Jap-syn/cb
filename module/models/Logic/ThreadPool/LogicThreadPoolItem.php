<?php
namespace models\Logic\ThreadPool;

use Zend\Db\Adapter\Adapter;
use models\Table\TableThreadPool;
use models\Logic\LogicThreadPool;

/**
 * スレッドプールロジック用のスレッドアイテム。
 * T_ThreadPoolの行に対するビューとなり、
 * スレッド完了状態をDBへ反映させる役割も持つ
 */
class LogicThreadPoolItem {
    /**
     * 親のThreadPool
     *
     * @access protected
     * @var LogicThreadPool
     */
    protected $_pool;

    /**
     * スレッドID
     *
     * @access protected
     * @var int
     */
    protected $_threadId;

    /**
     * DB上の行オブジェクト(ResultInterface->current)
     *
     * @access protected
     * @var array
     */
    protected $_row;

    /**
     * スレッドIDと管理元のLogicThreadPoolを指定して
     * LogicThreadPoolItemの新しいインスタンスを初期化する
     *
     * @param int $threadId 同期する行を示すスレッドID
     * @param LogicThreadPool このインスタンスの親となるLogicThreadPool
     */
    public function __construct($threadId, LogicThreadPool $pool) {
        $this->_pool = $pool;
        $this->_threadId = $threadId;
        $this->sync();
    }

    /**
     * インスタンスが破棄される
     */
    public function __destruct() {
        try {
            // もしこのインスタンスが終了していなかったら異常終了への更新を試みる
            if($this->isAlive()) {
                $this->abend('destructor called before terminate.');
            }
        }
        catch(\Exception $err) {
        }
    }

    /**
     * このインスタンスの親であるスレッドプールを取得する
     *
     * @return LogicThreadPool
     */
    public function getParentPool() {
        return $this->_pool;
    }

    /**
     * スレッドプールテーブルを取得する
     *
     * @return TableThreadPool
     */
    public function getTable() {
        return $this->getParentPool()->getTable();
    }

    /**
     * DBアダプタを取得する
     *
     * @return Adapter
     */
    public function getAdapter() {
        return $this->getParentPool()->getAdapter();
    }

    /**
     * データベースの状態にインスタンスの内容を同期する
     *
     * @return LogicThreadPoolItem このインスタンス
     */
    public function sync() {
        // 行の取得を試みる
        $threadId = $this->_threadId;
        $ri = $this->getTable()->find($threadId);

        // 対応する行が見つからない
        if (!($ri->count() > 0)) {
            throw new LogicThreadPoolException(sprintf('thread row not found. ThreadId = %s', $threadId));
        }
        $this->_row = $ri->current();

        // スレッドグループが親と異なる
        if($this->_row['ThreadGroup'] != $this->getParentPool()->getGroupName()) {
            throw new LogicThreadPoolException('invalid ThreadGroup specified');
        }

        return $this;
    }

    /**
     * スレッドIDを取得する
     *
     * @return int
     */
    public function getThreadId() {
        return $this->_row['ThreadId'];
    }
    /**
     * スレッドグループ名を取得する
     *
     * @return string
     */
    public function getGroupName() {
        return $this->_row['ThreadGroup'];
    }
    /**
     * スレッド作成日を取得する
     *
     * @return string
     */
    public function getCreateDate() {
        return $this->_row['CreateDate'];
    }
    /**
     * 最終アクセス日時を取得する
     *
     * @return string
     */
    public function getLastAccessDate() {
        return $this->_row['LastAccessDate'];
    }
    /**
     * ステータスを取得する
     *
     * @return int
     */
    public function getStatus() {
        return $this->_row['Status'];
    }
    /**
     * ユーザデータを取得する
     *
     * @return string
     */
    public function getUserData() {
        return $this->_row['UserData'];
    }
    /**
     * 異常終了理由を取得する
     *
     * @return string
     */
    public function getTerminateReason() {
        return $this->_row['TerminateReason'];
    }

    /**
     * このスレッドがアクティブ（＝実行待ちまたは実行中）であるかを判断する。
     *
     * @return boolean
     */
    public function isAlive() {
        return $this->isRunning() || $this->isStandBy();
    }
    /**
     * このスレッドが完了済みであるかを判断する
     *
     * @return boolean
     */
    public function isTerminated() {
        return !$this->isAlive();
    }
    /**
     * このスレッドが異常終了スレッドであるかを判断する
     * @return boolean
     */
    public function isAbendded() {
        return $this->getStatus() == TableThreadPool::STATUS_TERMINATED_ABNORMALLY;
    }
    /**
     * このスレッドが現在実行中であるかを判断する
     * @return boolean
     */
    public function isRunning() {
        return $this->getStatus() == TableThreadPool::STATUS_RUNNING;
    }
    /**
     * このスレッドが実行開始待ちであるかを判断する
     * @return boolean
     */
    public function isStandBy() {
        return $this->getStatus() == TableThreadPool::STATUS_STAND_BY;
    }

    /**
     * アクティブスレッドのアクセス日時を更新する。
     * すでに終了しているスレッドでこのメソッドを実行した場合は例外がスローされる
     *
     * @return boolean 実行中かを示すbool値
     */
    public function processing() {
        $this->_checkAlive();

        // UPDATE
        $sql  = " UPDATE T_ThreadPool ";
        $sql .= " SET ";
        $sql .= "     LastAccessDate  = :LastAccessDate ";
        $sql .= " WHERE ThreadId      = :ThreadId ";

        $this->getAdapter()->query($sql)->execute(array(':LastAccessDate' => date('Y-m-d H:i:s'), ':ThreadId' => $this->getThreadId()));

        $this->sync();
        return $this->isRunning();
    }

    /**
     * スレッドを正常終了させる。
     * すでに終了しているスレッドでこのメソッドを実行した場合は例外がスローされる
     */
    public function terminate() {
        $this->_checkAlive();

        // UPDATE
        $sql  = " UPDATE T_ThreadPool ";
        $sql .= " SET ";
        $sql .= "     LastAccessDate  = :LastAccessDate ";
        $sql .= " ,   Status          = :Status ";
        $sql .= " WHERE ThreadId      = :ThreadId ";

        $this->getAdapter()->query($sql)->execute(
            array(':LastAccessDate' => date('Y-m-d H:i:s'), ':Status' => TableThreadPool::STATUS_TERMINATED_NORMALLY, ':ThreadId' => $this->getThreadId()));

        $this->sync();
    }

    /**
     * 終了理由を指定して、スレッドを異常終了させる。
     * すでに終了しているスレッドでこのメソッドを実行した場合は例外がスローされる
     */
    public function abend($reason) {
        $this->_checkAlive();

        // UPDATE
        $sql  = " UPDATE T_ThreadPool ";
        $sql .= " SET ";
        $sql .= "     LastAccessDate  = :LastAccessDate ";
        $sql .= " ,   Status          = :Status ";
        $sql .= " ,   TerminateReason = :TerminateReason ";
        $sql .= " WHERE ThreadId      = :ThreadId ";

        $this->getAdapter()->query($sql)->execute(
            array(':LastAccessDate' => date('Y-m-d H:i:s'), ':Status' => TableThreadPool::STATUS_TERMINATED_NORMALLY, ':TerminateReason' => $reason, ':ThreadId' => $this->getThreadId()));

        $this->sync();
    }

    /**
     * このスレッドがアクティブであるかをチェックする内部チェックメソッド。
     * 終了済みの場合は例外をスローする
     *
     * @access protected
     */
    protected function _checkAlive() {
        if(!$this->isAlive()) {
            throw new LogicThreadPoolException('this item already terminated.');
        }
    }

    /**
     * スレッド状態を更新するためにデータを絞り込むwhere条件を取得する
     *
     * @access protected
     * @return string
     */
    protected function _getWhereForSync() {
        return " ThreadId = " . $this->getThreadId();
    }
}
