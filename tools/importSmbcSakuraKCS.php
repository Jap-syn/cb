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
use Coral\Coral\History\CoralHistoryOrder;
use models\Logic\Smbcpa\Account\Receipt\LogicSmbcpaAccountReceiptAuto;
use models\Table\ATablePayingAndSales;
use models\Table\ATableReceiptControl;
use models\Table\TableCode;
use models\Table\TableImportedSmbcPerfect;
use models\Table\TableOrder;
use models\Table\TablePayingAndSales;
use models\Table\TableReceiptControl;
use models\Table\TableUser;
use models\Table\TableSmbcpa;
use models\Table\TableSmbcpaAccount;
use models\Table\TableSmbcpaAccountGroup;
use models\Table\TableSmbcpaPaymentNotification;
use models\Table\TableSmbcpaAccountUsageHistory;
use models\Table\TableStagnationAlert;
use models\Table\TableSundryControl;
use models\Table\TableSystemProperty;
use Zend\Db\Adapter\Adapter;
use Zend\Config\Reader\Ini;
use Zend\I18n\Translator\LoaderPluginManager;
use Zend\Db\Sql\Ddl\Column\Decimal;
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

$this->logger->info('importSmbcSakuraKCS.php start');

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

            // 一時保存フォルダの特定
            $mdlsp = new TableSystemProperty($this->dbAdapter);
            $transDir = $mdlsp->getValue(TableSystemProperty::DEFAULT_MODULE, 'smbcpa', 'TempFileDir');
            $tempDir = $mdlsp->getValue(TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'TempFileDir');

            // 取込ファイル名の生成
            $dateTime = new DateTime();
            $fileName = 'nyusyukin_'. $dateTime->format('Ymd_His'). ".dat";

            // ファイルフルパスの生成
            $pathFileName = $transDir . '/' . $fileName;

            // jarファイルのファイルパス
            $jarFilePath = 'hccommand.jar';

            // jar用設定ファイル
            $jarConfigXml = 'config.xml';

            // ダウンロード対象のファイルが格納されたフォルダ
            $parentObjectPath = $mdlsp->getValue(TableSystemProperty::DEFAULT_MODULE, 'smbcpa', 'DownloadDir');

            // ダウンロード対象のファイル
            $registeredFileName = 'nyusyukin_yyyymmdd_hhmiss.dat';

            // シェルのメタ文字をエスケープ
            // -jar                  （ 必須 ）実行する jar ファイルの指定
            // -cmd                  （ 必須 ）{ download | dl } ダウンロードコマンドの指定
            // -config               （ 必須 ）設定ファイル
            // -parent_object_path   （ 必須 ）ダウンロード対象のファイルが格納されたフォルダのオブジェクトパス
            // -local_directory      （ 必須 ）ダウンロードされたファイルの保存先となるローカルシステム上のフォルダ名
            $jarStr  = 'java';
            $jarStr .= ' -jar '. $jarFilePath;
            $jarStr .= ' -cmd download';
            $jarStr .= ' -config '. $jarConfigXml;
            $jarStr .= ' -parent_object_path '. $parentObjectPath;
            $jarStr .= ' -local_directory '. $transDir;
            $jarCmd = escapeshellcmd( $jarStr );

$this->logger->info('importSmbcSakuraKCS.php javaCommand='. $jarStr);

            // ※JARのログ出力機能が出力先を指定出来ない作りのためワーキングディレクトリを移動
            chdir( './tools/CommandClient_tool' );

            // 取込ファイルの取得
            $jarResult = shell_exec( $jarCmd );
            $jarResult = str_replace('ERROR:', '', $jarResult);
            $jarResult = str_replace("\n", "", $jarResult);

            // 移動したワーキングディレクトリを戻す
            chdir( '../../' );

            // 処理結果がエラーの場合
            if ( $jarResult ) {
                // コードマスタからエラーメッセージを取得する
                $mdlc = new TableCode($this->dbAdapter);
                $errMsg = $mdlc->getMasterDescription('204', $jarResult);
                // 取得出来ない場合は固定文字
                $errMsg = ( empty( $errMsg ) ) ? '取込ファイルの取得に失敗しました。' : ("\n". $errMsg);
                throw new \Exception( 'importSmbcSakuraKCS.php err：['. $jarResult. ']'. $errMsg );
            }

            // 一時フォルダに移動させた入金ファイルの列挙
            $fpath_array = array();
            $pattern = $tempDir. '/nyusyukin_*.dat';
            $mdlinst = new TableImportedSmbcPerfect($this->dbAdapter);
            foreach ( glob( $pattern, GLOB_NOSORT ) as $file ) {
                // ファイルの存在確認（念のため）
                if ( file_exists( $file ) ) {
                    // 取込済みの確認
                    $row = $this->dbAdapter->query(" SELECT Seq FROM T_ImportedSmbcPerfect WHERE FileName = :FileName ")->execute( array(':FileName' => basename( $file ) ) )->current();
                    if ( !$row ) {
                        // 未取込分のみ
                        $fpath_array[] = basename( $file );
                    }
                }
            }

            // 移動したファイルの存在確認、無い場合は終了
            if ( count( $fpath_array ) <= 0 ) {
                $this->logger->info('importSmbcSakuraKCS.php end');
                $exitCode = 0;
                exit($exitCode);
            }

$this->logger->info('importSmbcSakuraKCS.php ImportedFileCount='. count( $fpath_array ));

            // SMBCパーフェクト口座結果ファイル取込
            // ※１：処理を同期させたいのでリダイレクトは指定しない
            $returnCd = 0;
            $errFlg = false;
            foreach ( $fpath_array as $filePath ) {
                $errormessage = "";
                $returnCd = exec("php ./tools/importSmbcPerfect.php ". $filePath. " importSmbcSakuraKCS");
                if ( $returnCd ) $errFlg = true;
            }

$this->logger->info('importSmbcSakuraKCS.php end');
            $exitCode = $errFlg;

        } catch( \Exception $e ) {
            // エラーログを出力
            if ( isset( $this->logger ) ) {
$this->logger->err($e->getMessage());

            }
            // ワークディレクトリの確認（念のため）
            if ( strstr(getcwd(), 'CommandClient_tool') ) {
                // 移動しているワーキングディレクトリを戻す
                chdir( '../../' );
            }

        }

        // 終了コードを指定して処理終了
        exit($exitCode);

    }
}

Application::getInstance()->run();
