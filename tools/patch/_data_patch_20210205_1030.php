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

$this->logger->info('_data_patch_20210205_1030.php start');

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

$this->logger->info('_data_patch_20210205_1030.php end');
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
        // 取引履歴検索テンプレートからNG無保証を削除
        $sql = <<<EOQ
SELECT h.TemplateSeq
FROM   M_TemplateHeader h
WHERE  h.TemplateId = 'CKA01005_1'
EOQ;
        $ri = $this->dbAdapter->query($sql)->execute(null);
        foreach ($ri as $row) {
            // PhysicalName = "NgNoGuaranteeChange" の ListNumberを取得
            $sql = " SELECT ListNumber FROM M_TemplateField WHERE TemplateSeq = :TemplateSeq AND PhysicalName = 'NgNoGuaranteeChange' ";

            $listNum = $this->dbAdapter->query($sql)->execute(array(':TemplateSeq' => $row['TemplateSeq']))->current()['ListNumber'];

            //ListNumberがないテンプレートの場合はスキップ
            if($listNum == null){
                continue;
            }

            // PhysicalName = "NgNoGuaranteeChange" のレコードを削除
            $sql = " DELETE FROM M_TemplateField WHERE TemplateSeq = :TemplateSeq AND ListNumber = :ListNumber ";
            $this->dbAdapter->query($sql)->execute(array(':TemplateSeq'=>$row['TemplateSeq'], ':ListNumber'=>$listNum));

            // PhysicalName = "NgNoGuaranteeChange" 以降の ListNumberに - 1
            $sql = " UPDATE M_TemplateField SET ListNumber = ListNumber - 1 WHERE TemplateSeq = :TemplateSeq AND ListNumber > :ListNumber ";
            $this->dbAdapter->query($sql)->execute(array(':TemplateSeq' => $row['TemplateSeq'], ':ListNumber'=>$listNum));

        }

    }
}

Application::getInstance()->run();
