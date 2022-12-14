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
$this->logger->info('_data_patch_20210114_1500_CancelRegister.php start');

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
'AK50177314',
'AK50177321',
'AK50177324',
'AK50177329',
'AK50177335',
'AK50177341',
'AK50205413',
'AK50205439',
'AK50205447',
'AK50205454',
'AK50232901',
'AK50232985',
'AK50320925',
'AK50320935',
'AK51007080',
'AK49307761',
'AK49391092',
'AK48238970',
'AK49400475',
'AK49400567',
'AK49400577',
'AK49400552',
'AK49430676',
'AK49595573',
'AK49783310',
'AK49783382',
'AK49783477',
'AK49810866',
'AK49389177',
'AK49803455',
'AK49721248',
'AK48927194',
'AK49177236',
'AK49436277',
'AK48960916',
'AK49177223',
'AK49560869',
'AK49560844',
'AK49566435',
'AK49567110',
'AK49352096',
'AK49811931',
'AK49784251',
'AK49713564',
'AK49555830',
'AB49538679',
'AB49539416',
'AK49728047',
'AK49368081',
'AK49568644',
'AK49568649',
'AK49568655',
'AK49691279',
'AK49691346',
'AK49367891',
'AK49367893',
'AK49389487',
'AK49389490',
'AK49423691',
'AK49446409',
'AK49471837',
'AK49471838',
'AK49565574',
'AK49565576',
'AK49632775',
'AK49632777',
'AK49674443',
'AK49674446',
'AK49674449',
'AK49689175',
'AK49689177',
'AK49689178',
'AK49689179',
'AK49689180',
'AK49689183',
'AK49689187',
'AK49714325',
'AK49714327',
'AK49714332',
'AK49714333',
'AK49714334',
'AK49714335',
'AK49802567',
'AK49471835',
'AK49446408',
'AK49674440',
'AK49459815',
'AK49661025',
'AK49368708',
'AK49363741',
'AK49519552',
'AK49323788',
'AK49323735',
'AK48716535',
'AK49407642',
'AK49704696',
'AK49704360',
'AK49378465',
'AK49338856',
'AK48716856',
'AK48716553',
'AK48551101',
'AK48551198',
'AK48551214',
'AK48551243',
'AK48551265',
'AK48551268',
'AK48551274',
'AK48551292',
'AK48551293',
'AK48551339',
'AK48551340',
'AK48716834',
'AK48716471',
'AK48716485',
'AK48716488',
'AK48716494',
'AK48716512',
'AK48716516',
'AK48716537',
'AK48716540',
'AK48716543',
'AK48716556',
'AK48716574',
'AK48716580',
'AK48716604',
'AK48716607',
'AK48716616',
'AK48716620',
'AK48716621',
'AK48716655',
'AK48716672',
'AK48716676',
'AK48716685',
'AK48716703',
'AK48716709',
'AK48716724',
'AK48716733',
'AK48716751',
'AK48716755',
'AK48716804',
'AK48716818',
'AK48716835',
'AK48716846',
'AK48716854',
'AK48716868',
'AK48716871',
'AK49300109',
'AK49300115',
'AK49300124',
'AK49300128',
'AK49300130',
'AK49300168',
'AK49300175',
'AK49300186',
'AK49300197',
'AK49300212',
'AK49300248',
'AK49323693',
'AK49323732',
'AK49323741',
'AK49323753',
'AK49323756',
'AK49323761',
'AK49323762',
'AK49323763',
'AK49323791',
'AK49323803',
'AK49323807',
'AK49323821',
'AK49323822',
'AK49338726',
'AK49338748',
'AK49338777',
'AK49338804',
'AK49338813',
'AK49338833',
'AK49338866',
'AK49352000',
'AK49352026',
'AK49352047',
'AK49352056',
'AK49352062',
'AK49352079',
'AK49352085',
'AK49352102',
'AK49352115',
'AK49352130',
'AK49352140',
'AK49352172',
'AK49352175',
'AK49352188',
'AK49352209',
'AK49352211',
'AK49352216',
'AK49352220',
'AK49352224',
'AK49352238',
'AK49352264',
'AK49352266',
'AK49352297',
'AK49352300',
'AK49352302',
'AK49352303',
'AK49352318',
'AK49352322',
'AK49352329',
'AK49352349',
'AK49352355',
'AK49352357',
'AK49378561',
'AK49378406',
'AK49378410',
'AK49378430',
'AK49378438',
'AK49378440',
'AK49378443',
'AK49378446',
'AK49378453',
'AK49378467',
'AK49378486',
'AK49378533',
'AK49378537',
'AK49378538',
'AK49407602',
'AK49407634',
'AK49407638',
'AK49407639',
'AK49407654',
'AK49407694',
'AK49407703',
'AK49462919',
'AK49462920',
'AK49462921',
'AK49462922',
'AK49462929',
'AK49462930',
'AK49462931',
'AK49462934',
'AK49520356',
'AK49520481',
'AK49520514',
'AK49519313',
'AK49519396',
'AK49519408',
'AK49519458',
'AK49519465',
'AK49519547',
'AK49519614',
'AK49519618',
'AK49519681',
'AK49519696',
'AK49519701',
'AK49519723',
'AK49519754',
'AK49519766',
'AK49519791',
'AK49519815',
'AK49519819',
'AK49519858',
'AK49519911',
'AK49519928',
'AK49519947',
'AK49520230',
'AK49520255',
'AK49520303',
'AK49520308',
'AK49520311',
'AK49520339',
'AK49520348',
'AK49520358',
'AK49520367',
'AK49520377',
'AK49520380',
'AK49520386',
'AK49520391',
'AK49520399',
'AK49520400',
'AK49520409',
'AK49520439',
'AK49520441',
'AK49520452',
'AK49520467',
'AK49520469',
'AK49520470',
'AK49520477',
'AK49520523',
'AK49520540',
'AK49520554',
'AK49520568',
'AK49520577',
'AK49520583',
'AK49520602',
'AK49520604',
'AK49520625',
'AK49520634',
'AK49520637',
'AK49520640',
'AK49520669',
'AK49488737',
'AK49488736',
'AK49488738',
'AK49488748',
'AK49488752',
'AK49488765',
'AK49488783',
'AK49488790',
'AK49488800',
'AK49488810',
'AK49488820',
'AK49488821',
'AK49488831',
'AK49488833',
'AK49488834',
'AK49488844',
'AK49488845',
'AK49488849',
'AK49488851',
'AK49488856',
'AK49488864',
'AK49488875',
'AK49437185',
'AK49437228',
'AK49437234',
'AK49437243',
'AK49437251',
'AK49437260',
'AK49437265',
'AK49437270',
'AK49437274',
'AK49437309',
'AK49437399',
'AK49437400',
'AK49437429',
'AK49300145',
'AK49520592',
'AK49749074',
'AK49749082',
'AK49749111',
'AK49749153',
'AK49749163',
'AK49749177',
'AK49704727',
'AK49703920',
'AK49703932',
'AK49703939',
'AK49703970',
'AK49703995',
'AK49704000',
'AK49704030',
'AK49704060',
'AK49704077',
'AK49704084',
'AK49704086',
'AK49704105',
'AK49704125',
'AK49704135',
'AK49704183',
'AK49704202',
'AK49704205',
'AK49704223',
'AK49704229',
'AK49704233',
'AK49704243',
'AK49704263',
'AK49704274',
'AK49704282',
'AK49704331',
'AK49704336',
'AK49704338',
'AK49704362',
'AK49704371',
'AK49704378',
'AK49704382',
'AK49704419',
'AK49704507',
'AK49704523',
'AK49704539',
'AK49704541',
'AK49704546',
'AK49704572',
'AK49704574',
'AK49704579',
'AK49704583',
'AK49704592',
'AK49704597',
'AK49704599',
'AK49704637',
'AK49704638',
'AK49704644',
'AK49704645',
'AK49704651',
'AK49704667',
'AK49704674',
'AK49704686',
'AK49704689',
'AK49704692',
'AK49704694',
'AK49704702',
'AK49704703',
'AK49704704',
'AK49704710',
'AK49704711',
'AK49704714',
'AK49704722',
'AK49704734',
'AK49704737',
'AK49704742',
'AK49704748',
'AK49704753',
'AK49704761',
'AK49704779',
'AK49704790',
'AK49704795',
'AK49704800',
'AK49704803',
'AK49704821',
'AK49727627',
'AK49727630',
'AK49727666',
'AK49727699',
'AK49727720',
'AK49727722',
'AK49727736',
'AK49727770',
'AK49763265',
'AK49763332',
'AK49763232',
'AK49378416',
'AK49378462',
'AK49763298',
'AK48419931',
'AK49378409',
'AK48716519',
'AK48716541',
'AK48186321',
'AK49520209',
'AK49520563',
'AK49300113',
'AK49323699',
'AK49437433',
'AK49462932',
'AK49520696',
'AK49519494',
'AK49704469',
'AK49704581',
'AK49703961',
'AK49749043',
'AK48979863',
'AK49734693',
'AK49752398',
'AK48697545',
'AK49493361',
'AB49427819',
'AB48913334',
'AK49541079',
'AK49498568',
'AK49494310',
'AK49494734',
'AK49495004',
'AK49495348',
'AK49495359',
'AK49495825',
'AK49495886',
'AK49496202',
'AK49496413',
'AK49496473',
'AK49496521',
'AK49496805',
'AK49496923',
'AK49497236',
'AK49497753',
'AK49498798',
'AK49499186',
'AK49499253',
'AK49500239',
'AK49500391',
'AK49500403',
'AK49500957',
'AK49501332',
'AK49501395',
'AK49501551',
'AK49502067',
'AK49502089',
'AK49502911',
'AK49503441',
'AK49503517',
'AK49503972',
'AK49504086',
'AK49504852',
'AK49504988',
'AK49505210',
'AK49505308',
'AK49505349',
'AK49505480',
'AK49505630',
'AK49505706',
'AK49505848',
'AK49506032',
'AK49506267',
'AK49506351',
'AK49506391',
'AK49506502',
'AK49506539',
'AK49506548',
'AK49506992',
'AK49507272',
'AK49507303',
'AK49507411',
'AK49507561',
'AK49507727',
'AK49507823',
'AK49508075',
'AK49508303',
'AK49508684',
'AK49508693',
'AK49509148',
'AK49509215',
'AK49509335',
'AK49509449',
'AK49509523',
'AK49509635',
'AK49509865',
'AK49510007',
'AK49510011',
'AK49510047',
'AK49510078',
'AK49510090',
'AK49510094',
'AK49510153',
'AK49510194',
'AK49511948',
'AK49512515',
'AK49512890',
'AK49514019',
'AK49514083',
'AK49514584',
'AK49515299',
'AK49515340',
'AK49515369',
'AK49515414',
'AK49515464',
'AK49515718',
'AK49515796',
'AK49515856',
'AK49516027',
'AK49516106',
'AK49516117',
'AK49516132',
'AK49516177',
'AK49516293',
'AK49516310',
'AK49516353',
'AK49516540',
'AK49516568',
'AK49516698',
'AK49516755',
'AK49516792',
'AK49516840',
'AK49516979',
'AK49516990',
'AK49517011',
'AK49517076',
'AK49517165',
'AK49517223',
'AK49517246',
'AK49517264',
'AK49517276',
'AK49517280',
'AK49517447',
'AK49517703',
'AK49517762',
'AK49517786',
'AK49517846',
'AK49517869',
'AK49518064',
'AK49518080',
'AK49518136',
'AK49518210',
'AK49518335',
'AK49518345',
'AK49518484',
'AK49518515',
'AK49518880',
'AK49518903',
'AK49518921',
'AK49518922',
'AK49518975',
'AK49519016',
'AK49519125',
'AK49519244',
'AK49519379',
'AK49519473',
'AK49519502',
'AK49519700',
'AK49519824',
'AK49520231',
'AK49520455',
'AK49520471',
'AK49520934',
'AK49521009',
'AK49521089',
'AK49521119',
'AK49521220',
'AK49521225',
'AK49521254',
'AK49521292',
'AK49521362',
'AK49521660',
'AK49521693',
'AK49521718',
'AK49521719',
'AK49521722',
'AK49521774',
'AK49521775',
'AK49521780',
'AK49521793',
'AK49521796',
'AK49522241',
'AK49522338',
'AK49522370',
'AK49522422',
'AK49522505',
'AK49522526',
'AK49522545',
'AK49522548',
'AK49522571',
'AK49522653',
'AK49522654',
'AK49522657',
'AK49522664',
'AK49522665',
'AK49522943',
'AK49522946',
'AK49522950',
'AK49522967',
'AK49523110',
'AK49523121',
'AK49523127',
'AK49523134',
'AK49523284',
'AK49523413',
'AK49523623',
'AK49523807',
'AK49523815',
'AK49524127',
'AK49524280',
'AK49524441',
'AK49524496',
'AK49524551',
'AK49524599',
'AK49524730',
'AK49524907',
'AK49524975',
'AK49525224',
'AK49525263',
'AK49525322',
'AK49525371',
'AK49525662',
'AK49525677',
'AK49525679',
'AK49525750',
'AK49526000',
'AK49526105',
'AK49526144',
'AK49526157',
'AK49526713',
'AK49526783',
'AK49526807',
'AK49526844',
'AK49526888',
'AK49526937',
'AK49527006',
'AK49527045',
'AK49527545',
'AK49527568',
'AK49527796',
'AK49527856',
'AK49527866',
'AK49527873',
'AK49527889',
'AK49527911',
'AK49527985',
'AK49528068',
'AK49528144',
'AK49528208',
'AK49528355',
'AK49528361',
'AK49528375',
'AK49528537',
'AK49528607',
'AK49528870',
'AK49528873',
'AK49529009',
'AK49529018',
'AK49529025',
'AK49529066',
'AK49529093',
'AK49529111',
'AK49529136',
'AK49529154',
'AK49529198',
'AK49529226',
'AK49529232',
'AK49529267',
'AK49529454',
'AK49529566',
'AK49529647',
'AK49530075',
'AK49530288',
'AK49530343',
'AK49530666',
'AK49530827',
'AK49530933',
'AK49531017',
'AK49531070',
'AK49531139',
'AK49531257',
'AK49531574',
'AK49531584',
'AK49531697',
'AK49531931',
'AK49532110',
'AK49532124',
'AK49532153',
'AK49532441',
'AK49532677',
'AK49532694',
'AK49533018',
'AK49533037',
'AK49533220',
'AK49533387',
'AK49533570',
'AK49533638',
'AK49534191',
'AK49535033',
'AK49537079',
'AK49537199',
'AK49537315',
'AK49537585',
'AK49537771',
'AK49537926',
'AK49537979',
'AK49538106',
'AK49538237',
'AK49538381',
'AK49538382',
'AK49538454',
'AK49538575',
'AK49538734',
'AK49538748',
'AK49538944',
'AK49538957',
'AK49538980',
'AK49538992',
'AK49538995',
'AK49539008',
'AK49539167',
'AK49539216',
'AK49539251',
'AK49539293',
'AK49539313',
'AK49539425',
'AK49539642',
'AK49539896',
'AK49540002',
'AK49540050',
'AK49540146',
'AK49540272',
'AK49540433',
'AK49540453',
'AK49540487',
'AK49540519',
'AK49540612',
'AK49540717',
'AK49541020',
'AK49541093',
'AK49541096',
'AK49541103',
'AK49541466',
'AK49541633',
'AK49541705',
'AK49541760',
'AK49542206',
'AK49542913',
'AK49543052',
'AK49543062',
'AK49543099',
'AK49543126',
'AK49543170',
'AK49543244',
'AK49543300',
'AK49543418',
'AK49543428',
'AK49543437',
'AK49543466',
'AK49543471',
'AK49543560',
'AK49543690',
'AK49543840',
'AK49543871',
'AK49543909',
'AK49543917',
'AK49544137',
'AK49544369',
'AK49544481',
'AK49544788',
'AK49545688',
'AK49545715',
'AK49545915',
'AK49545994',
'AK49546434',
'AK49546500',
'AK49546565',
'AK49546799',
'AK49546886',
'AK49547220',
'AK49547229',
'AK49547374',
'AK49547386',
'AK49547456',
'AK49547797',
'AK49547804',
'AK49548007',
'AK49548071',
'AK49548083',
'AK49548206',
'AK49548696',
'AK49548731',
'AK49548749',
'AK49548926',
'AK49549018',
'AK49549054',
'AK49549329',
'AK49549402',
'AK49549425',
'AK49549507',
'AK49549527',
'AK49549530',
'AK49549543',
'AK49549719',
'AK49549790',
'AK49549798',
'AK49549808',
'AK49549908',
'AK49549984',
'AK49549999',
'AK49550054',
'AK49550195',
'AK49550197',
'AK49550253',
'AK49550435',
'AK49550446',
'AK49550618',
'AK49550922',
'AK49551014',
'AK49551425',
'AK49551471',
'AK49551473',
'AK49551474',
'AK49595266',
'AK49595583',
'AK49596155',
'AK49596560',
'AK49597228',
'AK49597449',
'AK49597861',
'AK49597988',
'AK49598034',
'AK49598042',
'AK49598098',
'AK49598139',
'AK49598170',
'AK49598319',
'AK49598434',
'AK49598837',
'AK49598892',
'AK49599126',
'AK49599287',
'AK49599462',
'AK49599547',
'AK49599593',
'AK49599750',
'AK49599813',
'AK49600155',
'AK49600260',
'AK49600483',
'AK49600685',
'AK49600724',
'AK49600818',
'AK49600821',
'AK49600956',
'AK49601007',
'AK49601238',
'AK49601503',
'AK49601624',
'AK49601692',
'AK49602399',
'AK49602410',
'AK49602416',
'AK49602736',
'AK49602996',
'AK49603145',
'AK49603269',
'AK49603347',
'AK49603380',
'AK49603437',
'AK49603505',
'AK49603585',
'AK49603694',
'AK49603728',
'AK49603791',
'AK49603870',
'AK49604033',
'AK49604037',
'AK49604197',
'AK49604418',
'AK49604447',
'AK49604576',
'AK49604582',
'AK49604654',
'AK49604677',
'AK49604825',
'AK49604834',
'AK49605329',
'AK49605371',
'AK49605399',
'AK49605580',
'AK49605673',
'AK49606019',
'AK49606073',
'AK49606114',
'AK49606218',
'AK49606226',
'AK49606443',
'AK49606477',
'AK49606483',
'AK49606533',
'AK49606623',
'AK49606651',
'AK49606906',
'AK49607062',
'AK49607258',
'AK49607379',
'AK49607402',
'AK49607435',
'AK49607792',
'AK49607864',
'AK49607879',
'AK49608087',
'AK49608346',
'AK49608347',
'AK49608390',
'AK49608399',
'AK49608750',
'AK49608789',
'AK49608883',
'AK49608919',
'AK49609383',
'AK49609401',
'AK49609560',
'AK49609573',
'AK49609691',
'AK49609773',
'AK49610060',
'AK49610240',
'AK49610298',
'AK49610384',
'AK49610451',
'AK49610464',
'AK49610681',
'AK49610747',
'AK49610931',
'AK49611039',
'AK49611255',
'AK49611388',
'AK49611534',
'AK49611566',
'AK49611585',
'AK49611794',
'AK49612383',
'AK49612495',
'AK49612736',
'AK49612885',
'AK49612917',
'AK49613213',
'AK49613273',
'AK49613318',
'AK49613427',
'AK49613522',
'AK49613669',
'AK49613715',
'AK49613757',
'AK49613861',
'AK49613883',
'AK49613946',
'AK49613956',
'AK49614595',
'AK49614724',
'AK49614853',
'AK49615106',
'AK49615371',
'AK49615394',
'AK49615414',
'AK49615416',
'AK49615471',
'AK49615728',
'AK49615821',
'AK49615963',
'AK49616071',
'AK49616111',
'AK49616360',
'AK49616493',
'AK49616608',
'AK49616768',
'AK49616862',
'AK49617145',
'AK49617322',
'AK49617584',
'AK49617646',
'AK49618242',
'AK49618385',
'AK49618672',
'AK49618741',
'AK49618957',
'AK49619082',
'AK49619270',
'AK49619462',
'AK49619618',
'AK49619760',
'AK49619849',
'AK49619868',
'AK49620133',
'AK49620226',
'AK49620376',
'AK49620467',
'AK49620481',
'AK49620537',
'AK49620640',
'AK49620952',
'AK49620988',
'AK49621078',
'AK49621320',
'AK49621445',
'AK49621520',
'AK49621580',
'AK49621840',
'AK49621875',
'AK49621899',
'AK49621915',
'AK49621945',
'AK49622065',
'AK49622141',
'AK49622277',
'AK49622297',
'AK49622446',
'AK49622503',
'AK49622580',
'AK49622778',
'AK49622783',
'AK49622807',
'AK49622938',
'AK49622956',
'AK49622978',
'AK49623061',
'AK49623087',
'AK49623096',
'AK49623115',
'AK49623218',
'AK49623232',
'AK49623299',
'AK49623449',
'AK49623564',
'AK49623678',
'AK49623720',
'AK49623768',
'AK49623899',
'AK49623921',
'AK49623931',
'AK49624046',
'AK49624092',
'AK49624275',
'AK49624276',
'AK49624294',
'AK49624481',
'AK49624509',
'AK49624630',
'AK49624739',
'AK49624800',
'AK49624828',
'AK49624945',
'AK49625075',
'AK49625120',
'AK49625524',
'AK49625779',
'AK49625781',
'AK49625897',
'AK49626029',
'AK49626205',
'AK49626346',
'AK49626896',
'AK49626897',
'AK49626931',
'AK49627191',
'AK49627202',
'AK49627510',
'AK49627664',
'AK49627892',
'AK49628059',
'AK49628122',
'AK49628347',
'AK49628986',
'AK49629198',
'AK49629254',
'AK49629672',
'AK49629734',
'AK49629809',
'AK49630009',
'AK49630713',
'AK49630807',
'AK49631056',
'AK49631125',
'AK49631439',
'AK49631487',
'AK49631702',
'AK49631835',
'AK49631937',
'AK49632603',
'AK49632830',
'AK49632903',
'AK49632943',
'AK49633076',
'AK49634092',
'AK49634183',
'AK49634833',
'AK49634850',
'AK49634935',
'AK49634960',
'AK49635051',
'AK49635073',
'AK49635289',
'AK49635368',
'AK49635539',
'AK49636264',
'AK49636484',
'AK49637052',
'AK49637126',
'AK49637313',
'AK49637356',
'AK49637701',
'AK49637850',
'AK49638081',
'AK49638212',
'AK49638328',
'AK49638331',
'AK49638418',
'AK49638750',
'AK49639235',
'AK49639275',
'AK49639282',
'AK49639374',
'AK49639579',
'AK49639758',
'AK49639817',
'AK49639955',
'AK49640003',
'AK49640386',
'AK49640407',
'AK49640430',
'AK49640593',
'AK49640674',
'AK49640749',
'AK49640800',
'AK49640861',
'AK49641082',
'AK49641229',
'AK49641480',
'AK49641782',
'AK49641856',
'AK49642321',
'AK49642485',
'AK49643183',
'AK49643334',
'AK49643365',
'AK49643467',
'AK49643633',
'AK49644007',
'AK49644028',
'AK49644326',
'AK49644738',
'AK49644998',
'AK49645417',
'AK49645479',
'AK49646110',
'AK49646121',
'AK49646324',
'AK49646465',
'AK49647182',
'AK49647775',
'AK49647898',
'AK49776599',
'AK49776658',
'AK49776671',
'AK49776673',
'AK49776679',
'AK49776759',
'AK49776828',
'AK49776835',
'AK49776895',
'AK50118407',
'AK50118509',
'AK50118650',
'AK50118657',
'AK50118713',
'AK50118762',
'AK50118817',
'AK50118942',
'AK50118953',
'AK50119022',
'AK50119045',
'AK50119064',
'AK50119126',
'AK46532621',
'AK47167287',
'AK48862694',
'AK46536326',
'AK47164819',
'AK48024643',
'AK48861146',
'AK48019836',
'AK47991929',
'AK48779193',
'AK49643644',
'AK49596994',
'AK48816188',
'AK48829661',
'AK48831017',
'AK48822447',
'AK48774915',
'AK48834726',
'AK49307052',
'AK49501811',
'AK49627520',
'AK49517562',
'AK49644793',
'AK49531274',
'AK49525251',
'AK49517917',
'AK49539506',
'AK49533512',
'AK49628908',
'AK49621162',
'AK49528749',
'AK49537606',
'AK49519250',
'AK49612527',
'AK49499155',
'AK49516630',
'AK49547344',
'AK49624267',
'AK49601494',
'AK49604057',
'AK49611972',
'AK49507276',
'AK49518218',
'AK49530932',
'AK49538316',
'AK49633623',
'AK49522694',
'AK49644121',
'AK49610524',
'AK49499759',
'AK49536109',
'AK49641621',
'AK49620645',
'AK49515463',
'AK49622121',
'AK49504706',
'AK49645595',
'AK49615528',
'AK49522744',
'AK49524643',
'AK49530833',
'AK49532575',
'AK49620880',
'AK49549337',
'AK49524779',
'AK49527401',
'AK49520112',
'AK49601331',
'AK49637449',
'AK49609539',
'AK49551355',
'AK49612441',
'AK49623872',
'AK49548592',
'AK49630438',
'AK49519163',
'AK49524637',
'AK49521580',
'AK49529048',
'AK49549801',
'AK49539704',
'AK49522501',
'AK49522502',
'AK49546130',
'AK49635703',
'AK49522329',
'AK49548752',
'AK49605630',
'AK49644264',
'AK49632826',
'AK49635609',
'AK49617775',
'AK49609685',
'AK49520926',
'AK49526575',
'AK49546802',
'AK49501289',
'AK49598603',
'AK49508247',
'AK49521425',
'AK49524861',
'AK49549570',
'AK49613555',
'AK49622117',
'AK49509134',
'AK49532044',
'AK49537069',
'AK49538742',
'AK49603091',
'AK49596714',
'AK49631109',
'AK49536976',
'AK49548487',
'AK49515071',
'AK49522546',
'AK49523337',
'AK49543178',
'AK49543781',
'AK49509804',
'AK49519368',
'AK49526018',
'AK49533114',
'AK49606240',
'AK49622862',
'AK49621170',
'AK49507943',
'AK49517373',
'AK49613632',
'AK49639591',
'AK49616248',
'AK49521300',
'AK49526511',
'AK49510143',
'AK49529768',
'AK49494667',
'AK49503303',
'AK49524554',
'AK49612213',
'AK49510848',
'AK49507298',
'AK49543479',
'AK49602594',
'AK49622190',
'AK49638775',
'AK49616690',
'AK49595907',
'AK49613000',
'AK49524790',
'AK49518192',
'AK49640527',
'AK49548762',
'AK49643821',
'AK49620741',
'AK49622197',
'AK49648309',
'AK49540874',
'AK49619200',
'AK49629612',
'AK50119119',
'AK50118664',
'AK49622842',
'AK49498156',
'AK49494968',
'AK49637871',
'AK48023180',
'AK49625722',
'AK49497382',
'AK49527438',
'AK49522903',
'AK49528898',
'AK49596365',
'AK49597787',
'AK49621359',
'AK49638585',
'AK49627475',
'AK49506302',
'AK49538812',
'AK49496978',
'AK49521642',
'AK49503496',
'AK49623465',
'AK49645786',
'AK49504201',
'AK49518635',
'AK49495410',
'AK49624628',
'AK49546492',
'AK49607381',
'AK49524864',
'AK49495103',
'AK49519676',
'AK49616628',
'AK49776909',
'AK49532413',
'AK49542167',
'AK49611389',
'AK49527400',
'AK49645890',
'AK49624070',
'AK49497602',
'AK49497956',
'AK49639224',
'AK49640890',
'AK49605933',
'AK49637928',
'AK49638801',
'AK49522977',
'AK49623438',
'AK49614352',
'AK49604959',
'AK49508320',
'AK49595900',
'AK49609465',
'AK49613344',
'AK49621299',
'AK49613523',
'AK49611813',
'AK49630809',
'AK49528677',
'AK49624687',
'AK49528166',
'AK49507625',
'AK49515759',
'AK49523554',
'AK49524463',
'AK49528634',
'AK49614228',
'AK49618143',
'AK49619019',
'AK49619188',
'AK49619478',
'AK49624335',
'AK49625741',
'AK49636156',
'AK49509139',
'AK49513226',
'AK49521511',
'AK49531516',
'AK49541393',
'AK49597285',
'AK49621856',
'AK49624459',
'AK49632856',
'AK49641416',
'AK49644754',
'AK49528319',
'AK49532195',
'AK49628783',
'AK49520763',
'AK49549350',
'AK49525519',
'AK49507356',
'AK49519489',
'AK49528420',
'AK49530837',
'AK49533384',
'AK49527863',
'AK49642323',
'AK49521067',
'AK49549807',
'AK49520404',
'AK49531030',
'AK49529268',
'AK49538446',
'AK49637111',
'AK49523776',
'AK49530752',
'AK49504113',
'AK49529732',
'AK49596549',
'AK49603358',
'AK49627531',
'AK49524323',
'AK49517058',
'AK49523426',
'AK49530629',
'AK49524886',
'AK49630792',
'AK49551266',
'AK49498263',
'AK49548615',
'AK49600543',
'AK49603580',
'AK49614157',
'AK49621630',
'AK49630299',
'AK49640937',
'AK49598964',
'AK49620100',
'AK49539572',
'AK49540863',
'AK49626918',
'AK49640132',
'AK50118678',
'AK49503181',
'AK49520981',
'AK49526959',
'AK49610745',
'AK49613201',
'AK49518822',
'AK49499225',
'AK49538815',
'AK49503110',
'AK49637245',
'AK49609673',
'AK49504010',
'AK49517049',
'AK49599712',
'AK49601796',
'AK49606942',
'AK49612724',
'AK49632382',
'AK49635789',
'AK49636980',
'AK49617178',
'AK49624562',
'AK49496134',
'AK49506685',
'AK49525481',
'AK49526298',
'AK49524987',
'AK49535834',
'AK49549504',
'AK49598610',
'AK49526777',
'AK49540287',
'AK49610966',
'AK49641056',
'AK49517457',
'AK49527565',
'AK49548889',
'AK49501354',
'AK49518038',
'AK49533680',
'AK49544331',
'AK49523533',
'AK49543286',
'AK49549777',
'AK49541505',
'AK49521495',
'AK49641382',
'AK49525243',
'AK49525133',
'AK49529862',
'AK49515730',
'AK49529293',
'AK49551138',
'AK49510162',
'AK49498017',
'AK49523890',
'AK49542925',
'AK49639133',
'AK48847634',
'AK49512495',
'AK49537817',
'AK49537164',
'AK49546549',
'AK49624316',
'AK49532565',
'AK49608272',
'AK49524110',
'AK49629138',
'AK49531133',
'AK49607607',
'AK49632975',
'AK49639479',
'AK49528543',
'AK49610734',
'AK49529209',
'AK49620935',
'AK49539841',
'AK49621192',
'AK49634958',
'AK49522616',
'AK49620055',
'AK49527420',
'AK49639371',
'AK49527920',
'AK49626345',
'AK49521589',
'AK49614337',
'AK49523926',
'AK49526085',
'AK49615072',
'AK49603199',
'AK49604774',
'AK49500013',
'AK49500437',
'AK49498575',
'AK49538786',
'AK49522522',
'AK49606724',
'AK49550123',
'AK49637280',
'AK49523832',
'AK49511453',
'AK49517020',
'AK50118566',
'AK49530614',
'AK49635334',
'AK49634385',
'AK49502082',
'AK49520684',
'AK49609295',
'AK49638667',
'AK49625749',
'AK49538707',
'AK49551227',
'AK49601613',
'AK49499972',
'AK49521470',
'AK49628260',
'AK48813109',
'AK49626129',
'AK49525527',
'AK49502302',
'AK49549271',
'AK48862318',
'AK49498684',
'AK49523390',
'AK49498371',
'AK49635653',
'AK49614625',
'AK49606638',
'AK49621420',
'AK49602297',
'AK49522077',
'AK49540483',
'AK49546285',
'AK49619786',
'AK49522707',
'AK49523405',
'AK49620525',
'AK49624067',
'AK49619654',
'AK49610978',
'AK49625375',
'AK49597290',
'AK49776605',
'AK49547569',
'AK49547855',
'AK49632158',
'AK49549742',
'AK49550391',
'AK49596286',
'AK49596003',
'AK49621077',
'AK49621431',
'AK49621999',
'AK49623879',
'AK49624642',
'AK49538365',
'AK49523586',
'AK49529050',
'AK49607822',
'AK49608517',
'AK49527642',
'AK49527663',
'AK49527916',
'AK49527954',
'AK49528113',
'AK49529195',
'AK49529214',
'AK49536938',
'AK49527835',
'AK49543222',
'AK49540540',
'AK49538873',
'AK49524048',
'AK49543412',
'AK49540362',
'AK49540467',
'AK49601290',
'AK49602324',
'AK49627461',
'AK49640505',
'AK49640752',
'AK49540492',
'AK49600602',
'AK49548830',
'AK49558461',
'AK49386694',
'AK49789250',
'AK49791259',
'AK49559532',
'AK49398483',
'AK49754267',
'AK49453509',
'AK49673479',
'AK49673506',
'AK49673537',
'AK49489376',
'AK49489379',
'AK49489381',
'AK49654098',
'AK49791274',
'AK49705175',
'AK49680826',
'AK49245282',
'AK49352313',
'AK49352314',
'AK49352316',
'AK49407701',
'AK49437359',
'AK49459968',
'AK49459970',
'AK49488701',
'AK49519775',
'AK49300083',
'AK49488703',
'AK49300084',
'AK49245280',
'AK49222364',
'AK48419949',
'AK49178786',
'AK49378636',
'AK49465546',
'AK49342789',
'AK49329870',
'AK47473453',
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
$this->logger->info('_data_patch_20210114_1500_CancelRegister.php end');

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
