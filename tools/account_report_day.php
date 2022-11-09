<?php
chdir(dirname(__DIR__));

// Setup autoloading
require 'init_autoloader.php';

use Coral\Base\Application\BaseApplicationAbstract;
use Coral\Base\BaseLog;
use Zend\Db\Adapter\Adapter;
use Zend\Config\Reader\Ini;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Json\Json;
use models\Table\TableTemplateHeader;
use models\Table\TableTemplateField;
use models\Logic\LogicTreasurer;

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
$stt_time = microtime(true);
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

$this->logger->info('account_report_day.php start');

            $globalConfig = include __DIR__ . '/../config/autoload/global.php';
            // 接続時間を設定する
            $rds_session_timezone = $globalConfig['RDS_SESSION_TIMEZONE'];
            if (isset($rds_session_timezone)) {
                $this->dbAdapter->query('SET SESSION time_zone = :time_zone')->execute(array(':time_zone'=>$rds_session_timezone));
            }

            // 帳票作成用のﾛｼﾞｯｸをｺｰﾙ
            $logic = new LogicTreasurer($this->dbAdapter);
            $logic->account_report_day();
// 2015/11/30 Y.Suzuki Del 復元可能なようにｺﾒﾝﾄｱｳﾄ化 Stt
//             // 会計帳票/CSV作成処理
//             // (業務日付（バッチの実行時に利用する想定）)
//             $today = $this->dbAdapter->query(" SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'systeminfo' AND Name = 'BusinessDate' "
//                     )->execute(null)->current()['PropValue'];
// // $today = '2020-04-25';
//             $url = 'http://localhost/cbadmin/AccountReport/executedairy/day/' . $today;

//             // 一時ファイルの先指定
//             $mdlsp = new \models\Table\TableSystemProperty($this->dbAdapter);
//             $tempDir = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'TempFileDir');

//             $savename = $tempDir . '/kaikei_dairy_' . date('Ymd', strtotime($today)) . '.zip';
//             $client = new Zend\Http\Client();
//             $client->setStream();
//             $client->setUri($url);
//             $client->setOptions(array('timeout' => 21600, 'keepalive' => true, 'maxredirects' => 1, 'outputstream' => true));  // 20150717 試行回数(maxredirects) を 1 に設定
//             $response = $client->send();
//             copy($response->getStreamName(), $savename);

//             // 会計帳票ファイル日次登録
//             $this->_saveATReportFileDaily($today, $savename);

//             // ダウンロードファイル削除
//             unlink($savename);
// 2015/11/30 Y.Suzuki Del 復元可能なようにｺﾒﾝﾄｱｳﾄ化 End

$this->logger->info('account_report_day.php end');
            $exitCode = 0; // 正常終了
$end_time = microtime(true);
echo $end_time - $stt_time;
        } catch(\Exception $e) {
            // エラーログを出力
            if ( isset($this->logger) ) {
$this->logger->err($e->getMessage());
            }
        }

        // 終了コードを指定して処理終了
        exit($exitCode);
    }

    /**
     * 会計帳票ファイル日次登録
     *
     * @param string $today YYYY-MM-DD形式
     * @param string $filename ファイル名(ZIP形式)
     */
    protected function _saveATReportFileDaily($today, $filename)
    {
        // (ZIPファイル)
        $obj_file = null;
        if (!is_null($filename)) {
            $fp = fopen($filename, "rb");
            $obj_file = fread($fp, filesize($filename));
            fclose($fp);
        }

        // 既に登録がある場合はDELETE⇒INSERT
        $cnt = (int)$this->dbAdapter->query(" SELECT COUNT(1) AS cnt FROM AT_ReportFileDaily WHERE CreateDate = :CreateDate "
                )->execute(array(':CreateDate' => $today))->current()['cnt'];
        if ($cnt != 0) {
            $this->dbAdapter->query(" DELETE FROM AT_ReportFileDaily WHERE CreateDate = :CreateDate ")->execute(array(':CreateDate' => $today));
        }
        // 登録
        // ユーザーID(バッチユーザー)の取得
        $obj = new \models\Table\TableUser($this->dbAdapter);
        $userId = $obj->getUserId( 99, 1 );

        // 会計月（YYYY-MM-DD)※DDは01固定
        $sql = " SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'systeminfo' AND Name = 'AccountingMonth' ";
        $presentMonth = $this->dbAdapter->query($sql)->execute(null)->current()['PropValue'];

        $sql  = " INSERT INTO AT_ReportFileDaily (CreateDate, PresentMonth, ReportFile, Reserve, RegistDate, RegistId, ValidFlg) VALUES ( ";
        $sql .= "     :CreateDate ";
        $sql .= " ,   :PresentMonth ";
        $sql .= " ,   :ReportFile ";
        $sql .= " ,   :Reserve ";
        $sql .= " ,   :RegistDate ";
        $sql .= " ,   :RegistId ";
        $sql .= " ,   :ValidFlg ";
        $sql .= " ) ";

        $prm_save = array (
            'CreateDate'   => $today,
            'PresentMonth' => $presentMonth,
            'ReportFile'   => $obj_file,
            'Reserve'      => null,
            'RegistDate'   => date('Y-m-d H:i:s'),
            'RegistId'     => $userId,
            'ValidFlg'     => 1,
        );

        $this->dbAdapter->query($sql)->execute($prm_save);

        return;
    }
}

Application::getInstance()->run();
