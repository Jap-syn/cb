<?php
chdir(dirname(__DIR__));

require 'init_autoloader.php';

use Coral\Base\Application\BaseApplicationAbstract;
use Coral\Coral\History\CoralHistoryOrder;
use Coral\Coral\Mail\CoralMail;
use models\Logic\Jnb\LogicJnbAccount;
use models\Logic\LogicSmbcRelation;
use models\Logic\Smbcpa\LogicSmbcpaAccount;
use models\Table\ATableOrder;
use models\Table\TableCancel;
use models\Table\TableOemClaimFee;
use models\Table\TableOemSettlementFee;
use models\Table\TableOrder;
use models\Table\TablePayingAndSales;
use models\Table\TableSBPaymentSendResultHistory;
use models\Table\TableSite;
use models\Table\TableStampFee;
use Zend\Db\Adapter\Adapter;
use Zend\Config\Reader\Ini;
use Coral\Base\BaseLog;
use models\Logic\LogicCancel;
use models\Table\TableUser;
use Zend\Json\Json;

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
         * ログクラス
         *
         * @var BaseLog
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

$this->logger->info('old_delete.php start');

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

            // 主処理
            $this->exec();

$this->logger->info('old_delete.php end');
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

        private function exec() {
       
        $this->dbAdapter->query('delete from T_MailSendHistory where MailSendDate < DATE_SUB(CURDATE(), INTERVAL 2 YEAR) LIMIT 100000')->execute();
        
    }

}
Application::getInstance()->run();