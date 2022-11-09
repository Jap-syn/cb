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
$this->logger->info('_data_patch_20200812_1045_CancelRegister.php start');

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
'AK45175445',
'AK45350487',
'AK45505041',
'AK45528660',
'AK45671806',
'AK45761833',
'AK45878812',
'AK45880615',
'AK45901691',
'AK44917164',
'AK45993356',
'AK45842489',
'AK45891795',
'AK45842491',
'AK45842495',
'AK45842503',
'AK45842517',
'AK45842552',
'AK45842562',
'AK45842575',
'AK45869975',
'AK45869990',
'AK45870049',
'AK45870065',
'AK45870088',
'AK45870100',
'AK45891680',
'AK45891706',
'AK45891746',
'AK45891751',
'AK45891776',
'AK45891804',
'AK45891817',
'AK45891822',
'AK45914453',
'AK45914463',
'AK45914481',
'AK45914492',
'AK45914506',
'AK45934819',
'AK45934834',
'AK45934920',
'AK45934922',
'AK45934926',
'AK45934927',
'AK45934936',
'AK45947486',
'AK45947525',
'AK45947619',
'AK45947677',
'AK45947704',
'AK45947710',
'AK45947718',
'AK45947757',
'AK45947778',
'AK45947807',
'AK45947813',
'AK45947821',
'AK45947824',
'AK45947840',
'AK46117469',
'AK46117472',
'AK46117474',
'AK46117477',
'AK46117479',
'AK46117482',
'AK46117491',
'AK46117494',
'AK46117495',
'AK46117498',
'AK46117501',
'AK45372232',
'AK45523376',
'AK45635146',
'AK46019772',
'AK46202659',
'AK45562407',
'AK45562433',
'AK45604885',
'AK45759917',
'AK45899462',
'AK45899658',
'AK46129170',
'AK46202667',
'AK45836323',
'AK45836373',
'AK46094310',
'AK45354460',
'AK45482339',
'AK45482391',
'AK45482399',
'AK45505800',
'AK45614019',
'AK45614151',
'AK45614165',
'AK45641850',
'AK45690787',
'AK45813864',
'AK45814045',
'AK45814062',
'AK45858921',
'AK45883000',
'AK45960796',
'AK45960866',
'AK46038314',
'AK46131972',
'AK46132031',
'AB45674360',
'AB45678760',
'AB45674370',
'AB45544390',
'AB45677201',
'AB45679011',
'AB45674911',
'AB45674951',
'AB45678202',
'AB45678472',
'AB45675563',
'AB45674993',
'AB45676304',
'AB45678124',
'AB45676524',
'AB45677564',
'AB45678274',
'AB45679525',
'AB45679155',
'AB45677065',
'AB45677265',
'AB45679506',
'AB45674626',
'AB45680636',
'AB45677756',
'AB45680766',
'AB45677766',
'AB45679086',
'AB45676207',
'AB45674377',
'AB45679018',
'AB45674918',
'AB45676048',
'AB45676158',
'AB45676468',
'AB45675839',
'AB45674449',
'AB45674999',
'AK46180507',
'AK45677939',
'AK45730671',
'AK45730696',
'AK44304788',
'AK44744958',
'AK44990858',
'AK45200666',
'AK45426073',
'AK45446970',
'AK45493945',
'AK45493950',
'AK45556119',
'AK45575792',
'AK45575794',
'AK45575795',
'AK45773347',
'AK45773349',
'AK45870791',
'AK45870793',
'AK45915336',
'AK45976280',
'AK46057631',
'AK45425700',
'AK45446559',
'AK45446560',
'AK45446563',
'AK45458142',
'AK45587551',
'AK45747305',
'AK46026684',
'AK45381393',
'AK45406464',
'AK45446562',
'AK45458141',
'AK45468497',
'AK45468499',
'AK45493622',
'AK45511685',
'AK45555589',
'AK45575359',
'AK45597773',
'AK45621313',
'AK45621323',
'AK45621333',
'AK45652356',
'AK45652357',
'AK45799509',
'AK45824537',
'AK45892048',
'AK45892049',
'AK45892055',
'AK45914667',
'AK45914668',
'AK45935183',
'AK45975922',
'AK45975923',
'AK45587923',
'AK45587939',
'AK45590644',
'AK45395039',
'AK45395097',
'AK45395620',
'AK46078773',
'AK45550277',
'AK45734021',
'AK45734109',
'AK45734175',
'AK45761646',
'AK45835923',
'AK45886130',
'AK45886135',
'AK46046987',
'AK46078629',
'AK46078795',
'AK46078848',
'AK46139835',
'AK46139900',
'AK44699047',
'AK46235851',
'AK46781946',
'AK46622721',
'AK46622718',
'AK46622727',
'AK46622735',
'AK46622763',
'AK46622771',
'AK46622776',
'AK46622807',
'AK46444689',
'AK46444693',
'AK46368741',
'AK46368743',
'AK46368751',
'AK46368757',
'AK46368777',
'AK46368807',
'AK46368814',
'AK46368823',
'AK46368838',
'AK46368848',
'AK46368852',
'AK46368874',
'AK46368878',
'AK46368890',
'AK46368921',
'AK46259701',
'AK46229348',
'AK46229382',
'AK46229487',
'AK46832421',
'AK46832434',
'AK46781957',
'AK46782064',
'AK46759766',
'AK46706346',
'AK46677822',
'AK46677838',
'AK46480904',
'AK46480909',
'AK46444685',
'AK46368754',
'AK46368833',
'AK46229010',
'AK46234577',
'AK46235894',
'AK46229000',
'AK46229007',
'AK46229028',
'AK46229037',
'AK46229043',
'AK46229092',
'AK46229113',
'AK46229121',
'AK46229125',
'AK46229154',
'AK46229156',
'AK46229160',
'AK46229193',
'AK46229198',
'AK46229223',
'AK46229251',
'AK46229280',
'AK46229291',
'AK46229355',
'AK46229447',
'AK46229614',
'AK46229632',
'AK46229636',
'AK46229673',
'AK46234542',
'AK46234570',
'AK46234581',
'AK46235751',
'AK46235760',
'AK46235822',
'AK46235836',
'AK46235838',
'AK46235841',
'AK46235847',
'AK46235857',
'AK46235860',
'AK46235870',
'AK46235884',
'AK46235899',
'AK46235913',
'AK46235925',
'AK46235931',
'AK46235935',
'AK46235937',
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
$this->logger->info('_data_patch_20200812_1045_CancelRegister.php end');

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
