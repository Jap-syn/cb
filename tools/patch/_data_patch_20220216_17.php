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

            $this->logger->info('_data_patch_20220216_17.php start');

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

            $this->logger->info('_data_patch_20220216_17.php end');
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
        $sql = " SELECT distinct M_TemplateField.TemplateSeq FROM  M_TemplateField JOIN M_TemplateHeader ON M_TemplateField.TemplateSeq = M_TemplateHeader.TemplateSeq WHERE M_TemplateHeader.TemplateId ='CKA01005_1' GROUP BY M_TemplateField.TemplateSeq ";
        $ri = $this->dbAdapter->query($sql)->execute(null);
        foreach ($ri as $row) {
            $sql_has_extra = " SELECT TemplateSeq FROM  M_TemplateField WHERE PhysicalName =:PhysicalName AND TemplateSeq = :TemplateSeq ";
            $prm_base_has_extra = array(':PhysicalName' => 'ExtraPayKey', ':TemplateSeq' => $row['TemplateSeq']);
            $ri_has_extra = $this->dbAdapter->query($sql_has_extra)->execute($prm_base_has_extra)->current()['TemplateSeq'];
            if($ri_has_extra != null) {
                continue;
            }
            $sql = " SELECT distinct TemplateSeq, MAX(ListNumber) as ListNumber FROM M_TemplateField WHERE TemplateSeq = :TemplateSeq";

            $listNumMax = $this->dbAdapter->query($sql)->execute(array(':TemplateSeq' => $row['TemplateSeq']))->current()['ListNumber'];

            //ListNumberがないテンプレートの場合はスキップ
            if($listNumMax == null){
                continue;
            }

            $sql  = " INSERT INTO M_TemplateField (`TemplateSeq`, `ListNumber`, `PhysicalName`, `LogicalName`, `FieldClass`, `RequiredFlg`, `DefaultValue`, `DispWidth`, `TableName`, `ValidationRegex`, `ApplicationData`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (";
            $sql .= "   :TemplateSeq ";
            $sql .= " , :ListNumber ";
            $sql .= " , :PhysicalName ";
            $sql .= " , :LogicalName ";
            $sql .= " , :FieldClass ";
            $sql .= " , :RequiredFlg ";
            $sql .= " , :DefaultValue ";
            $sql .= " , :DispWidth ";
            $sql .= " , :TableName ";
            $sql .= " , :ValidationRegex ";
            $sql .= " , :ApplicationData ";
            $sql .= " , :RegistDate ";
            $sql .= " , :RegistId ";
            $sql .= " , :UpdateDate ";
            $sql .= " , :UpdateId ";
            $sql .= " , :ValidFlg ";
            $sql .= " )";

            $stm = $this->dbAdapter->query($sql);

            $prm = array(
                ':TemplateSeq' => $row['TemplateSeq'],
                ':ListNumber' => $listNumMax + 1,
                ':PhysicalName' => 'ExtraPayKey',
                ':LogicalName' => 'トラッキングID',
                ':FieldClass' => 'CHAR',
                ':RequiredFlg' => 0,
                ':DefaultValue' => null,
                ':DispWidth' => 0,
                ':TableName' => null,
                ':ValidationRegex' => null,
                ':ApplicationData' => null,
                ':RegistDate' => date('Y-m-d H:i:s'),
                ':RegistId' => '1',
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => '1',
                ':ValidFlg' => 1,
            );
            $stm->execute($prm);
        }
    }
}

Application::getInstance()->run();
