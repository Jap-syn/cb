<?php
chdir(dirname(__DIR__));

// Setup autoloading
require 'init_autoloader.php';

/**
 * アプリケーションクラスです。
 *
 */
use Coral\Base\Application\BaseApplicationAbstract;
use Zend\Db\Adapter\Adapter;
use Zend\Config\Reader\Ini;
use Coral\Base\BaseLog;
use models\Logic\LogicCancel;
use models\Table\TableUser;
use models\Logic\OrderCancelException;

class Application extends BaseApplicationAbstract {
	protected $_application_id = 'tools';

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
	 * @var Adapter
	 */
	public $dbAdapter;

	/**
	 * メール環境
	 */
	public $mail;

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
	    $exitCode = 1;

	    try {

            $configPath = __DIR__ . '/../module/cbadmin/config/config.ini';
            // データベースアダプタをiniファイルから初期化します
            $data = array();
            if (file_exists($configPath))
            {
                $reader = new Ini();
                $data = $reader->fromFile($configPath);
            }

            $this->dbAdapter = new Adapter($data['database']);

            // ログ設定の読み込み
            $logConfig = $data['log'];
            // 標準ログクラス初期化
            $this->logger = BaseLog::createFromArray( $logConfig );

$this->logger->info('defectordercancel.php start');

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

            // メールに絡む属性
            $this->mail = $data['mail'];

            // 不備注文キャンセル処理
            $this->defectOrderCancel();

$this->logger->info('defectordercancel.php end');
            $exitCode = 0; // 正常終了

		} catch( \Exception $e ) {
		    // エラーログを出力
		    if ( isset($this->logger) ) {
$this->logger->err($e->getMessage());
		    }
		}

		// 終了コードを指定して処理終了
		exit($exitCode);

	}

	/**
	 * 不備注文をキャンセルする
	 */
	protected function defectOrderCancel() {
        $lgcCancel = new LogicCancel($this->dbAdapter);
        $mdlu = new TableUser($this->dbAdapter);

        $userId = $mdlu->getUserId(TableUser::USERCLASS_SYSTEM, TableUser::SEQ_BATCH_USER);

        // 不備注文で、保留期間を経過した注文を取得する
        $sql = <<<EOQ
SELECT o.OrderSeq
FROM T_Order o
     INNER JOIN AT_Order ao
             ON o.OrderSeq = ao.OrderSeq
WHERE 1 = 1
AND   ao.DefectFlg = 1
AND   ao.DefectCancelPlanDate <= :DefectCancelPlanDate
AND   o.Cnl_Status = 0
AND   o.DataStatus < 31
EOQ;

        $prm = array(
            ':DefectCancelPlanDate' => date('Y-m-d H:i:s'),
        );

        $ri = $this->dbAdapter->query($sql)->execute($prm);

        // 対象データを取得し、キャンセル処理を実施する
        foreach ($ri as $row) {
            $oseq = $row['OrderSeq'];

            try {
                $lgcCancel->applies($oseq, '入力不備による自動キャンセル', 8, 0, true, $userId);
            } catch (OrderCancelException $e) {
                // OrderCancelExceptionはチェック処理なので無視
            } catch (\Exception $e) {
                // 例外エラーは上位へ投げる
                throw $e;
            }
        }
	}
}

Application::getInstance()->run();
