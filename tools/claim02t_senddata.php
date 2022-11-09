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
use Zend\Db\Adapter\Adapter;
use Zend\Config\Reader\Ini;
use models\Table\TableUser;
use Coral\Coral\History\CoralHistoryOrder;
use models\Table\TableOrder;
use models\Table\TableBusinessCalendar;
use models\Table\TableClaimBatchControl;
use models\Table\TableSystemProperty;
use Coral\Coral\Mail\CoralMail;

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
     * @var BaseLog
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
        $isBeginTran = false;

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
            $this->logger->info('claim02t_senddata.php start');

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

            // 主処理
            // (東洋紙業のSFTPサーバーへ送信)
            $this->execSendData();

            // (請求バッチ管理(T_ClaimBatchControl)更新)
            $this->updClaimBatchControl($this->getClaimBatchControlSeq());

            // (請求確定処理スレッドスタート(非同期にて))
            if (\Coral\Base\BaseProcessInfo::isWin()) {
                $fp = popen('start php ' . __DIR__ . '/claim03t_compdata.php', 'r');
                pclose($fp);
            }
            else {
                exec('php ' . __DIR__ . '/claim03t_compdata.php > /dev/null &');
            }

            $this->logger->info('claim02t_senddata.php end');
            $exitCode = 0;

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
     * 請求バッチ管理の最新シーケンス取得
     *
     * @return int 請求バッチ管理の最新シーケンス
     * @see [請求バッチ管理.請求データ作成バッチ実行フラグ(MakeFlg)]が、[1:完了]であることは、本バッチ起動時には確定している
     */
    protected function getClaimBatchControlSeq() {
        return $this->dbAdapter->query(" SELECT Seq FROM T_ClaimBatchControl ORDER BY 1 DESC LIMIT 1 ")->execute(null)->current()['Seq'];
    }

    /**
     * ファイルを東洋紙業のSFTPサーバーへ送信します
     *
     * @return boolean true:送信成功, false:送信失敗
     */
    protected function execSendData() {
        $mdlsys = new TableSystemProperty($this->dbAdapter);

        // 共同印刷連携連携で使用する各種情報を取得
        $port = $mdlsys->getValue(TableSystemProperty::DEFAULT_MODULE, 'kyodoinfo', 'SFTPPORT');
        $host = $mdlsys->getValue(TableSystemProperty::DEFAULT_MODULE, 'kyodoinfo', 'SFTPIP');
        $id = $mdlsys->getValue(TableSystemProperty::DEFAULT_MODULE, 'kyodoinfo', 'SFTPID');
        $key = $mdlsys->getValue(TableSystemProperty::DEFAULT_MODULE, 'kyodoinfo', 'SFTPKEY');

        $filePath = __DIR__ . '/../data/sftp/';
        $localfile = $filePath . 'Claim1.zip';
        $shellfile = $filePath . 'upload.sh';

        $fh = fopen( $shellfile, 'w' );
        fwrite( $fh, "#!/bin/sh\n");
        fwrite( $fh, "sftp -oStrictHostKeyChecking=no -oIdentityFile=".$key." -oPort=\"".$port."\"  ".$id."@".$host." <<_EOD\n");
        fwrite( $fh, "put ".$localfile."\n");
        fwrite( $fh, "_EOD\n");
        fclose( $fh );

        $output = null;
        $retval = null;
        $result = exec('sh '.$shellfile, $output, $retval);

        if ($retval != 0) {
            // 接続に失敗した場合
            // エラー情報をログ出力
            $this->logger->err('[claim02t_senddata.php]' . $result);
            throw new Exception($result);
        }

        // TEMP領域削除
        unlink( $shellfile );

        return true;
    }

    /**
     * 請求バッチ管理(T_ClaimBatchControl)のSendFlgを、[1:完了]に更新する
     *
     * @param int $prmCbSeq T_ClaimBatchControlのSEQ
     */
    protected function updClaimBatchControl($prmCbSeq) {

        $mdlcb = new TableClaimBatchControl($this->dbAdapter);

        // データ更新
        $data = array(
            'SendFlg' => 1,
        );
        $mdlcb->saveUpdate($data, $prmCbSeq);
    }
}

Application::getInstance()->run();
