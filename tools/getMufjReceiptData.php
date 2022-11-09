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
use Coral\Base\IO\BaseIOUtility;
use Zend\Db\Adapter\Adapter;
use Zend\Config\Reader\Ini;
use models\Table\TableUser;
use models\Table\TableCode;
use models\Logic\LogicCreditTransfer;
use models\Table\TableSystemProperty;
use Zend\Http\Client;

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

$this->logger->info('getMufjReceiptData.php start');

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

            $this->execMain();

$this->logger->info('getMufjReceiptData.php end');
            $exitCode = 0;

        } catch( \Exception $e ) {
            // エラーログを出力
            if ( isset($this->logger) ) {
$this->logger->err($e->getMessage());
            }
        }

        // 終了コードを指定して処理終了
        exit($exitCode);

    }

    private function execMain()
    {
        $mdlsp = new TableSystemProperty($this->dbAdapter);
        $param = array();

        // ユーザーID取得
        $mdlu = new TableUser($this->dbAdapter);
        $userId = $mdlu->getUserId(TableUser::USERCLASS_SYSTEM, TableUser::SEQ_BATCH_USER);

        // 収納企業番号
        $company = $mdlsp->getValue(TableSystemProperty::DEFAULT_MODULE, 'mufjpayment', 'company');
        $sql = " INSERT INTO T_MufjReceipt(ResponseData, RegistDate, RegistId, UpdateDate, UpdateId) values (:ResponseData, :RegistDate, :RegistId, :RegistDate, :RegistId) ";
        $stmt = $this->dbAdapter->query($sql);


        $param['COMPANY_BN'] = $company;
        $param['YMD_FROM'] = date('Ymd');
        $param['YMD_TO'] = date('Ymd');
        $param['GET_CONDITIONS'] = 1;
        $param['DATA_KEY'] = '';
        $param['REQUEST_TIME'] = date('YmdHis').'000000';
        $param['COMPLETION_FLG'] = 0;
        $param['CERT_KEY'] = $this->createHash($param);

        $response = $this->apiSend($param);
        while (true) {
            // データ登録
            if (strlen($response['RECEIPT_DATA']) > 0) {
                for ($i=0;$i<strlen($response['RECEIPT_DATA']);$i+=120) {
                    $data = mb_substr($response['RECEIPT_DATA'], $i, 120);
                    $stmt->execute(array(':ResponseData'=>$data, ':RegistDate'=>date('Y-m-d H:i:s'), ':RegistId'=>$userId));
                }
            }

            // 終了リクエスト
            if (($response['DATA_FLG'] == 2) || ($response['DATA_FLG'] == 3)) {
                $param['COMPANY_BN'] = $company;
                $param['YMD_FROM'] = '';
                $param['YMD_TO'] = '';
                $param['GET_CONDITIONS'] = '';
                $param['DATA_KEY'] = $response['DATA_KEY'];
                $param['REQUEST_TIME'] = date('YmdHis').'000000';
                $param['COMPLETION_FLG'] = 1;
                $param['CERT_KEY'] = $this->createHash($param);
                $response = $this->apiSend($param);
                break;
            }

            // リクエスト
            $param['COMPANY_BN'] = $company;
            $param['YMD_FROM'] = '';
            $param['YMD_TO'] = '';
            $param['GET_CONDITIONS'] = '';
            $param['DATA_KEY'] = $response['DATA_KEY'];
            $param['REQUEST_TIME'] = date('YmdHis').'000000';
            $param['COMPLETION_FLG'] = 0;
            $param['CERT_KEY'] = $this->createHash($param);
            $response = $this->apiSend($param);
        }

        //

    }

    private function createHash($param)
    {
        $mdlsp = new TableSystemProperty($this->dbAdapter);
        $pass = $mdlsp->getValue(TableSystemProperty::DEFAULT_MODULE, 'mufjpayment', 'password');
        $char = $param['COMPANY_BN'].$param['YMD_FROM'].$param['YMD_TO'].$param['GET_CONDITIONS'].$param['DATA_KEY'].$param['REQUEST_TIME'].$param['COMPLETION_FLG'].$pass;
        return hash('sha256', $char);
    }

    private function apiSend($param)
    {
        try {
            // 接続情報取得
            $mdlsp = new TableSystemProperty($this->dbAdapter);
            $url = $mdlsp->getValue(TableSystemProperty::DEFAULT_MODULE, 'mufjpayment', 'url');
            $timeout = $mdlsp->getValue(TableSystemProperty::DEFAULT_MODULE, 'mufjpayment', 'timeout');

            $option = array(
                'adapter'=> 'Zend\Http\Client\Adapter\Curl', // SSL通信用に差し替え
                'ssltransport' => 'tls',
                'maxredirects' => 1,                         // 試行回数(maxredirects) を 1 に設定
            );
            $client = new Client($url, $option);
            $client->setOptions(array('timeout' => (int)$timeout, 'keepalive' => true, 'maxredirects' => 1));

            $this->logger->info('Request:' . json_encode($param));
            $response = $client
                ->setRawBody(json_encode($param))
                ->setEncType('application/json; charset=UTF-8', ';')
                ->setMethod('Post')
                ->send();

            // 結果を取得する
            $status = $response->getStatusCode();
            $res_msg = $response->getReasonPhrase();
            $res_msg = mb_convert_encoding($res_msg, mb_internal_encoding(), BaseIOUtility::detectEncoding($res_msg));

            $this->logger->info('Receive:' . $res_msg);
            if ($status == 200) {
                $body = json_decode($response->getBody(), true);
                $this->logger->info('Response:' . $response->getBody());
                if ($body['STATUS'] != 0) {
                    $this->logger->info('リクエストで正常終了以外が返ってきましたので処理終了します。　'.$body['STATUS']);
                    $this->logger->info('ERROR_CD：'.$body['ERROR_CD']);
                    $this->logger->info('ERROR_MESSAGE：'.$body['ERROR_MESSAGE']);
                    throw new Exception();
                }
                return $body;
            }

            $this->logger->info('MUFJ連携で失敗しました。　'.$status);
            throw new Exception();
        }
        catch (\Exception $err) {
            $this->logger->info('MUFJ連携で異常が発生しました。　'.$err->getMessage());
            throw $err;
        }
    }
}

Application::getInstance()->run();
