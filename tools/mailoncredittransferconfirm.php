<?php
chdir(dirname(__DIR__));

// Setup autoloading
require 'init_autoloader.php';

use Coral\Base\Application\BaseApplicationAbstract;
use Coral\Base\BaseLog;
use models\Table\TableSystemProperty;
use Zend\Db\Adapter\Adapter;
use Zend\Config\Reader\Ini;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Json\Json;

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
     * メール環境
     */
    public $mail;

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

$this->logger->info('mailonreceiptconfirm.php start');

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

            // 入金完了メール送信処理
            $this->_sendReceiptConfirmMail();

$this->logger->info('mailonreceiptconfirm.php end');

            $exitCode = 0; // 正常終了
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
     * 入金完了メール送信処理
     */
    protected function _sendReceiptConfirmMail()
    {
        // ユーザーID(バッチユーザー)の取得
        $obj = new \models\Table\TableUser($this->dbAdapter);
        $userId = $obj->getUserId( 99, 1 );

//        $mdlsp = new TableSystemProperty($this->dbAdapter);
//        $days = intval($mdlsp->getValue('[DEFAULT]', 'systeminfo', 'CreditTransferConfirmDays'));
//        $days = -1 * $days;

        // 入金確認メール送信対象を取得し、件数分処理を行う。
        $ri = $this->dbAdapter->query(" SELECT ReceiptSeq FROM T_ReceiptControl rc INNER JOIN T_Order o ON (o.OrderSeq = rc.OrderSeq) INNER JOIN T_ClaimControl c ON ( o.OrderSeq = c.OrderSeq ) WHERE (rc.MailFlg = 0 AND rc.MailRetryCount < 5) AND rc.ValidFlg = 1 AND o.DataStatus = 91 AND o.CloseReason = 1 AND c.F_CreditTransferDate IS NOT NULL AND rc.ReceiptClass = 13 ")->execute(null);
        if (!($ri->count() > 0)) { return; }
        foreach($ri as $row) {

            try {
                // 入金確認メール送信
                $mail = new \Coral\Coral\Mail\CoralMail($this->dbAdapter, $this->mail['smtp']);
                $mail->SendCreditTransferConfirmMail($row['ReceiptSeq'], $userId);

                // メール送信に成功した場合のみ、送信フラグを更新する
                $this->dbAdapter->query(" UPDATE T_ReceiptControl SET MailFlg = 1 WHERE ReceiptSeq = :ReceiptSeq "
                    )->execute(array(':ReceiptSeq' => $row['ReceiptSeq']));
            }
            catch(\Exception $e) {
                // 入金管理.送信ﾘﾄﾗｲ回数のｲﾝｸﾘﾒﾝﾄ
                $this->dbAdapter->query(" UPDATE T_ReceiptControl SET MailRetryCount = MailRetryCount + 1 WHERE ReceiptSeq = :ReceiptSeq "
                    )->execute(array(':ReceiptSeq' => $row['ReceiptSeq']));
            }
        }
    }
}

Application::getInstance()->run();
