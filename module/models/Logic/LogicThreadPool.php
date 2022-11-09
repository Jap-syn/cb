<?php
namespace models\Logic;

use Zend\Db\Adapter\Adapter;
use Zend\Log\Logger;
use Coral\Base\BaseLog;
use models\Table\TableThreadPool;
use models\Logic\ThreadPool\LogicThreadPoolItem;
use models\Logic\ThreadPool\LogicThreadPoolException;

/**
 * T_ThreadPoolテーブルを介して特定処理の複数プロセス/スレッドにおける
 * 同時実行数を管理するためのスレッドプールロジック。
 * スレッドグループ名に関連付けたインスタンスを取得し、ロックアイテムを獲得することで
 * 実行制限を実現する
 */
class LogicThreadPool {
    // デフォルトプロパティ定数：スレッド制限数（-1：無制限）
    const DEFAULT_THREAD_LIMIT = -1;

    // デフォルトプロパティ定数：ロック待ちタイムアウト（60秒）
    const DEFAULT_LOCKWAIT_TIMEOUT = 60;

    // デフォルトプロパティ定数：ロック再獲得試行間隔（0.3秒）
    const DEFAULT_LOCK_RETRY_INTERVAL = 0.3;

    // ロック待ちタイムアウトに設定可能な下限値（5秒）
    const LOCKWAIT_TIMEOUT_MIN = 5;
    // ロック待ちタイムアウトに設定可能な上限値（180秒）
    const LOCKWAIT_TIMEOUT_MAX = 180;

    // ロック再獲得間隔に設定可能な下限値（0.1秒）
    const LOCK_RETRY_INTERVAL_MIN = 0.1;
    // ロック再獲得間隔に設定可能な上限値（5秒）
    const LOCK_RETRY_INTERVAL_MAX = 5.0;

    // 初期化オプションキー定数：DBアダプタを指定するキー
    const OPTION_DB_ADAPTER = 'db adapter';

    // 初期化オプションキー定数：スレッド制限数を指定するキー
    const OPTION_THREAD_LIMIT = 'thread limit';

    // 初期化オプションキー定数：ロック待ちタイムアウトを指定するキー
    const OPTION_LOCKWAIT_TIMEOUT = 'lockwait timeout';

    // 初期化オプションキー定数：ロック再獲得試行間隔を指定するキー
    const OPTION_LOCK_RETRY_INTERVAL = 'lock retry interval';

    /**
     * インスタンスキャッシュ
     *
     * @static
     * @access protected
     * @var array
     */
    protected static $__instances = array();

    /**
     * デフォルトで使用するDBアダプタ
     *
     * @static
     * @access protected
     * @var Adapter
     */
    protected static $__adapter;

    /**
     * 指定スレッドグループを扱うLogicThreadPoolのインスタンスを取得する。
     *
     * @static
     * @param string $groupName スレッドグループ名
     * @param array $options 初期化オプション配列
     * @return LogicThreadPool
     */
    public static function getPool($groupName, array $options = array()) {
        if(isset(self::$__instances[$groupName])) {
            // 指定グループのインスタンスがすでに存在する場合はオプションを上書きして返す
            /** @var LogicThreadPool */
            $instance = self::$__instances[$groupName];
            $instance->_setOptions($options);
        } else {
            // 新規インスタンス生成
            // → インスタンスキャッシュへの登録はコンストラクタから行われる
            $instance = new self($groupName, $options);
        }
        return $instance;
    }

    /**
     * JNB口座オープン専用のスレッドプールインスタンスを取得する
     *
     * @static
     * @param int $jnbId 対象JNB契約ID
     * @param null | Adapter $adapter アダプタ
     * @return LogicThreadPool
     */
    public static function getPoolForJnbAccountOpen($jnbId, Adapter $adapter = null) {
        $groupName = sprintf('lock-for-jnb-account-open-%s', $jnbId);
        $options = array(
                self::OPTION_LOCKWAIT_TIMEOUT => self::DEFAULT_LOCKWAIT_TIMEOUT,
                self::OPTION_LOCK_RETRY_INTERVAL => self::DEFAULT_LOCK_RETRY_INTERVAL,
                self::OPTION_THREAD_LIMIT => 1
        );

        if ($adapter != null) {
            $options[self::OPTION_DB_ADAPTER] = $adapter;
        }

        return self::getPool($groupName, $options);
    }

    /**
     * SMBCバーチャル口座オープン専用のスレッドプールインスタンスを取得する
     *
     * @static
     * @param int $smbcpaId 対象SMBCバーチャル口座契約ID
     * @param null | Adapter $adapter アダプタ
     * @return LogicThreadPool
     */
    public static function getPoolForSmbcpaAccountOpen($smbcpaId, Adapter $adapter = null) {
        $groupName = sprintf('lock-for-smbcpa-account-open-%s', $smbcpaId);
        $options = array(
                self::OPTION_LOCKWAIT_TIMEOUT => self::DEFAULT_LOCKWAIT_TIMEOUT,
                self::OPTION_LOCK_RETRY_INTERVAL => self::DEFAULT_LOCK_RETRY_INTERVAL,
                self::OPTION_THREAD_LIMIT => 1
        );

        if ($adapter != null) {
            $options[self::OPTION_DB_ADAPTER] = $adapter;
        }

        return self::getPool($groupName, $options);
    }

    /**
     * 指定のLogicThreadPoolインスタンスをキャッシュに登録する
     *
     * @static
     * @access protected
     * @param LogicThreadPool $instance 登録するインスタンス
     */
    protected static function __addInstance(LogicThreadPool $instance) {
        if(!is_array(self::$__instances)) self::$__instances = array();
        $key = $instance->getGroupName();
        self::$__instances[$key] = $instance;
    }

    /**
     * デフォルトのDBアダプタを取得する
     *
     * @static
     * @return Adapter
     */
    public static function getDefaultAdapter() {
        return self::$__adapter;
    }
    /**
     * デフォルトのDBアダプタを設定する
     *
     * @static
     * @param Adapter $adapter
     */
    public static function setDefaultAdapter(Adapter $adapter) {
        self::$__adapter = $adapter;
    }


    /**
     * スレッドグループ名
     *
     * @access protected
     * @var string
     */
    protected $_groupName;

    /**
     * アダプタ
     *
     * @access protected
     * @var Adapter
     */
    protected $_adapter = null;

    /**
     * スレッドプールテーブル
     *
     * @access protected
     * @var TableThreadPool
     */
    protected $_table = null;

    /**
     * スレッド制限数
     *
     * @access protected
     * @var int
     */
    protected $_threadLimit = self::DEFAULT_THREAD_LIMIT;

    /**
     * ロック獲得待ちタイムアウト（秒）
     *
     * @access protected
     * @var int
     */
    protected $_lockWaitTimeout = self::DEFAULT_LOCKWAIT_TIMEOUT;

    /**
     * ロック再獲得試行間隔（秒）
     *
     * @access protected
     * @var float
     */
    protected $_lockRetryInterval = self::DEFAULT_LOCK_RETRY_INTERVAL;

    /**
     * @access protected
     * @var array
     */
    protected $_items = array();

    /**
     * ロガーインスタンス
     *
     * @access protected
     * @var BaseLog
     */
    protected $_logger;

    /**
     * スレッドグループ名と初期化オプションを指定して
     * LogicThreadPoolの新しいインスタンスを初期化する
     *
     * @access protected
     * @param string $groupName スレッドグループ名
     * @param array $options 初期化オプション配列
     */
    protected function __construct($groupName, array $options) {
        // オプションの初期設定を反映
        if(!is_array($options)) $options = array();
        $options = array_merge(array(
            self::OPTION_DB_ADAPTER => self::getDefaultAdapter(),
            self::OPTION_THREAD_LIMIT => self::DEFAULT_THREAD_LIMIT,
            self::OPTION_LOCKWAIT_TIMEOUT => self::DEFAULT_LOCKWAIT_TIMEOUT,
            self::OPTION_LOCK_RETRY_INTERVAL => self::DEFAULT_LOCK_RETRY_INTERVAL
        ), $options);

        // グループ名の適切性を検証
        TableThreadPool::isValidGroupName($groupName);
        $this->_groupName = $groupName;

        // インスタンスキャッシュへ登録
        self::__addInstance($this);

        // オプション適用
        $this->_setOptions($options);
    }

    /**
     * このインスタンスが関連付けられたスレッドグループ名を取得する
     *
     * @return string
     */
    public function getGroupName() {
        return $this->_groupName;
    }

    /**
     * 初期化オプション配列で各種オプションを設定する。
     *
     * @access protected
     * @param array $options 初期化オプション配列。
     *                       キーはこのクラスで定義されるOPTION_～定数を使用する
     */
    protected function _setOptions(array $options) {
        if(!is_array($options)) $options = array();
        foreach($options as $key => $val) {
            switch($key) {
                case self::OPTION_DB_ADAPTER:
                    // DBアダプタ設定
                    $this->setAdapter($val);
                    break;
                case self::OPTION_THREAD_LIMIT:
                    // スレッド上限設定
                    $this->setThreadLimit($val);
                    break;
                case self::OPTION_LOCKWAIT_TIMEOUT:
                    // ロック獲得待ちタイムアウト設定
                    $this->setLockWaitTimeout($val);
                    break;
                case self::OPTION_LOCK_RETRY_INTERVAL:
                    // ロック再獲得間隔設定
                    $this->setLockRetryInterval($val);
                    break;
            }
        }
    }

    /**
     * このインスタンスが使用するDBアダプタを取得する
     *
     * @return Adapter
     */
    public function getAdapter() {
        return $this->_adapter;
    }
    /**
     * このインスタンスが使用するDBアダプタを設定する
     *
     * @param Adapter アダプタ
     * @return LogicThreadPool このインスタンス
     */
    public function setAdapter(Adapter $adapter) {
        $this->_adapter = $adapter;

        // テーブルインスタンスは廃棄しておく
        $this->_table = null;

        return $this;
    }

    /**
     * スレッド制限数を取得する
     *
     * @return int スレッド制限数。-1は無制限を表す
     */
    public function getThreadLimit() {
        return $this->_threadLimit;
    }
    /**
     * スレッド制限数を設定する。
     * 0またはそれより小さい値を指定した場合は-1と見なされ、無制限となる
     *
     * @param int $limit スレッド制限数。
     * @return LogicThreadPool このインスタンス
     */
    public function setThreadLimit($limit) {
        $limit = (int)$limit;
        if($limit <= 0) $limit = -1;
        $this->_threadLimit = $limit;
        return $this;
    }

    /**
     * ロック獲得時のタイムアウト値を取得する
     *
     * @return int タイムアウト値を示す秒数
     */
    public function getLockWaitTimeout() {
        return $this->_lockWaitTimeout;
    }
    /**
     * ロック獲得時のタイムアウト値を設定する。
     * 指定可能範囲はLOCKWAIT_TIMEOUT_MIN定数およびLOCKWAIT_TIMEOUT_MAX定数で
     * 定義され、これらの範囲を超える場合は範囲内になるよう丸められる
     *
     * @param int $limit タイムアウトを秒で指定
     * @return LogicThreadPool このインスタンス
     */
    public function setLockWaitTimeout($timeOut) {
        $timeOut = (int)$timeOut;
        if($timeOut < self::LOCKWAIT_TIMEOUT_MIN) {
            $timeOut = self::LOCKWAIT_TIMEOUT_MIN;
        }
        if($timeOut > self::LOCKWAIT_TIMEOUT_MAX) {
            $timeOut = self::LOCKWAIT_TIMEOUT_MAX;
        }
        $this->_lockWaitTimeout = $timeOut;
        return $this;
    }

    /**
     * ロック再獲得を試行するまでのインターバル間隔を取得する
     *
     * @return float インターバルを示す秒数
     */
    public function getLockRetryInterval() {
        return $this->_lockRetryInterval;
    }
    /**
     * ロック再獲得を試行するまでのインターバル間隔を秒単位で設定する。
     * 指定可能範囲はLOCK_RETRY_INTERVAL_MIN定数およびLOCK_RETRY_INTERVAL_MAX定数で
     * 定義され、これらの範囲を超える場合は範囲内になるよう丸められる
     *
     * @param float $interval インターバルを秒で指定する
     * @return LogicThreadPool このインスタンス
     */
    public function setLockRetryInterval($interval) {
        $interval = (float)$interval;
        if($interval < self::LOCK_RETRY_INTERVAL_MIN) {
            $interval = self::LOCK_RETRY_INTERVAL_MIN;
        }
        if($interval > self::LOCK_RETRY_INTERVAL_MAX) {
            $interval = self::LOCK_RETRY_INTERVAL_MAX;
        }
        $this->_lockRetryInterval = $interval;
        return $this;
    }

    /**
     * スレッドプールテーブルを取得する
     *
     * @return TableThreadPool
     */
    public function getTable() {
        if(!$this->_table) {
            $this->_table = new TableThreadPool($this->_adapter);
        }
        return $this->_table;
    }

    /**
     * このインスタンスで使用するロガーを取得する
     *
     * @return BaseLog
     */
    public function getLogger() {
        return $this->_logger;
    }
    /**
     * このインスタンスで使用するロガーを設定する
     *
     * @param BaseLog $logger
     * @return LogicThreadPool
     */
    public function setLogger(BaseLog $logger = null) {
        $this->_logger = $logger;
        return $this;
    }

    /**
     * ロックアイテムを開く
     *
     * @param string $userData ロックアイテムに関連付ける任意データ
     * @return LogicThreadPoolItem ロックアイテム
     */
    public function open($userData) {
        /** @var TableThreadPool */
        $table = $this->getTable();
        $grp = $this->getGroupName();
        $limit = $this->getThreadLimit();

        // INSERT
        $threadId = $table->createNewItem($grp, $userData);
        $row = $table->find($threadId)->current();
        $item = new LogicThreadPoolItem($row['ThreadId'], $this);

        $start = microtime(true);

        while(true) {
            $runningCount = $table->fetchRunngingItems($grp)->count();
            if($limit < 0 || $runningCount < $limit) {
                // 実行可能スレッド数未満なら次回実行対象のスレッドであるかをチェック
                if($table->findNextStartId($grp) == $row['ThreadId']) {
                    // いまのスレッドが次回実行対象なら実行開始（＝ロック獲得）

                    // UPDATE
                    $sql  = " UPDATE T_ThreadPool ";
                    $sql .= " SET ";
                    $sql .= "     Status          = :Status ";
                    $sql .= " WHERE ThreadId      = :ThreadId ";

                    $this->_adapter->query($sql)->execute(array(':Status' => TableThreadPool::STATUS_RUNNING, ':ThreadId' => $threadId));

                    $item->sync();
                    return $item;
                }
            }
            if(microtime(true) - $start > $this->getLockWaitTimeout()) {
                // ロック獲得待ちタイムアウト
                break;
            }
            // 再試行までウェイトを入れる
            usleep($this->getLockRetryInterval() * 1000000);
        }

        // タイムアウトになったので異常終了させて例外をスロー
        $item->abend('lock wait timed out');
        throw new LogicThreadPoolException('lock wait timed out');
    }

    /**
     * ロックアイテムを開く(API専用)
     *
     * @param string $userData ロックアイテムに関連付ける任意データ
     * @return LogicThreadPoolItem ロックアイテム
     */
    public function openApi($userData) {
        /** @var TableThreadPool */
        $table = $this->getTable();
        $grp = $this->getGroupName();
        $limit = $this->getThreadLimit();

        // INSERT
        $threadId = $table->createNewItemApi($grp, $userData);
        $row = $table->find($threadId)->current();
        $item = new LogicThreadPoolItem($row['ThreadId'], $this);

        return $item;

    }


    /**
     * 現在のスレッドグループ内で唯一の実行中アイテムとしてロックアイテムを開く。
     * 同一スレッドグループで先行して実行中のアイテムがあった場合、指定の回数のリトライを試み、
     * それでも獲得できない場合は例外をスローする。
     * ただしこのスレッドプールのスレッド数上限が1以外に設定されている場合は通常のopenと同じ動作を行うため
     * 同時実行数の制約は通常のopen同様のルーズさを保持する
     *
     * @param string $userData ロックアイテムに関連付ける任意データ
     * @param null | int $wait スレッド衝突時のリトライ間隔（ミリ秒）。省略時は250
     * @param null | int $retry スレッド衝突時のリトライ上限数。省略時は3
     * @return LogicThreadPoolItem ロックアイテム
     */
    public function openAsSingleton($userData, $wait = 250, $retry = 3) {
        // ウェイトパラメータの調整
        $wait = (int)$wait;
        if($wait < 50) $wait = 50;
        if($wait > 3000) $wait = 3000;

        // リトライパラメータの調整
        $retry = (int)$retry;
        if($retry < 1) $retry = 1;
        $count = 0;
        while(true) {
            $item = $this->open($userData);

            // スレッド数上限が1でなければそのまま結果を返す
            if($this->getThreadLimit() != 1) return $item;
            foreach($this->getTable()->fetchRunningItems($this->getGroupName()) as $itemRow) {
                if($itemRow['ThreadId'] == $item->getThreadId()) {
                    // 獲得したロックのスレッドIDがグループ内の最初のスレッドなので獲得成功
                    return $item;
                }
                break;  // リストの先頭のみチェックすればいいのでここでbreak
            }
            // 先頭スレッドとしてロックを獲得できなかったのでabend
            $item->abend('thread conflicted');
            if(++$count >= $retry) {
                $message = 'cannot open as singleton thread';
                $this->warn(sprintf('%s [userData = %s]', $message, $userData));
                throw new LogicThreadPoolException($message);
            }
            $this->notice(sprintf('thread conflicted. now %d times tried (limit = %d). [userData = %s]', $count, $retry, $userData));
            // ウェイトを入れる
            usleep($wait * 1000);
        }
    }

    /**
     * 指定スレッドIDの実行待ち・実行中アイテムを取得する。
     * 指定スレッドが見つからないまたは実行待ち・実行中でない場合、このメソッドはnullを返す
     *
     * @param int $threadId スレッドID
     * @return LogicThreadPoolItem | null 実行中ロックアイテム
     */
    public function getRunningItemByThreadId($threadId) {

        $table = $this->getTable();
        $grp = $this->getGroupName();

        foreach($table->fetchRunngingItems($grp) as $row) {
            if($row['ThreadId'] == $threadId) {
                return new LogicThreadPoolItem($row['ThreadId'], $this);
            }
        }
        foreach($table->fetchStandByItems($grp) as $row) {
            if($row['ThreadId'] == $threadId) {
                return new LogicThreadPoolItem($row['ThreadId'], $this);
            }
        }
        return null;
    }

    /**
     * 指定ユーザデータを持つ実行待ち・実行中アイテムをすべて取得する。
     *
     * @param string $userData アイテムをopenした際に使用したユーザデータ
     * @return array
     */
    public function getRunningItemsByUserData($userData) {
        return $this->_getItemsByUserData($userData, true);
    }

    /**
     * 指定ユーザデータを持つアイテムをすべて取得する。
     *
     * @param string $userData アイテムをopenした際に使用したユーザデータ
     * @return array
     */
    public function getItemsByUserData($userData) {
        return $this->_getItemsByUserData($userData, false);
    }

    /**
     * ユーザデータ検索によるアイテム取得の内部メソッド
     *
     * @param string $userData アイテムをopenした際に使用したユーザデータ
     * @param boolean $excludeTerminated 完了済みアイテムを除外するかのフラグ
     * @return array
     */
    protected function _getItemsByUserData($userData, $excludeTerminated) {

        $table = $this->getTable();
        $grp = $this->getGroupName();

        $results = array();
        foreach($table->fetchItemsByUserData($grp, $userData, $excludeTerminated) as $row) {
            $results[] = new LogicThreadPoolItem($row['ThreadId'], $this);
        }
        return $results;
    }

    /**
     * 指定の優先度でログメッセージを出力する
     *
     * @param string $message ログメッセージ
     * @param int $priority 優先度
     */
    public function log($message, $priority) {
        $logger = $this->getLogger();
        $message = sprintf('[%s:%s] %s', get_class($this), $this->getGroupName(), $message);
        if($logger) {
            $logger->log($priority, $message);
        }
    }

    /**
     * DEBUGレベルでログメッセージを出力する
     *
     * @param string $message ログメッセージ
     */
    public function debug($message) {
        $this->log($message, Logger::DEBUG);
    }

    /**
     * INFOレベルでログメッセージを出力する
     *
     * @param string $message ログメッセージ
     */
    public function info($message) {
        $this->log($message, Logger::INFO);
    }

    /**
     * NOTICEレベルでログメッセージを出力する
     *
     * @param string $message ログメッセージ
     */
    public function notice($message) {
        $this->log($message, Logger::NOTICE);
    }

    /**
     * WARNレベルでログメッセージを出力する
     *
     * @param string $message ログメッセージ
     */
    public function warn($message) {
        $this->log($message, Logger::WARN);
    }

    /**
     * ERRレベルでログメッセージを出力する
     *
     * @param string $message ログメッセージ
     */
    public function err($message) {
        $this->log($message, Logger::ERR);
    }

    /**
     * CRITレベルでログメッセージを出力する
     *
     * @param string $message ログメッセージ
     */
    public function crit($message) {
        $this->log($message, Logger::CRIT);
    }

    /**
     * ALERTレベルでログメッセージを出力する
     *
     * @param string $message ログメッセージ
     */
    public function alert($message) {
        $this->log($message, Logger::ALERT);
    }

    /**
     * EMERGレベルでログメッセージを出力する
     *
     * @param string $message ログメッセージ
     */
    public function emerg($message) {
        $this->log($message, Logger::EMERG);
    }
}

