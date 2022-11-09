<?php
chdir(dirname(__DIR__));

// Setup autoloading
require 'init_autoloader.php';

use Coral\Base\Application\BaseApplicationAbstract;
use models\Logic\Jnb\Account\LogicJnbAccountReceipt;
use Zend\Db\Adapter\Adapter;
use Coral\Base\BaseLog;
use models\Logic\Jnb\LogicJnbCommon;
use models\Logic\LogicThreadPool;
use models\Logic\ThreadPool\LogicThreadPoolItem;
use models\Logic\Jnb\Account\Receipt\LogicJnbAccountReceiptAuto;
use models\Logic\ThreadPool\LogicThreadPoolException;
use Zend\Config\Reader\Ini;

setlocale( LC_ALL, 'ja_JP.UTF-8' );
ini_set( 'default_charset', 'UTF-8' );
mb_internal_encoding( 'UTF-8' );
mb_http_output('UTF-8');
mb_regex_encoding( 'UTF-8' );

ini_set( 'max_execution_time', 0 );

/**
 * JNBから受信した入金通知データを元に入金処理を実行するバッチアプリケーション
 */
class Application extends BaseApplicationAbstract {
    /** 入金処理ループの実行インターバル（秒） @var int */
    const BATCH_LOOP_INTERVAL = 30;

    /**
     * アプリケーション固有ID
     *
     * @access protected
     * @var string
     */
	protected $_application_id = 'jnb-auto-rcpt-batch';

	/**
	 * Application の唯一のインスタンスを取得します。
	 *
	 * @static
	 * @access public
	 * @return Application
	 */
	public static function getInstance() {
		if( self::$_instance === null ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Application の新しいインスタンスを初期化します。
	 *
	 * @ignore
	 * @access private
	 */
	private function __construct() {
		parent::init();
	}

	/**
     * DBアダプタ
     *
	 * @var Adapter
	 */
	public $dbAdapter;

    /**
     * ログクラス
     *
     * @var BaseLog
     */
    public $logger;

	/**
     * メールサーバ設定
     *
	 * @var メール環境
	 */
	public $mail;

    /**
     * ロックアイテム
     *
     * @access protected
     * @var LogicThreadPoolItem
     */
    protected $_lock;

	/**
	 * アプリケーションを実行します。
	 *
	 * @access public
	 */
	public function run() {

        // 夜間1,2時の時間帯は動作させない
        $hour = (int)date('G');
        if ($hour >= 1 && $hour < 3) {
            exit(0);
        }

        $exitCode = 1;

        try {

            // アプリケーションIDは入金処理ロジックで定義されている定数値に合わせる
            $this->_application_id = LogicJnbAccountReceipt::BATCH_THREAD_GROUP_NAME;

            $configPath = __DIR__ . '/../module/cbadmin/config/config.ini';

            // データベースアダプタをiniファイルから初期化します
            $data = array();
            if (file_exists($configPath))
            {
                $reader = new Ini();
                $data = $reader->fromFile($configPath);
            }

            $this->dbAdapter = new Adapter($data['database']);

            $globalConfig = include __DIR__ . '/../config/autoload/global.php';
            // 接続時間を設定する
            $rds_session_timezone = $globalConfig['RDS_SESSION_TIMEZONE'];
            if (isset($rds_session_timezone)) {
                $this->dbAdapter->query('SET SESSION time_zone = :time_zone')->execute(array(':time_zone'=>$rds_session_timezone));
            }

            // 設定をシステムプロパティテーブルから読み込み
            $apinfo = $this->getApplicationiInfo($this->dbAdapter, 'cbadmin');
            // iniファイルの内容をマージ
            $data = array_merge($data, $apinfo);

            // ログ設定の読み込み
            $logConfig = $data['log'];
            // 標準ログクラス初期化
            $this->logger = BaseLog::createFromArray( $logConfig );

            // メールに絡む属性を取得
            $this->mail = $data['mail'];

            // JNBロジックにメール設定を適用
            if(isset($this->mail['smtp'])) {
                LogicJnbCommon::setDefaultSmtpServer($this->mail['smtp']);
            }
            if(isset($this->mail['charset'])) {
                LogicJnbCommon::setDefaultMailCharset($this->mail['charset']);
            }
            // スレッドプールにデフォルトDBアダプタを設定
            LogicThreadPool::setDefaultAdapter($this->dbAdapter);

            // loggerの割り当て
            LogicJnbCommon::setDefaultLogger($this->logger);

            $this->logger->log(BaseLog::DEBUG, 'batch process start.');

            $db = $this->dbAdapter;

            // ロック獲得を試行
            $option = array();
            $option[LogicThreadPool::OPTION_DB_ADAPTER] = $db;
            $option[LogicThreadPool::OPTION_THREAD_LIMIT] = 1;
            $option[LogicThreadPool::OPTION_LOCKWAIT_TIMEOUT] = 5;
            $option[LogicThreadPool::OPTION_LOCK_RETRY_INTERVAL] = 1;
            $pool = LogicThreadPool::getPool(LogicJnbAccountReceipt::BATCH_THREAD_GROUP_NAME, $option);

            // ロック獲得。獲得出来ない場合はLogicThreadPoolException例外が発生
            $this->_lock = $pool->openAsSingleton(getmypid());

            // ロックアイテムのクリーンナップ条件を整備
            $where = sprintf(' ThredGroup = %s AND ThreadId <> %d AND Status = 8 ', $this->_application_id, $this->_lock->getThreadId());

            // ロックが獲得できたので自動入金処理を準備
            $rcptResultMap = LogicJnbAccountReceipt::getReceiptResultMap();
            // 自動入金ロジックのインスタンス初期化にあたってログ設定をリセット
            $logic = new LogicJnbAccountReceiptAuto($this->dbAdapter);

            $start = microtime(true);
            $msg = '';
            $msgLevel = BaseLog::DEBUG;

            $this->logger->log($msgLevel,'begin auto receipt');

            // 自動入金処理本体
            // ※：トランザクション管理はreceiptAll内で行われるので、ここでは単にメソッドを呼ぶだけ
            try {

                $results = $logic->receiptAll();

                $buf = array();
                $total = 0;
                foreach($results as $type => $count) {
                    // 実行結果をキー毎に整形
                    $buf[] = sprintf('"%s"=%s', $rcptResultMap[$type], f_nf($count, '#,##0'));
                    $total += $count;
                }

                // 正常終了メッセージを構築
                $msg = sprintf('auto receipt completed normally. elapsed time = %s, %s items processed.',
                (microtime(true) - $start),
                f_nf($total, '#,##0'),
                join(', ', $buf));

                // 処理件数が1件でもあれば内訳をメッセージに追加
                if($total > 0) $msg .= sprintf(' (%s)', join(', ', $buf));

            } catch(\Exception $rcptError) {
                // エラーメッセージを構築
                $msg = sprintf('auto receipt completed abnormally. elapsed time = %s (%s [%s])',
                (microtime(true) - $start),
                $rcptError->getMessage(),
                get_class($err));

                // INFOレベルでロギング
                $msgLevel = BaseLog::INFO;

                // エラーは上位へスロー
                throw $rcptError;
            }

            // 完了メッセージをロギング
            $this->logger->log($msgLevel, $msg);

            // 正常終了したのでロックを解放
            try {
                $this->_lock->terminate();

            } catch(LogicThreadPoolException $releaseError) {
                // ロック解放時の例外は無視する
            }

            // 正常終了
            $exitCode = 0;

        } catch(LogicThreadPoolException $lockError) {
            // ロック獲得失敗メッセージをロギング
            $this->logger->log(BaseLog::DEBUG, 'batch process terminated. (cannot get lock)');
            // ロック獲得失敗であれば、正常終了とみなす
            $exitCode = 0;

        } catch(\Exception $err) {
            // その他の周辺エラー

            // ロックを異常終了で解放
            if($this->_lock && $this->_lock->isAlive()) {
                try {
                    $this->_lock->abend(sprintf('%s (%s)', $err->getMessage(), get_class($err)));
                } catch(\Exception $releaseError) {
                    // ロック解放時の例外は個別にロギング
                    $this->logger->log(BaseLog::ERR, sprintf('cannot release lock !!! (%s [%s])', $releaseError->getMessage(), get_class($releaseError)));
                }
            }

            $this->logger->log(BaseLog::INFO, sprintf('batch process terminated. (%s [%s])', $err->getMessage(), get_class($err)));
        }

        // 終了コードを指定して処理終了
        exit($exitCode);
	}
}

Application::getInstance()->run();