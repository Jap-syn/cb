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

/**
 * アプリケーションクラスです。
 * [キャンセル備考]が複数表示される問題を解消するバッチ
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

            $this->logger->info('_data_patch_20220216_21.php start');

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

            $this->logger->info('_data_patch_20220216_21.php end');
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
        $templateId = 'CKA04016_1';
        // 指定条件からテンプレートSEQを検索
        $sql = " SELECT TemplateSeq FROM M_TemplateHeader WHERE TemplateId = :TemplateId ";
        $prm = array(
            ':TemplateId' => $templateId,
        );
        $ri = $this->dbAdapter->query($sql)->execute($prm);
        foreach ($ri as $row) {
            $sql_update  = " UPDATE M_TemplateField ";
            $sql_update .= " SET ";
            $sql_update .= "     LogicalName = :LogicalName ";
            $sql_update .= " WHERE PhysicalName = :PhysicalName ";
            $sql_update .= " AND  TemplateSeq = :TemplateSeq ";
            $prm_update = array(':LogicalName' => 'クレジット手続き期限日', ':PhysicalName' => 'CreditLimitDate', ':TemplateSeq' => $row['TemplateSeq']);
            $this->dbAdapter->query($sql_update)->execute($prm_update);
        }
    }
}

Application::getInstance()->run();
