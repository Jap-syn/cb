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
use Coral\Base\BaseLog;
use Zend\Db\Adapter\Adapter;
use Zend\Config\Reader\Ini;
use models\Table\TableUser;
use models\Logic\LogicCancel;
use models\Logic\OrderCancelException;


/**
 * アプリケーションクラスです。
 *
 */
class Application extends BaseApplicationAbstract {
    protected $_application_id = 'tools-CancelRegister-batch';

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
     * アプリケーションを実行します。
     *
     * @access public
     */
    public function run() {
        $exitCode = 1;

        try {

            // 実行確認
            echo "Run the Cancel batch. Is it OK?(Y/N)";
            $yn = trim(fgets(STDIN));
            if (strtoupper($yn) != 'Y') {
                echo "It has stopped the execution. ";
                exit(0);
            }

            // iniファイルから設定を取得
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
$this->logger->info('_data_patch_20201027_1020_CancelRegister.php start');

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

            // ユーザーID取得
            $mdlu = new TableUser($this->dbAdapter);
            $userId = $mdlu->getUserId(TableUser::USERCLASS_SYSTEM, TableUser::SEQ_BATCH_USER);

            $this->dbAdapter->getDriver()->getConnection()->beginTransaction();


            // ------------------------------------------------------------------------->
            // 対象の定義
            $target = array(
'AK49156452',
'AK47851374',
'AK45312695',
'AK46689311',
'AK46753343',
'AK45693545',
'AK45577104',
'AK48820433',
'AK48618103',
'AK45425649',
'AK45425602',
'AK46437603',
'AK46437599',
'AK46641613',
'AK47058656',
'AK48127250',
'AK46268277',
'AK48404672',
'AK45747445',
'AK45534576',
'AK47646833',
'AK43325628',
'AK46253445',
'AK45393559',
'AK46281105',
'AK43932955',
'AK48820583',
'AK47834594',
'AK44922925',
'AK46315045',
'AK48716671',
'AK48271396',
'AK45895217',
'AK44756099',
'AK48060189',
'AK47013439',
'AK46985292',
'AK45935066',
'AK45910600',
'AK48121160',
'AK48101421',
'AK48632305',
'AK48079426',
'AK48716612',
'AK48632307',
'AK46953475',
'AK46314998',
'AK46315146',
'AK46953716',
'AK46281275',
'AK46253393',
'AK45841669',
'AK48197021',
'AK48806259',
'AK45799379',
'AK46147425',
'AK46362128',
'AK45890910',
'AK47799131',
'AK47194990',
'AK45211936',
'AK48120464',
'AK48203078',
'AK46281350',
'AK45890869',
'AK46219040',
'AK44756090',
'AK45934341',
'AK48118418',
'AK46253625',
'AK46346540',
'AK46953354',
'AK46100361',
'AK46672144',
'AK46057045',
'AK45473042',
'AK46281374',
'AK46468494',
'AK45161806',
'AK46363229',
'AK47235813',
'AK43325638',
'AK45975832',
'AK46057011',
'AK44740909',
'AK46268211',
'AK45873947',
'AK48381159',
'AK45947553',
'AK46297679',
'AK45914660',
'AK47846659',
'AK46957249',
'AK47290273',
'AK45913294',
'AK45529869',
'AK48745142',
'AK48745146',
'AK48658338',
'AK45885686',
'AK46759033',
'AK48437783',
'AK49155891',
'AK45914552',
'AK48142413',
'AK46468490',
'AK47851370',
'AK47208401',
'AK48550996',
'AK48088863',
'AK47451042',
'AK45213429',
'AK46753189',
'AK47264906',
'AK47902074',
'AK45747442',
'AK46403661',
'AK46001427',
'AK45329210',
'AK46957282',
'AK46953149',
'AK46725692',
'AK46056885',
'AK45766632',
'AK45456986',
'AK47912238',
'AK46333509',
'AK47058752',
'AK47917874',
'AK46753186',
'AK46045822',
'AK45890536',
'AK46191691',
'AK47792098',
'AK45851493',
'AK45947648',
'AK46437584',
'AK48169664',
'AK43991919',
'AK42769143',
'AK48096800',
'AK45947580',
'AK49156063',
'AK49002888',
            );

            // キャンセル理由の定義
            $reason = '';
            $reasonCode = 8;
            // <-------------------------------------------------------------------------

            $logic = new LogicCancel($this->dbAdapter);

            // 対象データ分ループ
            foreach ($target as $orderId) {
                // $this->logger->info('[' . $orderId . '] Start ');

                // 注文SEQを特定する
                $sql = ' SELECT * FROM T_Order WHERE OrderId = :OrderId ';
                $prm = array(
                    ':OrderId' => $orderId,
                );

                $row = $this->dbAdapter->query($sql)->execute($prm)->current();

                if (!$row) {
                    // 特定できない場合はアラート出力⇒次の行へ
                    $this->logger->warn('<CancelRegister> [' . $orderId . '] OrderId Is Not Found!!');
                    continue;
                }

                // 注文SEQ特定
                $oseq = $row['OrderSeq'];

                // キャンセル申請処理を行う
                try {
                    $logic->applies($oseq, $reason, $reasonCode, 0, false, $userId);
                    $this->logger->info('<CancelRegister> [' . $orderId . '] Complete!! ');
                } catch(OrderCancelException $oce) {
                    $this->logger->warn('<CancelRegister> [' . $orderId . '] Order Is Not Cancel Message = ' . $oce->getMessage());
                    $this->logger->warn('<CancelRegister> [' . $orderId . '] ' . $oce->getTraceAsString());
                }

            }

            // $this->dbAdapter->getDriver()->getConnection()->rollback();
            $this->dbAdapter->getDriver()->getConnection()->commit();

            $exitCode = 0; // 正常終了
$this->logger->info('_data_patch_20201027_1020_CancelRegister.php end');

        } catch( \Exception $e ) {
            try{
                $this->dbAdapter->getDriver()->getConnection()->rollback();
            } catch ( \Exception $err) { }

            // エラーログを出力
            if ( isset($this->logger) ) {
$this->logger->err('<CancelRegister> ' . $e->getMessage());
$this->logger->err('<CancelRegister> ' . $e->getTraceAsString());
            }
        }

        // 終了コードを指定して処理終了
        exit($exitCode);

    }
}

Application::getInstance()->run();
