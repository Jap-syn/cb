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
use Coral\Base\Auth\BaseAuthUtility;
use Coral\Base\BaseGeneralUtils;
use Coral\Base\BaseLog;

use Zend\Config\Reader\Ini;
use Zend\Db\Adapter\Adapter;
use Zend\Json\Json;

use models\Logic\Exception\LogicClaimException;
use models\Logic\LogicTemplate;

use models\Table\T_OrderNotClose;

class Application extends BaseApplicationAbstract {
    protected $_application_id = 'tools';

    private $checkcsv;

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
        $isBeginTran = false;

        try {

            // データベースアダプタをiniファイルから初期化します
            $configPath = __DIR__ . '/../module/cbadmin/config/config.ini';
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

$this->logger->info('orderLinkInfo_upd.php start');

            // メイン処理
            $this->orderLinkInfo_upd();

$this->logger->info('orderLinkInfo_upd.php end');

            $exitCode = 0; // 正常終了

        } catch( \Exception $e ) {
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
     * 対象の注文を削除
     *
     */
    protected function orderLinkInfo_upd() {

    // 処理結果反映SQL
    $sql =<<<EOQ
DELETE onc FROM T_OrderNotClose onc
INNER JOIN T_Order o
  ON o.OrderSeq = onc.OrderSeq
  WHERE onc.RegistDate < :TargetDate
  OR o.DataStatus = 91
EOQ;

        // 2-1. 対象の注文を物理削除
        $this->dbAdapter->query($sql)->execute(array(':TargetDate' => date("Y-m-d", strtotime("-2 year"))));

        return;
    }
}


Application::getInstance ()->run ();
