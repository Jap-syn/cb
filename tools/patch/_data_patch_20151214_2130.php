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
use Zend\Db\Adapter\Adapter;
use Zend\Config\Reader\Ini;
use Coral\Base\BaseLog;
use Coral\Base\BaseGeneralUtils;
use oemmember\Controller\AccountController;
use models\Table\TableSystemProperty;
use Coral\Base\Auth\BaseAuthUtility;
use models\Logic\LogicMypageOrder;

/**
 * アプリケーションクラスです。
 * 過去分で生きている注文の注文マイページを作成するバッチ
 *
 */
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
     * Logger
     * @var unknown
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

$this->logger->info('_data_patch_20151214_2130.php start');

            $globalConfig = include __DIR__ . '/../config/autoload/global.php';
            // 接続時間を設定する
            $rds_session_timezone = $globalConfig['RDS_SESSION_TIMEZONE'];
            if (isset($rds_session_timezone)) {
                $this->dbAdapter->query('SET SESSION time_zone = :time_zone')->execute(array(':time_zone'=>$rds_session_timezone));
            }

$this->dbAdapter->getDriver()->getConnection()->beginTransaction();

            // 本処理
            $this->_exec();

$this->dbAdapter->getDriver()->getConnection()->commit();
// $this->dbAdapter->getDriver()->getConnection()->rollback();

$this->logger->info('_data_patch_20151214_2130.php end');
            $exitCode = 0; // 正常終了
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

    /**
     * 本処理
     */
    public function _exec()
    {
        // 認証関連
        $mdlsys = new TableSystemProperty($this->dbAdapter);
        $authUtil = new BaseAuthUtility($mdlsys->getHashSalt());

        // 対象データ取得
        // データステータス：51、61 かつ 注文マイページが作成されていないものを抽出
        $sql = "";
        $sql .= " SELECT  o.OrderSeq ";
        $sql .= "     ,   cc.F_LimitDate ";
        $sql .= "     ,   IFNULL(o.OemId, 0) AS OemId ";
        $sql .= " FROM    T_Order o ";
        $sql .= "         INNER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.OrderSeq) ";
        $sql .= " WHERE   o.DataStatus IN (51, 61) ";
        $sql .= " AND     0 = (SELECT COUNT(1) FROM T_MypageOrder WHERE OrderSeq = o.OrderSeq) ";
//         $sql .= " LIMIT 5 ";        // ﾃﾞﾊﾞｯｸﾞ用
$this->logger->info($sql);

        $ri = $this->dbAdapter->query($sql)->execute(null);

        // ユーザIDの取得
        $userId = 1;

        // 取得できたデータ分ループする
        $logicmo = new LogicMypageOrder($this->dbAdapter);
        foreach ($ri as $value) {
// $this->logger->info(sprintf('OrderSeq = %s, F_LimitDate = %s, OemId = %s', $value['OrderSeq'], $value['F_LimitDate'], $value['OemId']));
            // 取得データを引数に注文マイページ作成処理をコールする
            $logicmo->createMypageOrder($value['OrderSeq'], $value['F_LimitDate'], $value['OemId'], $userId, $authUtil);
        }
        return;
    }
}

Application::getInstance()->run();
