<?php
chdir(dirname(__DIR__));

// Setup autoloading
require 'init_autoloader.php';

use Coral\Base\Application\BaseApplicationAbstract;
use Zend\Db\Adapter\Adapter;
use Zend\Config\Reader\Ini;
use models\Logic\LogicAgency;
use Coral\Base\BaseLog;
use models\Table\TableSystemProperty;

/**
 * アプリケーションクラスです。
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

$this->logger->info('updatebusinessdate.php start');

            $globalConfig = include __DIR__ . '/../config/autoload/global.php';
            // 接続時間を設定する
            $rds_session_timezone = $globalConfig['RDS_SESSION_TIMEZONE'];
            if (isset($rds_session_timezone)) {
                $this->dbAdapter->query('SET SESSION time_zone = :time_zone')->execute(array(':time_zone'=>$rds_session_timezone));
            }

            // 業務日付を+1日更新する
            $sql  = " UPDATE T_SystemProperty ";
            $sql .= "    SET PropValue = CAST(DATE_ADD(STR_TO_DATE(PropValue, '%Y-%m-%d'), interval 1 day) AS CHAR) ";
            $sql .= "  WHERE Module    = :Module ";
            $sql .= "    AND Category  = :Category ";
            $sql .= "    AND Name      = :Name ";

            $prm = array(
                ':Module'   => TableSystemProperty::DEFAULT_MODULE,
                ':Category' => 'systeminfo',
                ':Name'     => 'BusinessDate',
            );
            $this->dbAdapter->query($sql)->execute($prm);

$this->logger->info('updatebusinessdate.php end');
            $exitCode = 0; // 正常終了

        } catch(\Exception $e) {
            // エラーログを出力
            if ( isset($this->logger) ) {
$this->logger->err($this->logger->err($e->getMessage()));
            }
        }

        // 終了コードを指定して処理終了
        exit($exitCode);

    }
}

Application::getInstance()->run();
