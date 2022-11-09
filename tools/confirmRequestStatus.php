<?php
chdir(dirname(__DIR__));

// Setup autoloading
require 'init_autoloader.php';

setlocale( LC_ALL, 'ja_JP.UTF-8' );
ini_set( 'default_charset', 'UTF-8' );
mb_internal_encoding( 'UTF-8' );
mb_http_output('UTF-8');
mb_regex_encoding( 'UTF-8' );

ini_set( 'max_execution_time', 0 );

/**
 * アプリケーションクラスです。
 *
 */
use Coral\Base\Application\BaseApplicationAbstract;
use Coral\Base\BaseLog;
use Zend\Db\Adapter\Adapter;
use Zend\Config\Reader\Ini;
use models\Table\TableUser;
use models\Table\TableEnterpriseCustomer;

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
     * @var Log
     */
    public $logger;

    /**
     * @var メール環境
     */
    public $mail;

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

$this->logger->info('confirmRequestStatus.php start');

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

            // ユーザーID取得
            $mdlu = new TableUser($this->dbAdapter);
            $userId = $mdlu->getUserId(TableUser::USERCLASS_SYSTEM, TableUser::SEQ_BATCH_USER);

            // トランザクション開始
            $this->dbAdapter->getDriver()->getConnection()->beginTransaction();

            // 加盟店顧客テーブルを更新する(申込ステータスの2:完了化)
            $this->confirmRequestStatus($userId);

            // コミット
            $this->dbAdapter->getDriver()->getConnection()->commit();

$this->logger->info('confirmRequestStatus.php end');
            $exitCode = 0;

        } catch( \Exception $e ) {
            $this->dbAdapter->getDriver()->getConnection()->rollback();
            // エラーログを出力
            if ( isset($this->logger) ) {
$this->logger->err($e->getMessage());
            }
        }

        // 終了コードを指定して処理終了
        exit($exitCode);

    }

    /**
     * 加盟店顧客テーブルを更新する(申込ステータスの2:完了化)
     *
     * @param int $prmUserId ユーザーID
     */
    protected function confirmRequestStatus($prmUserId) {
        $mdlec = new TableEnterpriseCustomer($this->dbAdapter);

        // 申込ステータス更新対象(申込ステータス＝1:申請中)取得
        $sql = <<<EOQ
SELECT EntCustSeq
FROM   T_EnterpriseCustomer
WHERE  1 = 1
AND    RequestCompScheduleDate IS NOT NULL
AND    RequestCompScheduleDate <= :RequestCompScheduleDate
AND    RequestStatus = 1
AND    ValidFlg = 1
EOQ;
        $ri = $this->dbAdapter->query($sql)->execute(array(':RequestCompScheduleDate' => date('Y-m-d')));

        // 申込ステータスの2:完了化
        foreach ($ri as $row) {
            $mdlec->saveUpdate(array('RequestStatus' => 2, 'RequestSubStatus' => 0, 'RequestCompDate' => date('Y-m-d H:i:s'), 'UpdateDate' => date('Y-m-d H:i:s'), 'UpdateId' => $prmUserId), $row['EntCustSeq']);
        }
    }
}

Application::getInstance()->run();
