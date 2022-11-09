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
$this->logger->info('_data_patch_20200811_1400_CancelRegister.php start');

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
'AK44467385',
'AK44731795',
'AK44764191',
'AK45424636',
'AK45772797',
'AK45799571',
'AK45802335',
'AK45896088',
'AK45911798',
'AK45939539',
'AK45975810',
'AK45527153',
'AK45936204',
'AK45493459',
'AK45526744',
'AK45527162',
'AK45549165',
'AK45550601',
'AK45552435',
'AK45703962',
'AK45752791',
'AK45794136',
'AK45824346',
'AK45883815',
'AK45884029',
'AK45890685',
'AK45890690',
'AK45901009',
'AK45924659',
'AK45941521',
'AK45975708',
'AK46140457',
'AK44648666',
'AK46008263',
'AK45362489',
'AK45381113',
'AK45381220',
'AK45381238',
'AK45446499',
'AK45446502',
'AK45446506',
'AK45461479',
'AK45462087',
'AK45468344',
'AK45468384',
'AK45468402',
'AK45476112',
'AK45485319',
'AK45493531',
'AK45511550',
'AK45511556',
'AK45575321',
'AK45587467',
'AK45861139',
'AK45861187',
'AK45868211',
'AK45870167',
'AK45899105',
'AK45913490',
'AK45914180',
'AK45933480',
'AK45947686',
'AK45947719',
'AK45947733',
'AK45966289',
'AK45992076',
'AK46000040',
'AK46011305',
'AK46015738',
'AK46020416',
'AK46032295',
'AK46065020',
'AK46084433',
'AK46086316',
'AK46107068',
'AK45273522',
'AK43183333',
'AK44900338',
'AK45525931',
'AK45543656',
'AK45254140',
'AK45605842',
'AK45719789',
'AK45955975',
'AK45985673',
'AK46201645',
'AK45578986',
'AK45587946',
'AK45653936',
'AK45755196',
'AK46090236',
'AK46110881',
'AK45605355',
'AK45605468',
'AK45605492',
'AK45605606',
'AK45960222',
'AK45378258',
'AK45396438',
'AK45752872',
'AK40273777',
'AK45324994',
'AK45585489',
'AK46131595',
'AK45348178',
'AK45811285',
'AK45502989',
'AB45658777',
'AK45505361',
'AK45543614',
'AK45820924',
'AK45824637',
'AK45938146',
'AK46046717',
'AK46050790',
'AK46052171',
'AK46052385',
'AK46053811',
'AK46055753',
'AK46056052',
'AK45453963',
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
                    $logic->applies($oseq, $reason, $reasonCode, 1, false, $userId);
                    $this->logger->info('<CancelRegister> [' . $orderId . '] Complete!! ');
                } catch(OrderCancelException $oce) {
                    $this->logger->warn('<CancelRegister> [' . $orderId . '] Order Is Not Cancel Message = ' . $oce->getMessage());
                    $this->logger->warn('<CancelRegister> [' . $orderId . '] ' . $oce->getTraceAsString());
                }

            }

            // $this->dbAdapter->getDriver()->getConnection()->rollback();
            $this->dbAdapter->getDriver()->getConnection()->commit();

            $exitCode = 0; // 正常終了
$this->logger->info('_data_patch_20200811_1400_CancelRegister.php end');

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
