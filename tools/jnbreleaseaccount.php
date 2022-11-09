<?php
chdir(dirname(__DIR__));

// Setup autoloading
require 'init_autoloader.php';

use Coral\Base\Application\BaseApplicationAbstract;
use models\Logic\Jnb\LogicJnbCommon;
use Zend\Config\Reader\Ini;
use Zend\Log\Formatter\Base;
use Coral\Base\BaseLog;
use models\Logic\Jnb\LogicJnbAccount;
use models\Logic\Jnb\LogicJnbConfig;
use Zend\Db\Adapter\Adapter;

setlocale( LC_ALL, 'ja_JP.UTF-8' );
ini_set( 'default_charset', 'UTF-8' );
mb_internal_encoding( 'UTF-8' );
mb_http_output('UTF-8');
mb_regex_encoding( 'UTF-8' );

ini_set( 'max_execution_time', 0 );

/**
 * JNB以外で入金された注文に対するJNB口座のメンテナンスを行うバッチ
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
	protected $_application_id = 'jnb-release-account-batch';

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
	 * アプリケーションを実行します。
	 *
	 * @access public
	 */
	public function run() {
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
		LogicJnbCommon::setDefaultLogger($this->logger);

		$this->logger->debug('batch process start.');

        // 通常の解放処理
        $retry = 0;
        while(true) {
            $results = $this->releaseClosedAccount();
            if(!count($results['error'])) break;
            if(++$retry < 3) {
                $this->logger->info(sprintf('%s errors occured. retrying after 5 seconds.', f_nf(count($results['error']), '#,##0')));
                sleep(5);
            } else {
                // リトライ3回でもエラーが解消されなかったら処理をあきらめる
                $this->logger->warn(sprintf('%s errors occured and retry count over 3 times.', f_nf(count($results['error']))));
                foreach($results['error'] as $accSeq) {
                    $this->logger->warn(sprintf(' - AccountSeq: %s', $accSeq));
                }
                break;
            }
        }

        // TODO: 再7期限超過の強制開放処理を実装

		$this->logger->debug('batch process completed.');
	}

    /**
     * 開放待ち口座を解放する
     *
     * @access protected
     * @return array
     */
    protected function releaseClosedAccount() {
		$db = $this->dbAdapter;
		$logic = new LogicJnbAccount($db);
        $jnbConfig = new LogicJnbConfig($db);
        $days = $jnbConfig->getReleaseAfterReceiptInterval();

        // 20時以前は前日を0日経過とする
        if(date('H') < 20) $days--;

        $date = date('Y-m-d', strtotime(date('Y-m-d')) - (86400 * $days));
        $targets = $logic->findAccountsForReleaseByDate($date);
        $this->logger->debug(sprintf('[releaseClosedAccount] release threshold: %s days (closed in %s), target count = %s',
                               $days,
							   $date,
                               f_nf(count($targets), '#,##0')));
        $results = array(
            'success' => array(),
            'error' => array(),
            'ignore' => array()
        );
        foreach($targets as $target) {
            $db->getDriver()->getConnection()->beginTransaction();
            try {
                $this->logger->debug(sprintf('[releaseClosedAccount] release %s:%s-%s (oseq = %s, OrderId = %s, reason = %s, %s)',
                                       $target['AccountSeq'],
                                       $target['BranchCode'],
                                       $target['AccountNumber'],
                                       $target['OrderSeq'],
                                       $target['OrderId'],
                                       $target['CloseReason'],
                                       $target['CloseMemo']));
                $acc = $logic->releaseAccount($target['OrderSeq']);
                $results['success']++;
                if(!$acc) {
                    // クローズできた口座がないので無視リストに加える
                    $db->getDriver()->getConnection()->rollBack();
                    $this->logger->debug('[releaseClosedAccount] account not found.');
                    $results['ignore'][] = $target['AccountSeq'];
                } else {
                    // クローズ成功
                    $db->getDriver()->getConnection()->commit();
                    $this->logger->debug('[releaseClosedAccount] done.');
                    $results['success'][] = $target['AccountSeq'];
                }
            } catch(\Exception $err) {
                // エラー発生
                $db->getDriver()->getConnection()->rollBack();
                $this->logger->info(sprintf('[releaseClosedAccount] an error has occured. error = %s', $err->getMessage()));
                $results['error'][] = $target['AccountSeq'];
            }
        }
        $this->logger->debug(sprintf('[releaseClosedAccount] total count: %s, success = %s, error = %s, ignore = %s',
                               f_nf(count($results['success']) + count($results['error']) + count($results['ignore'])),
                               f_nf(count($results['success'])),
                               f_nf(count($results['error'])),
                               f_nf(count($results['ignore']))));
        return $results;
    }
}

Application::getInstance()->run();
