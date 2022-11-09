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
$this->logger->info('_data_patch_20200310_1600_CancelRegister.php start');

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
'AK44394100',
'AK44136400',
'AK44216600',
'AK44672700',
'AK44035800',
'AK44036010',
'AK43873210',
'AK44350610',
'AK44497710',
'AK44321910',
'AK44343910',
'AK44368020',
'AK44590120',
'AK44330320',
'AK44159320',
'AK44289520',
'AK44672720',
'AK44451130',
'AK43877330',
'AK44200730',
'AK44345730',
'AK44316930',
'AK43930040',
'AK44368040',
'AK44330340',
'AK44113640',
'AK43874640',
'AK44345640',
'AK44672740',
'AK44035840',
'AK44033050',
'AK44822150',
'AK44159350',
'AK44216550',
'AK44287650',
'AK44013750',
'AK44128750',
'AK44481850',
'AK44686850',
'AK43986950',
'AK44159260',
'AK44391360',
'AK44779360',
'AK44039660',
'AK44672760',
'AK44113760',
'AK44337760',
'AK44069760',
'AK44063070',
'AK44437170',
'AK44159270',
'AK44814670',
'AK44590770',
'AK44169770',
'AK44562870',
'AK44451280',
'AK44739280',
'AK44330380',
'AK44090480',
'AK44035880',
'AK44822090',
'AK44394090',
'AK44595090',
'AK44249090',
'AK44242190',
'AK44113290',
'AK44330390',
'AK44814690',
'AK44345690',
'AK44187790',
'AK43883890',
'AK44729990',
'AK44330301',
'AK44663601',
'AK44822011',
'AK44595011',
'AK44249111',
'AK44640211',
'AK44095211',
'AK44516211',
'AK44226411',
'AK44150711',
'AK44672711',
'AK44345711',
'AK44730021',
'AK43887121',
'AK43875421',
'AK44216621',
'AK44594721',
'AK44021821',
'AK44368031',
'AK44035531',
'AK44071631',
'AK44035731',
'AK44308831',
'AK44167931',
'AK44570241',
'AK44035341',
'AK44672741',
'AK44527841',
'AK44729941',
'AK44822051',
'AK44836251',
'AK44289551',
'AK44237651',
'AK44289651',
'AK44672751',
'AK44037061',
'AK44451361',
'AK44289561',
'AK44672761',
'AK44169761',
'AK44394071',
'AK44351171',
'AK43996171',
'AK44451271',
'AK44216571',
'AK44267671',
'AK44672771',
'AK44521081',
'AK44394081',
'AK44124381',
'AK44159381',
'AK44345681',
'AK44821781',
'AK44136781',
'AK44729781',
'AK44163881',
'AK44729981',
'AK44226391',
'AK43889391',
'AK44363491',
'AK44216591',
'AK44289591',
'AK44035002',
'AK44822102',
'AK44437202',
'AK44094402',
'AK44035402',
'AK44029402',
'AK44289502',
'AK44672702',
'AK44345702',
'AK43920802',
'AK44368012',
'AK43936212',
'AK44477212',
'AK44330412',
'AK44451412',
'AK44090512',
'AK44323512',
'AK44289612',
'AK44035712',
'AK44672812',
'AK44038912',
'AK44128322',
'AK44090522',
'AK44094622',
'AK44200722',
'AK44729722',
'AK44034922',
'AK43922032',
'AK44645232',
'AK44595013',
'AK44216632',
'AK44073042',
'AK44159342',
'AK44392542',
'AK44487542',
'AK44216642',
'AK44562842',
'AK44686842',
'AK44776052',
'AK44325152',
'AK44535152',
'AK44451252',
'AK44082652',
'AK44672752',
'AK43947062',
'AK44330362',
'AK44071362',
'AK43977462',
'AK44035562',
'AK44216562',
'AK44345662',
'AK44169762',
'AK44050862',
'AK44628172',
'AK44451372',
'AK43905372',
'AK44035672',
'AK44467672',
'AK44672772',
'AK44345772',
'AK44686872',
'AK44822082',
'AK44417282',
'AK44159282',
'AK44451382',
'AK44381682',
'AK44633882',
'AK44035982',
'AK44369092',
'AK44111392',
'AK44013792',
'AK44298792',
'AK44562892',
'AK44590003',
'AK44822003',
'AK44249103',
'AK44159303',
'AK44330403',
'AK44161403',
'AK44216603',
'AK44035703',
'AK44355703',
'AK44562903',
'AK44330313',
'AK44216613',
'AK44672713',
'AK44309713',
'AK44562913',
'AK44368023',
'AK44678023',
'AK44437123',
'AK44640223',
'AK44035623',
'AK44035923',
'AK44035133',
'AK44159233',
'AK44389233',
'AK44159333',
'AK44570533',
'AK44319533',
'AK44035633',
'AK44200733',
'AK44672733',
'AK44748143',
'AK44451343',
'AK44522343',
'AK44042343',
'AK44216543',
'AK44350743',
'AK44672743',
'AK44035843',
'AK44243053',
'AK44822153',
'AK43945453',
'AK44146453',
'AK44216553',
'AK44216653',
'AK44672753',
'AK44035853',
'AK44730063',
'AK44035063',
'AK44036063',
'AK44035163',
'AK44451263',
'AK44159263',
'AK44200363',
'AK44173563',
'AK44035663',
'AK43881763',
'AK44035763',
'AK44154863',
'AK44035863',
'AK44256863',
'AK44035963',
'AK44822073',
'AK44113373',
'AK44381673',
'AK44355673',
'AK44249083',
'AK44437183',
'AK44216583',
'AK44035683',
'AK44672783',
'AK44821883',
'AK44729983',
'AK43882093',
'AK44035093',
'AK44124393',
'AK44159393',
'AK44216593',
'AK43951993',
'AK44034993',
'AK43928004',
'AK44174104',
'AK44640204',
'AK44451204',
'AK44057204',
'AK44241404',
'AK44672704',
'AK44035804',
'AK44595014',
'AK44368014',
'AK44059014',
'AK44226414',
'AK44273614',
'AK44345714',
'AK44542814',
'AK44262814',
'AK44672814',
'AK44719024',
'AK44330324',
'AK44159324',
'AK44672724',
'AK44035824',
'AK44729824',
'AK44343924',
'AK44036034',
'AK44137034',
'AK44354534',
'AK44289534',
'AK44216634',
'AK44035834',
'AK44686834',
'AK44595044',
'AK43998044',
'AK44263544',
'AK44289644',
'AK44403744',
'AK44821844',
'AK44190154',
'AK44035154',
'AK44159254',
'AK44216554',
'AK44692654',
'AK44216654',
'AK44672754',
'AK44492854',
'AK44074854',
'AK44686854',
'AK43920464',
'AK44345664',
'AK44237664',
'AK44672764',
'AK44562864',
'AK44473864',
'AK44653964',
'AK44316964',
'AK44437174',
'AK44159374',
'AK44216574',
'AK44672774',
'AK43993774',
'AK44488284',
'AK44330384',
'AK44024384',
'AK44652584',
'AK44482684',
'AK44345684',
'AK44562884',
'AK44035194',
'AK44162394',
'AK44090494',
'AK44381694',
'AK44035694',
'AK44497694',
'AK44473794',
'AK44785005',
'AK44368005',
'AK44035605',
'AK44289605',
'AK44821805',
'AK44262805',
'AK44729805',
'AK44035115',
'AK44257115',
'AK44129215',
'AK44159315',
'AK43931415',
'AK44686515',
'AK44329515',
'AK44672715',
'AK44729715',
'AK44368025',
'AK44216625',
'AK44701725',
'AK44139825',
'AK44653135',
'AK44437135',
'AK43920335',
'AK44216635',
'AK44035735',
'AK44019835',
'AK44035145',
'AK44159345',
'AK44216645',
'AK44672745',
'AK44035945',
'AK44730055',
'AK44595055',
'AK44330255',
'AK44829255',
'AK44451355',
'AK44483355',
'AK44216555',
'AK44237655',
'AK44821755',
'AK44315755',
'AK44729855',
'AK44595065',
'AK44155165',
'AK44236165',
'AK44197465',
'AK44289465',
'AK44216565',
'AK44035665',
'AK44355665',
'AK44289665',
'AK44035075',
'AK44369175',
'AK44062275',
'AK44381675',
'AK44035875',
'AK44473975',
'AK44034975',
'AK44394085',
'AK44595085',
'AK43936485',
'AK43890585',
'AK44845685',
'AK44035985',
'AK44249095',
'AK44226395',
'AK44614795',
'AK44064795',
'AK44169795',
'AK44562895',
'AK44122995',
'AK44330306',
'AK44330406',
'AK44035906',
'AK44036016',
'AK43638216',
'AK44401916',
'AK43927916',
'AK43864326',
'AK44757326',
'AK44090526',
'AK44200726',
'AK44493726',
'AK44814726',
'AK44237636',
'AK44672736',
'AK44497736',
'AK44822146',
'AK44090546',
'AK44216546',
'AK44035746',
'AK44126746',
'AK44618746',
'AK44241846',
'AK44027856',
'AK44342066',
'AK44677266',
'AK44216566',
'AK44672766',
'AK44034966',
'AK44187176',
'AK44216576',
'AK43976576',
'AK44388576',
'AK44729676',
'AK44672776',
'AK44686876',
'AK44027876',
'AK44295086',
'AK44672786',
'AK44254786',
'AK44035786',
'AK44345786',
'AK44035886',
'AK44686886',
'AK44594986',
'AK44729986',
'AK44394096',
'AK44437196',
'AK44216596',
'AK43976596',
'AK44821796',
'AK44730007',
'AK43883107',
'AK44177407',
'AK44289507',
'AK44740707',
'AK44814707',
'AK44035707',
'AK44345707',
'AK44672807',
'AK43966907',
'AK44595017',
'AK44124417',
'AK44342617',
'AK44672817',
'AK44368027',
'AK44330327',
'AK43963327',
'AK44335527',
'AK44035627',
'AK44821727',
'AK44715727',
'AK44035727',
'AK44729927',
'AK44159237',
'AK44267437',
'AK44389537',
'AK44200737',
'AK43942737',
'AK44351837',
'AK44821937',
'AK44035147',
'AK43986147',
'AK43888147',
'AK44451247',
'AK44038347',
'AK44642647',
'AK44035647',
'AK44355647',
'AK44216647',
'AK44262747',
'AK44434747',
'AK44345747',
'AK44035847',
'AK44291157',
'AK43929157',
'AK44026257',
'AK44330357',
'AK44262757',
'AK44437167',
'AK44345667',
'AK44672767',
'AK44053767',
'AK44035867',
'AK44195077',
'AK44051177',
'AK44709177',
'AK44330377',
'AK44315577',
'AK44730087',
'AK44672787',
'AK44035197',
'AK44164697',
'AK44665797',
'AK44035897',
'AK44451108',
'AK44437108',
'AK44095308',
'AK44330408',
'AK44226408',
'AK44035608',
'AK44216608',
'AK44672808',
'AK44035808',
'AK44562908',
'AK44730018',
'AK44175018',
'AK44368018',
'AK44394118',
'AK44330318',
'AK44289418',
'AK44035618',
'AK44672718',
'AK44542818',
'AK44232918',
'AK44590028',
'AK44595028',
'AK44394128',
'AK44387628',
'AK44036038',
'AK44035138',
'AK44751338',
'AK44090538',
'AK44315538',
'AK44216638',
'AK44262738',
'AK44113738',
'AK44035738',
'AK44345738',
'AK44354938',
'AK44485248',
'AK44159248',
'AK44330348',
'AK44005648',
'AK44672748',
'AK44562848',
'AK44034948',
'AK44328948',
'AK44338058',
'AK44237658',
'AK44672758',
'AK43883958',
'AK44451168',
'AK44035168',
'AK44751268',
'AK44330368',
'AK44159368',
'AK44035468',
'AK44677468',
'AK44035568',
'AK44216568',
'AK44267668',
'AK44262768',
'AK43927768',
'AK44289478',
'AK44672778',
'AK44345778',
'AK44354088',
'AK44002588',
'AK44035588',
'AK44216588',
'AK44319588',
'AK44262788',
'AK44821888',
'AK44326888',
'AK43927988',
'AK43882398',
'AK44216598',
'AK44035798',
'AK44368009',
'AK44394109',
'AK44640209',
'AK44330409',
'AK44124409',
'AK44672709',
'AK44672809',
'AK44035909',
'AK44437119',
'AK44594419',
'AK44216619',
'AK44672719',
'AK44345719',
'AK44448719',
'AK44035919',
'AK44027129',
'AK44437129',
'AK44366429',
'AK44090529',
'AK44216629',
'AK44821729',
'AK44043729',
'AK44053729',
'AK44345729',
'AK44821929',
'AK44159339',
'AK44289539',
'AK44355639',
'AK44057939',
'AK44159249',
'AK44139449',
'AK44216649',
'AK44672749',
'AK44363849',
'AK44729849',
'AK43929059',
'AK44631259',
'AK44216559',
'AK44636559',
'AK44035659',
'AK44672759',
'AK44562859',
'AK44074859',
'AK44686859',
'AK44729859',
'AK43971069',
'AK44035069',
'AK44315269',
'AK44124369',
'AK44289569',
'AK44492769',
'AK44169769',
'AK44027869',
'AK44729869',
'AK44729969',
'AK44243079',
'AK44757279',
'AK44677279',
'AK43872679',
'AK44345679',
'AK44617679',
'AK44672779',
'AK44697879',
'AK44482979',
'AK44784979',
'AK44729979',
'AK44159389',
'AK44216589',
'AK44381689',
'AK44468889',
'AK44181299',
'AK44226399',
'AK44359399',
'AK44355699',
'AK43965799',
'AK44562899',
'AK44165899',
'AK44729999',
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
$this->logger->info('_data_patch_20200310_1600_CancelRegister.php end');

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