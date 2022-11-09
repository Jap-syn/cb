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
$this->logger->info('_data_patch_20160722_1800_CancelRegister.php start');

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
                    'SE25742312',
                    'SE25744009',
                    'SE25744034',
                    'SE25744046',
                    'SE25744057',
                    'SE25744248',
                    'SE25744253',
                    'SE25744092',
                    'SE25744098',
                    'SE25744131',
                    'SE25744141',
                    'SE25744689',
                    'SE25744711',
                    'SE25744729',
                    'SE25744741',
                    'SE25743290',
                    'SE25743307',
                    'SE25745390',
                    'SE25745393',
                    'SE25745395',
                    'SE25745398',
                    'SE25745404',
                    'SE25743418',
                    'SE25743429',
                    'SE25743443',
                    'SE25744966',
                    'SE25742310',
                    'SE25742325',
                    'SE25742337',
                    'SE25742440',
                    'SE25742517',
                    'SE25742538',
                    'SE25742694',
                    'SE25742735',
                    'SE25743952',
                    'SE25743961',
                    'SE25743972',
                    'SE25743987',
                    'SE25744010',
                    'SE25744032',
                    'SE25744038',
                    'SE25744676',
                    'SE25744728',
                    'SE25744748',
                    'SE25744840',
                    'SE25744853',
                    'SE25744900',
                    'SE25744941',
                    'SE25744959',
                    'SE25744973',
                    'SE25744981',
                    'SE25744996',
                    'SE25744999',
                    'SE25745019',
                    'SE25745025',
                    'SE25745038',
                    'SE25745045',
                    'SE25745050',
                    'SE25745091',
                    'SE25745116',
                    'SE25745077',
                    'SE25745093',
                    'SE25744043',
                    'SE25742199',
                    'SE25742200',
                    'SE25742202',
                    'SE25742203',
                    'SE25742213',
                    'SE25742350',
                    'SE25742363',
                    'SE25742368',
                    'SE25742576',
                    'SE25742579',
                    'SE25742771',
                    'SE25743181',
                    'SE25743205',
                    'SE25743218',
                    'SE25743220',
                    'SE25743347',
                    'SE25743372',
                    'SE25743246',
                    'SE25743263',
                    'SE25743273',
                    'SE25743483',
                    'SE25743496',
                    'SE25743501',
                    'SE25743522',
                    'SE25743540',
                    'SE25743590',
                    'SE25743599',
                    'SE25743630',
                    'SE25742223',
                    'SE25742225',
                    'SE25744648',
                    'SE25744654',
                    'SE25744674',
                    'SE25744725',
                    'SE25744735',
                    'SE25744740',
                    'SE25744742',
                    'SE25742596',
                    'SE25742603',
                    'SE25742629',
                    'SE25742630',
                    'SE25742634',
                    'SE25742635',
                    'SE25742659',
                    'SE25742667',
                    'SE25742669',
                    'SE25742684',
                    'SE25742690',
                    'SE25742747',
                    'SE25742749',
                    'SE25742751',
                    'SE25742775',
                    'SE25742776',
                    'SE25742925',
                    'SE25743188',
                    'SE25743235',
                    'SE25743245',
                    'SE25743261',
                    'SE25743278',
                    'SE25743294',
                    'SE25743324',
                    'SE25743335',
                    'SE25743360',
                    'SE25743368',
                    'SE25743370',
                    'SE25743373',
                    'SE25743382',
                    'SE25743397',
                    'SE25743402',
                    'SE25743449',
                    'SE25743464',
                    'SE25743465',
                    'SE25743475',
                    'SE25743500',
                    'SE25743539',
                    'SE25743552',
                    'SE25743564',
                    'SE25743576',
                    'SE25743805',
                    'SE25743823',
                    'SE25744062',
                    'SE25744082',
                    'SE25744086',
                    'SE25744088',
                    'SE25742176',
                    'SE25742186',
                    'SE25742193',
                    'SE25742205',
                    'SE25742315',
                    'SE25743669',
                    'SE25743676',
                    'SE25743686',
                    'SE25743709',
                    'SE25742369',
                    'SE25742372',
                    'SE25742377',
                    'SE25742381',
                    'SE25742436',
                    'SE25742442',
                    'SE25742449',
                    'SE25742456',
                    'SE25742498',
                    'SE25743876',
                    'SE25743886',
                    'SE25745171',
                    'SE25745174',
                    'SE25742500',
                    'SE25742511',
                    'SE25742523',
                    'SE25742527',
                    'SE25742623',
                    'SE25742625',
                    'SE25744177',
                    'SE25744194',
                    'SE25744206',
                    'SE25744683',
                    'SE25744686',
                    'SE25744707',
                    'SE25744715',
                    'SE25744744',
                    'SE25745022',
                    'SE25745043',
                    'SE25745072',
                    'SE25745090',
                    'SE25745118',
                    'SE25743898',
                    'SE25743906',
                    'SE25743917',
                    'SE25743985',
                    'SE25743997',
                    'SE25744003',
                    'SE25744072',
                    'SE25742292',
                    'SE25742313',
                    'SE25742314',
                    'SE25742320',
                    'SE25742324',
                    'SE25742328',
                    'SE25742330',
                    'SE25742336',
                    'SE25742346',
                    'SE25742354',
                    'SE25742357',
                    'SE25742366',
                    'SE25742374',
                    'SE25742380',
                    'SE25742397',
                    'SE25742400',
                    'SE25742431',
                    'SE25742522',
                    'SE25742528',
                    'SE25742547',
                    'SE25742582',
                    'SE25742627',
                    'SE25742660',
                    'SE25743717',
                    'SE25743729',
                    'SE25743941',
                    'SE25743999',
                    'SE25744031',
                    'SE25744091',
                    'SE25744175',
                    'SE25745035',
                    'SE25745048',
                    'SE25745102',
                    'SE25742178',
                    'SE25742189',
                    'SE25742196',
                    'SE25742204',
                    'SE25742218',
                    'SE25742229',
                    'SE25742241',
                    'SE25742243',
                    'SE25742257',
                    'SE25742777',
                    'SE25742778',
                    'SE25742779',
                    'SE25743157',
                    'SE25743207',
                    'SE25743219',
                    'SE25743227',
                    'SE25743234',
                    'SE25743241',
                    'SE25743257',
                    'SE25743343',
                    'SE25743349',
                    'SE25743351',
                    'SE25743369',
                    'SE25743386',
                    'SE25743489',
                    'SE25743495',
                    'SE25743594',
                    'SE25743615',
                    'SE25743661',
                    'SE25743667',
                    'SE25743670',
                    'SE25743695',
                    'SE25743749',
                    'SE25743783',
                    'SE25743844',
                    'SE25743857',
                    'SE25743867',
                    'SE25743884',
                    'SE25743947',
                    'SE25743951',
                    'SE25743954',
                    'SE25743970',
                    'SE25744070',
                    'SE25744093',
                    'SE25744163',
                    'SE25744176',
                    'SE25744187',
                    'SE25744197',
                    'SE25744367',
                    'SE25744374',
                    'SE25744379',
                    'SE25744407',
                    'SE25744461',
                    'SE25744481',
                    'SE25744495',
                    'SE25744535',
                    'SE25744542',
                    'SE25744548',
                    'SE25744580',
                    'SE25744582',
                    'SE25744589',
                    'SE25744598',
                    'SE25744687',
                    'SE25744716',
                    'SE25744726',
                    'SE25744774',
                    'SE25745181',
                    'SE25745385',
                    'SE25745392',
                    'SE25745394',
                    'SE25745429',
                    'SE25742560',
                    'SE25742568',
                    'SE25742577',
                    'SE25742598',
                    'SE25743693',
                    'SE25744745',
                    'SE25744751',
                    'SE25742564',
                    'SE25742574',
                    'SE25742331',
                    'SE25742334',
                    'SE25742348',
                    'SE25742383',
                    'SE25742392',
                    'SE25742409',
                    'SE25742414',
                    'SE25742429',
                    'SE25742432',
                    'SE25742441',
                    'SE25742446',
                    'SE25742457',
                    'SE25742493',
                    'SE25742535',
                    'SE25742543',
                    'SE25742556',
                    'SE25744500',
                    'SE25744519',
                    'SE25744522',
                    'SE25744528',
                    'SE25744538',
                    'SE25744540',
                    'SE25744543',
                    'SE25744563',
                    'SE25744572',
                    'SE25744581',
                    'SE25742680',
                    'SE25742688',
                    'SE25742692',
                    'SE25744881',
                    'SE25744885',
                    'SE25744898',
                    'SE25743848',
                    'SE25743873',
                    'SE25743888',
                    'SE25743897',
                    'SE25743937',
                    'SE25743948',
                    'SE25744084',
                    'SE25744123',
                    'SE25744135',
                    'SE25744139',
                    'SE25744147',
                    'SE25744322',
                    'SE25744325',
                    'SE25744368',
                    'SE25744380',
                    'SE25743498',
                    'SE25743365',
                    'SE25743371',
                    'SE25743395',
                    'SE25743405',
                    'SE25743408',
                    'SE25743412',
                    'SE25743468',
                    'SE25744516',
                    'SE25744518',
                    'SE25744523',
                    'SE25744531',
                    'SE25743960',
                    'SE25743986',
                    'SE25744004',
                    'SE25744737',
                    'SE25744739',
                    'SE25744750',
                    'SE25744772',
                    'SE25744781',
                    'SE25744782',
                    'SE25744841',
                    'SE25744848',
                    'SE25744955',
                    'SE25744980',
                    'SE25745042',
                    'SE25745067',
                    'SE25744653',
                    'SE25744673',
                    'SE25744678',
                    'SE25744770',
                    'SE25744775',
                    'SE25742599',
                    'SE25742601',
                    'SE25742608',
                    'SE25742636',
                    'SE25743732',
                    'SE25743738',
                    'SE25743896',
                    'SE25743904',
                    'SE25743918',
                    'SE25744029',
                    'SE25744036',
                    'SE25744048',
                    'SE25744058',
                    'SE25744061',
                    'SE25744134',
                    'SE25744409',
                    'SE25744450',
                    'SE25744499',
                    'SE25744520',
                    'SE25744526',
                    'SE25744533',
                    'SE25744546',
                    'SE25744597',
                    'SE25744646',
                    'SE25744688',
                    'SE25744704',
                    'SE25744708',
                    'SE25744713',
                    'SE25744809',
                    'SE25744813',
                    'SE25744816',
            );

            // キャンセル理由の定義
            $reason = '2016/7/21 障害発生によるキャンセル';
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
$this->logger->info('_data_patch_20160722_1800_CancelRegister.php end');

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
