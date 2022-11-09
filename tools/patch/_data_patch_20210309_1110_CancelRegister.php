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
$this->logger->info('_data_patch_20210225_1130_CancelRegister.php start');

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
'AK50971903',
'AK50992921',
'AK50993007',
'AK51133332',
'AK51159086',
'AK51407477',
'AK51407542',
'AK51439266',
'AK51467399',
'AK51467413',
'AK51467636',
'AK51001005',
'AK51435025',
'AK51064844',
'AK51022024',
'AK51072832',
'AK51491973',
'AK49887326',
'AK49559771',
'AK51068288',
'AK51009717',
'AK51009802',
'AK51009828',
'AK51009836',
'AK51009870',
'AK51032765',
'AK51032770',
'AK51032783',
'AK51032800',
'AK51032851',
'AK51032854',
'AK51032908',
'AK51032917',
'AK51070433',
'AK51070459',
'AK51070485',
'AK51070490',
'AK51070534',
'AK51070540',
'AK51070546',
'AK51123407',
'AK51123465',
'AK51123473',
'AK51123527',
'AK51123577',
'AK51123611',
'AK51123636',
'AK51123715',
'AK51123849',
'AK51151242',
'AK51151256',
'AK51151335',
'AK51151486',
'AK51202629',
'AK51202691',
'AK51202728',
'AK51395036',
'AK50554294',
'AK50729758',
'AK51047899',
'AK49771812',
'AB51036523',
'AK46043795',
'AK50936524',
'AK50971321',
'AK51012563',
'AK51013013',
'AK51119074',
'AK51128293',
'AK51140817',
'AK50845250',
'AK51356246',
'AK51187685',
'AK50840421',
'AK50960835',
'AK50960862',
'AK51010890',
'AK51152645',
'AK51152656',
'AK51182529',
'AK51460734',
'AK51460757',
'AK51013016',
'AK51020860',
'AK51101979',
'AK51101986',
'AK51436847',
'AK51436852',
'AK51125390',
'AK51154083',
'AK51154085',
'AK51182442',
'AK51323096',
'AK51323099',
'AK51357012',
'AK51396321',
'AK51435992',
'AK51436002',
'AK51436008',
'AK51462369',
'AK51462371',
'AK51462379',
'AK51031050',
'AK50205406',
'AK51328368',
'AK51328392',
'AK51328425',
'AK51328541',
'AK50919113',
'AK51092641',
'AK51141221',
'AK51141222',
'AK50270526',
'AK50600784',
'AK50600785',
'AK50631305',
'AK50684305',
'AK50684306',
'AK50700058',
'AK50826698',
'AK50826699',
'AK50873871',
'AK50873872',
'AK50873877',
'AK50888515',
'AK50945507',
'AK51027324',
'AK51091262',
'AK51091274',
'AK51197558',
'AK51254397',
'AK51254398',
'AK51254400',
'AK51254401',
'AK51254403',
'AK50210373',
'AK50588581',
'AB50457196',
'AB50457203',
'AB50457246',
'AB50457266',
'AB50457415',
'AB50457812',
'AB50457942',
'AB50458039',
'AB50458190',
'AB50458193',
'AB50458304',
'AB50458385',
'AB50458441',
'AB50458625',
'AB50458667',
'AB50458801',
'AB50458843',
'AB50459051',
'AB50459417',
'AB50459423',
'AB50459476',
'AB50459551',
'AB50459924',
'AB50459998',
'AB50460041',
'AB50460082',
'AB50460138',
'AB50460415',
'AB50460460',
'AB50460754',
'AB50460811',
'AB50460886',
'AB50461247',
'AB50461262',
'AB50461389',
'AB50461450',
'AB50461622',
'AB50461649',
'AB50461974',
'AB50461978',
'AB50462047',
'AB50462098',
'AB50462180',
'AB50462204',
'AB50462663',
'AB50463229',
'AB51126538',
'AK51435548',
'AK49879721',
'AK50525960',
'AK50575123',
'AK50700266',
'AK50951543',
'AK50951545',
'AK50951547',
'AK50951555',
'AK50951559',
'AK50951561',
'AK50951564',
'AK50951565',
'AK50951571',
'AK50951578',
'AK50951579',
'AK50951581',
'AK50951582',
'AK50978688',
'AK50978702',
'AK51004607',
'AK51004608',
'AK51004609',
'AK51004611',
'AK51004613',
'AK51027247',
'AK51027255',
'AK51027308',
'AK51027316',
'AK51027318',
'AK51027331',
'AK51027338',
'AK51027348',
'AK51027384',
'AK51027432',
'AK51027436',
'AK51027454',
'AK51027457',
'AK51027462',
'AK51027465',
'AK51027475',
'AK51027498',
'AK51027500',
'AK51027501',
'AK51027521',
'AK51027527',
'AK51042862',
'AK51042868',
'AK51042875',
'AK51042877',
'AK51042888',
'AK51042898',
'AK51042916',
'AK51042918',
'AK51042955',
'AK51063323',
'AK51063324',
'AK51063325',
'AK51063328',
'AK51063329',
'AK51063331',
'AK51063332',
'AK51063333',
'AK51063335',
'AK51063336',
'AK51063337',
'AK51063338',
'AK51063339',
'AK51063340',
'AK51063341',
'AK51063343',
'AK51063344',
'AK51063345',
'AK51063349',
'AK51063350',
'AK51063351',
'AK51063354',
'AK51090153',
'AK51090162',
'AK51090178',
'AK51090180',
'AK51090189',
'AK51090191',
'AK51090193',
'AK51090194',
'AK51090196',
'AK51090207',
'AK51090212',
'AK51090234',
'AK51090243',
'AK51090250',
'AK51090253',
'AK51109522',
'AK51109524',
'AK51109531',
'AK51109534',
'AK51109556',
'AK51109557',
'AK51109564',
'AK51109588',
'AK51109603',
'AK51109606',
'AK51109607',
'AK51109631',
'AK51140760',
'AK51140789',
'AK51140792',
'AK51140834',
'AK51168283',
'AK51168287',
'AK51168311',
'AK51168333',
'AK51168382',
'AK51168385',
'AK51168388',
'AK51168390',
'AK51196904',
'AK51196929',
'AK51196961',
'AK51196978',
'AK51196983',
'AK51196985',
'AK51197013',
'AK51197022',
'AK51197026',
'AK51197038',
'AK51197053',
'AK51197070',
'AK51197083',
'AK51197084',
'AK51197101',
'AK51197102',
'AK51197107',
'AK51197117',
'AK51197122',
'AK51197165',
'AK51197190',
'AK51250031',
'AK51250120',
'AK51250147',
'AK51250156',
'AK51250196',
'AK51250236',
'AK51250268',
'AK51250270',
'AK51250346',
'AK51309152',
'AK51309173',
'AK51309176',
'AK51309205',
'AK51309209',
'AK51309261',
'AK51309267',
'AK51309273',
'AK51309288',
'AK51309291',
'AK51309311',
'AK51340703',
'AK51340704',
'AK51340732',
'AK51340754',
'AK51340760',
'AK51340764',
'AK51340768',
'AK51340771',
'AK51340794',
'AK51340810',
'AK51340827',
'AK51341164',
'AK51341167',
'AK51341179',
'AK51341188',
'AK51341189',
'AK51341196',
'AK51341206',
'AK51341213',
'AK51341215',
'AK51341222',
'AK51341229',
'AK51341240',
'AK51341242',
'AK51341249',
'AK51341260',
'AK51341262',
'AK51341266',
'AK51341269',
'AK51371801',
'AK51371815',
'AK51371858',
'AK51371888',
'AK51371895',
'AK51371896',
'AK51371910',
'AK51371922',
'AK51371924',
'AK51371943',
'AK51371945',
'AK51371954',
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
$this->logger->info('_data_patch_20210225_1130_CancelRegister.php end');

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

