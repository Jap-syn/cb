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


use Coral\Base\Application\BaseApplicationAbstract;
use Coral\Base\BaseLog;
use Zend\Db\Adapter\Adapter;
use Zend\Config\Reader\Ini;
use models\Table\TableUser;
use models\Table\TableOrderSummary;
use models\Logic\LogicOrderRegister;


/**
 * アプリケーションクラスです。
 *
 */
class Application extends BaseApplicationAbstract {
    protected $_application_id = 'tools-ModifyCombinedData-batch';

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
     * アプリケーションを実行します。
     *
     * @access public
     */
    public function run() {
        $exitCode = 1;

        try {

            // iniファイルから設定を取得
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
$this->logger->info('ModifyCombinedData.php start');

            $globalConfig = include __DIR__ . '/../config/autoload/global.php';
            // 接続時間を設定する
            $rds_session_timezone = $globalConfig['RDS_SESSION_TIMEZONE'];
            if (isset($rds_session_timezone)) {
                $this->dbAdapter->query('SET SESSION time_zone = :time_zone')->execute(array(':time_zone'=>$rds_session_timezone));
            }

$this->logger->DEBUG('rds_session_timezone:'.$rds_session_timezone);

            // 設定をシステムプロパティテーブルから読み込み
            $apinfo = $this->getApplicationiInfo($this->dbAdapter, 'cbadmin');
            // iniファイルの内容をマージ
            $data = array_merge($data, $apinfo);

            // ユーザーID取得
            $mdlu = new TableUser($this->dbAdapter);
            $userId = $mdlu->getUserId(TableUser::USERCLASS_SYSTEM, TableUser::SEQ_BATCH_USER);

$this->logger->DEBUG('userId:'.$userId);

            $this->dbAdapter->getDriver()->getConnection()->beginTransaction();

            $mdlOs = new TableOrderSummary($this->dbAdapter);
            $logicOr = new LogicOrderRegister( $this->dbAdapter );

            // データ修正パッチ対象のデータを取得
            $sql = "";
            $sql .= " SELECT o.OrderSeq ";
            $sql .= "       ,ec.EntCustSeq ";
            $sql .= "       ,mc.ManCustId ";
            $sql .= "   FROM T_Order o ";
            $sql .= "        join T_Customer c on o.OrderSeq = c.OrderSeq ";
            $sql .= "        Join T_EnterpriseCustomer ec on c.EntCustSeq = ec.EntCustSeq ";
            $sql .= "        Join T_ManagementCustomer mc on ec.ManCustId = mc.ManCustId ";
            $sql .= "  WHERE o.CombinedClaimParentFlg = 1 ";
            $sql .= "    AND o.CombinedClaimTargetStatus is null ";
//            $sql .= " LIMIT 100 ";        // ﾃﾞﾊﾞｯｸﾞ用
//            $sql .= " AND o.OrderSeq in ('20499405','20499406','20499407','20499408','20499409') ";  // ﾃﾞﾊﾞｯｸﾞ用

$this->logger->DEBUG($sql);

            $ri = $this->dbAdapter->query($sql)->execute(null);

            // 取得できたデータ分ループする
            foreach ($ri as $value) {
// $this->logger->DEBUG('OrderSeq:'.$value['OrderSeq'].'  EntCustSeq:'.$value['EntCustSeq'].'  ManCustId:'.$value['ManCustId']);

                // 取得したデータの、購入者情報、請求先情報を正しい正規化のロジックで修正する
                // TableOrderSummary::updateSummary() のコール
                $mdlOs->updateSummary($value['OrderSeq'], $userId);

                // 正規化した購入者情報を使用し管理顧客、加盟店顧客の紐付をあらためて行う
                // LogicOrderRegister::updateCustomer() のコール
                $logicOr->updateCustomer( $value['OrderSeq'] , $userId );

            }

            $this->dbAdapter->getDriver()->getConnection()->commit();

            $exitCode = 0; // 正常終了
$this->logger->info('ModifyCombinedData.php end');

        } catch( \Exception $e ) {
            try{
                $this->dbAdapter->getDriver()->getConnection()->rollback();
            } catch ( \Exception $err) { }

            // エラーログを出力
            if ( isset($this->logger) ) {
$this->logger->err($e->getMessage());
$this->logger->err($e->getTraceAsString());
            }
        }

        // 終了コードを指定して処理終了
        exit($exitCode);

    }
}

Application::getInstance()->run();
