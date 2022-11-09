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
$this->logger->info('_data_patch_20191119_1500_CancelRegister.php start');

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
'AK39162856',
'AK39162893',
'AK39162918',
'AK39162925',
'AK39162938',
'AK39162939',
'AK39162974',
'AK39162992',
'AK39162993',
'AK39163017',
'AK39163018',
'AK39163024',
'AK39163025',
'AK39163055',
'AK39163056',
'AK39163080',
'AK39163081',
'AK39163082',
'AK39163098',
'AK39163100',
'AK39163102',
'AK39196045',
'AK39196055',
'AK39196077',
'AK39196078',
'AK39246255',
'AK39246267',
'AK39246272',
'AK39246278',
'AK39246279',
'AK39246300',
'AK39246301',
'AK39246302',
'AK39246307',
'AK39246328',
'AK39246343',
'AK39246351',
'AK39246357',
'AK39246381',
'AK39246382',
'AK39246384',
'AK39246387',
'AK39246388',
'AK39246414',
'AK39246422',
'AK39246428',
'AK39246439',
'AK39246457',
'AK39246462',
'AK39246475',
'AK39246496',
'AK39246510',
'AK39246532',
'AK39246544',
'AK39246545',
'AK39246578',
'AK39246581',
'AK39246595',
'AK39246615',
'AK39246656',
'AK39246657',
'AK39246667',
'AK39246678',
'AK39246691',
'AK39246724',
'AK39246729',
'AK39246730',
'AK39246738',
'AK39246739',
'AK39246747',
'AK39246779',
'AK39246797',
'AK39246808',
'AK39246809',
'AK39246810',
'AK39246827',
'AK39246832',
'AK39246848',
'AK39246864',
'AK39246867',
'AK39246868',
'AK39246870',
'AK39246875',
'AK39246880',
'AK39246881',
'AK39246882',
'AK39246912',
'AK39246928',
'AK39246929',
'AK39246930',
'AK39246953',
'AK39246961',
'AK39247020',
'AK39247031',
'AK39247058',
'AK39247066',
'AK39247071',
'AK39247077',
'AK39247096',
'AK39247132',
'AK39247139',
'AK39247156',
'AK39247157',
'AK39247204',
'AK39247215',
'AK39247238',
'AK39247252',
'AK39247267',
'AK39247329',
'AK39247347',
'AK39247365',
'AK39247378',
'AK39247403',
'AK39247422',
'AK39247436',
'AK39247445',
'AK39247447',
'AK39247496',
'AK39247500',
'AK39247516',
'AK39247517',
'AK39247527',
'AK39247542',
'AK39247548',
'AK39247565',
'AK39247566',
'AK39247567',
'AK39247580',
'AK39247584',
'AK39247585',
'AK39247600',
'AK39247605',
'AK39247606',
'AK39247695',
'AK39247701',
'AK39247717',
'AK39247720',
'AK39247726',
'AK39247733',
'AK39247734',
'AK39247748',
'AK39247789',
'AK39247790',
'AK39247791',
'AK39247853',
'AK39247854',
'AK39247857',
'AK39247884',
'AK39247885',
'AK39247902',
'AK39247905',
'AK39247906',
'AK39247907',
'AK39247908',
'AK39247913',
'AK39247922',
'AK39247925',
'AK39247926',
'AK39247956',
'AK39247965',
'AK39247979',
'AK39247987',
'AK39247991',
'AK39247992',
'AK39248038',
'AK39248039',
'AK39248048',
'AK39248061',
'AK39248074',
'AK39248095',
'AK39248134',
'AK39248137',
'AK39248154',
'AK39248185',
'AK39248190',
'AK39248195',
'AK39248199',
'AK39248201',
'AK39248206',
'AK39248212',
'AK39248215',
'AK39248226',
'AK39248249',
'AK39248259',
'AK39248288',
'AK39248290',
'AK39248294',
'AK39248312',
'AK39248326',
'AK39248336',
'AK39248355',
'AK39248359',
'AK39248386',
'AK39248388',
'AK39248392',
'AK39248393',
'AK39248414',
'AK39248421',
'AK39248436',
'AK39248448',
'AK39248480',
'AK39248482',
'AK39248569',
'AK39248590',
'AK39248591',
'AK39248617',
'AK39248638',
'AK39248654',
'AK39248661',
'AK39248672',
'AK39248676',
'AK39248677',
'AK39248721',
'AK39248744',
'AK39248781',
'AK39248782',
'AK39248787',
'AK39248788',
'AK39248800',
'AK39248863',
'AK39248867',
'AK39248892',
'AK39248898',
'AK39248930',
'AK39248931',
'AK39248937',
'AK39248938',
'AK39248943',
'AK39248948',
'AK39248950',
'AK39248960',
'AK39248984',
'AK39248989',
'AK39249013',
'AK39249017',
'AK39249021',
'AK39249022',
'AK39249099',
'AK39249120',
'AK39249127',
'AK39249143',
'AK39249275',
'AK39249306',
'AK39249344',
'AK39249345',
'AK39249346',
'AK39249371',
'AK39249372',
'AK39249396',
'AK39249426',
'AK39249431',
'AK39249437',
'AK39249438',
'AK39249467',
'AK39249473',
'AK39249486',
'AK39249491',
'AK39249509',
'AK39249537',
'AK39249539',
'AK39249548',
'AK39249551',
'AK39249587',
'AK39249593',
'AK39249599',
'AK39249613',
'AK39249620',
'AK39249641',
'AK39249668',
'AK39249669',
'AK39249696',
'AK39249697',
'AK39249698',
'AK39249699',
'AK39249729',
'AK39249750',
'AK39249753',
'AK39249758',
'AK39249766',
'AK39249816',
'AK39249868',
'AK39249869',
'AK39249870',
'AK39249871',
'AK39249881',
'AK39249889',
'AK39249892',
'AK39249946',
'AK39249952',
'AK39249975',
'AK39249996',
'AK39249999',
'AK39250042',
'AK39250046',
'AK39250048',
'AK39250094',
'AK39250096',
'AK39250114',
'AK39250127',
'AK39250128',
'AK39250131',
'AK39250140',
'AK39250180',
'AK39250203',
'AK39250204',
'AK39250214',
'AK39250225',
'AK39250264',
'AK39250278',
'AK39250295',
'AK39250303',
'AK39250306',
'AK39250324',
'AK39250325',
'AK39250361',
'AK39250401',
'AK39250467',
'AK39250483',
'AK39250612',
'AK39250626',
'AK39250631',
'AK39250632',
'AK39250633',
'AK39250652',
'AK39250655',
'AK39250659',
'AK39250672',
'AK39250673',
'AK39250728',
'AK39250734',
'AK39250736',
'AK39250740',
'AK39250758',
'AK39250776',
'AK39250777',
'AK39250787',
'AK39250821',
'AK39250828',
'AK39250830',
'AK39250836',
'AK39250857',
'AK39250858',
'AK39250877',
'AK39250878',
'AK39250932',
'AK39250933',
'AK39250935',
'AK39250958',
'AK39250969',
'AK39250972',
'AK39250996',
'AK39251005',
'AK39251015',
'AK39251026',
'AK39251027',
'AK39251033',
'AK39251040',
'AK39251045',
'AK39251090',
'AK39251092',
'AK39251098',
'AK39251099',
'AK39251118',
'AK39251126',
'AK39251144',
'AK39251148',
'AK39251200',
'AK39251212',
'AK39251227',
'AK39251229',
'AK39251242',
'AK39251243',
'AK39251263',
'AK39251267',
'AK39251285',
'AK39251296',
'AK39251304',
'AK39251337',
'AK39251350',
'AK39251355',
'AK39251356',
'AK39251362',
'AK39251370',
'AK39251417',
'AK39251450',
'AK39251459',
'AK39251469',
'AK39251476',
'AK39251480',
'AK39251485',
'AK39251486',
'AK39251492',
'AK39251493',
'AK39251494',
'AK39251496',
'AK39251498',
'AK39251516',
'AK39251535',
'AK39251567',
'AK39251588',
'AK39251607',
'AK39251608',
'AK39251609',
'AK39251612',
'AK39251625',
'AK39251678',
'AK39251679',
'AK39251681',
'AK39251682',
'AK39251683',
'AK39251694',
'AK39251700',
'AK39251701',
'AK39251726',
'AK39251773',
'AK39251811',
'AK39251819',
'AK39251872',
'AK39251873',
'AK39251892',
'AK39251893',
'AK39251902',
'AK39251959',
'AK39251960',
'AK39251967',
'AK39252014',
'AK39252046',
'AK39252052',
'AK39252053',
'AK39252056',
'AK39252063',
'AK39252106',
'AK39252107',
'AK39252108',
'AK39252161',
'AK39252172',
'AK39252224',
'AK39252232',
'AK39252244',
'AK39252299',
'AK39252303',
'AK39252323',
'AK39252330',
'AK39252337',
'AK39252339',
'AK39252353',
'AK39252418',
'AK39252423',
'AK39252470',
'AK39252484',
'AK39252536',
'AK39252577',
'AK39252584',
'AK39252589',
'AK39252612',
'AK39252616',
'AK39252617',
'AK39252618',
'AK39252633',
'AK39252638',
'AK39252639',
'AK39252658',
'AK39252723',
'AK39252730',
'AK39252732',
'AK39252764',
'AK39252774',
'AK39252813',
'AK39252865',
'AK39252873',
'AK39252933',
'AK39252940',
'AK39252945',
'AK39252954',
'AK39252974',
'AK39252983',
'AK39252984',
'AK39252987',
'AK39252989',
'AK39252999',
'AK39253003',
'AK39253064',
'AK39253073',
'AK39253074',
'AK39253099',
'AK39253100',
'AK39253101',
'AK39253107',
'AK39253121',
'AK39253149',
'AK39253172',
'AK39253220',
'AK39253235',
'AK39253260',
'AK39253281',
'AK39253282',
'AK39253287',
'AK39253292',
'AK39253293',
'AK39253300',
'AK39253308',
'AK39253309',
'AK39253311',
'AK39253316',
'AK39253317',
'AK39253318',
'AK39253338',
'AK39253346',
'AK39253355',
'AK39253372',
'AK39253398',
'AK39253459',
'AK39253467',
'AK39253511',
'AK39253531',
'AK39253564',
'AK39253565',
'AK39253568',
'AK39253648',
'AK39253664',
'AK39253666',
'AK39253677',
'AK39253679',
'AK39253722',
'AK39253723',
'AK39253737',
'AK39253762',
'AK39253787',
'AK39253795',
'AK39253889',
'AK39253911',
'AK39253914',
'AK39253942',
'AK39254063',
'AK39254075',
'AK39254076',
'AK39254077',
'AK39254080',
'AK39254081',
'AK39254189',
'AK39254190',
'AK39254208',
'AK39254243',
'AK39254250',
'AK39254272',
'AK39254273',
'AK39254289',
'AK39254296',
'AK39254355',
'AK39254424',
'AK39254464',
'AK39254474',
'AK39254505',
'AK39254583',
'AK39254589',
'AK39254602',
'AK39254649',
'AK39254673',
'AK39254713',
'AK39254759',
'AK39254780',
'AK39254784',
'AK39254798',
'AK39254808',
'AK39254815',
'AK39254821',
'AK39254823',
'AK39254830',
'AK39254846',
'AK39254859',
'AK39254875',
'AK39254883',
'AK39254886',
'AK39254891',
'AK39254990',
'AK39255049',
'AK39255054',
'AK39255059',
'AK39255062',
'AK39255097',
'AK39255102',
'AK39255106',
'AK39255150',
'AK39255151',
'AK39255156',
'AK39255157',
'AK39255169',
'AK39255193',
'AK39255194',
'AK39255202',
'AK39255212',
'AK39255223',
'AK39255248',
'AK39255249',
'AK39255251',
'AK39255256',
'AK39255306',
'AK39255314',
'AK39255323',
'AK39255326',
'AK39255342',
'AK39255346',
'AK39255389',
'AK39255410',
'AK39255414',
'AK39255453',
'AK39255454',
'AK39255457',
'AK39255458',
'AK39255462',
'AK39255501',
'AK39255502',
'AK39255508',
'AK39255510',
'AK39255513',
'AK39255526',
'AK39255571',
'AK39255610',
'AK39255637',
'AK39255654',
'AK39255665',
'AK39255667',
'AK39255677',
'AK39255681',
'AK39255682',
'AK39255683',
'AK39255697',
'AK39255699',
'AK39255701',
'AK39255710',
'AK39255713',
'AK39255720',
'AK39255828',
'AK39255841',
'AK39255854',
'AK39255857',
'AK39255858',
'AK39255883',
'AK39255886',
'AK39255898',
'AK39255903',
'AK39255904',
'AK39255906',
'AK39255914',
'AK39255920',
'AK39255927',
'AK39255934',
'AK39255943',
'AK39256018',
'AK39256024',
'AK39256026',
'AK39256033',
'AK39256039',
'AK39256058',
'AK39256065',
'AK39256083',
'AK39256088',
'AK39256089',
'AK39256125',
'AK39256140',
'AK39256143',
'AK39256164',
'AK39256178',
'AK39256198',
'AK39256201',
'AK39256203',
'AK39256211',
'AK39256220',
'AK39256236',
'AK39256239',
'AK39256244',
'AK39256269',
'AK39256272',
'AK39256275',
'AK39256276',
'AK39256277',
'AK39256300',
'AK39256304',
'AK39256308',
'AK39256320',
'AK39256321',
'AK39256322',
'AK39256325',
'AK39256326',
'AK39256347',
'AK39256356',
'AK39256372',
'AK39256379',
'AK39256432',
'AK39256433',
'AK39256434',
'AK39256448',
'AK39256449',
'AK39256480',
'AK39256495',
'AK39256500',
'AK39256581',
'AK39256587',
'AK39256602',
'AK39256644',
'AK39256660',
'AK39256698',
'AK39256699',
'AK39256705',
'AK39256709',
'AK39256758',
'AK39256760',
'AK39256766',
'AK39256772',
'AK39256783',
'AK39256792',
'AK39256828',
'AK39256829',
'AK39256842',
'AK39256845',
'AK39256895',
'AK39256934',
'AK39256963',
'AK39256971',
'AK39256978',
'AK39256996',
'AK39257014',
'AK39257032',
'AK39257052',
'AK39257053',
'AK39257055',
'AK39257056',
'AK39257057',
'AK39257084',
'AK39257099',
'AK39257108',
'AK39282725',
'AK39282801',
'AK39282933',
'AK39282974',
'AK39283156',
'AK39283157',
'AK39283232',
'AK39283233',
'AK39283263',
'AK39283275',
'AK39283353',
'AK39283382',
'AK39283383',
'AK39283417',
'AK39283564',
'AK39283581',
'AK39283582',
'AK39283596',
'AK39283639',
'AK39283644',
'AK39283645',
'AK39283687',
'AK39283688',
'AK39283832',
'AK39283833',
'AK39283878',
'AK39283883',
'AK39283914',
'AK39284037',
'AK39284161',
'AK39284167',
'AK39284206',
'AK39284218',
'AK39284230',
'AK39284235',
'AK39284326',
'AK39284347',
'AK39284348',
'AK39284379',
'AK39284381',
'AK39284435',
'AK39284436',
'AK39284444',
'AK39284480',
'AK39284752',
'AK39284870',
'AK39284873',
'AK39284917',
'AK39284920',
'AK39284921',
'AK39284975',
'AK39284976',
'AK39284994',
'AK39285056',
'AK39285183',
'AK39285328',
'AK39285329',
'AK39285380',
'AK39285477',
'AK39285483',
'AK39285494',
'AK39285495',
'AK39285676',
'AK39285747',
'AK39285791',
'AK39285817',
'AK39467861',
'AK39467918',
'AK39468014',
'AK39468025',
'AK39468069',
'AK39468088',
'AK39468143',
'AK39468154',
'AK39468156',
'AK39468221',
'AK39468222',
'AK39468238',
'AK39468304',
'AK39468334',
'AK39468339',
'AK39468348',
'AK39468373',
'AK39468406',
'AK39468465',
'AK39468654',
'AK39468890',
'AK39468907',
'AK39468919',
'AK39468936',
'AK39468940',
'AK39468960',
'AK39469000',
'AK39469005',
'AK39469068',
'AK39469079',
'AK39469117',
'AK39469118',
'AK39469132',
'AK39469151',
'AK39469441',
'AK39469463',
'AK39469468',
'AK39469605',
'AK39469757',
'AK39469807',
'AK39469851',
'AK39469871',
'AK39469903',
'AK39469904',
'AK39470006',
'AK39470054',
'AK39470132',
'AK39470151',
'AK39470191',
'AK39470260',
'AK39470266',
'AK39470286',
'AK39470293',
'AK39470402',
'AK39470431',
'AK39470432',
'AK39470466',
'AK39470596',
'AK39470648',
'AK39470651',
'AK39470738',
'AK39470759',
'AK39470802',
'AK39470816',
'AK39470858',
'AK39470945',
'AK39470947',
'AK39471111',
'AK39471197',
'AK39471225',
'AK39471366',
'AK39471579',
'AK39471647',
'AK39471766',
'AK39471957',
'AK39472008',
'AK39472062',
'AK39472142',
'AK39472181',
'AK39472206',
'AK39472215',
'AK39472218',
'AK39472251',
'AK39472257',
'AK39472262',
'AK39472270',
'AK39472279',
'AK39472325',
'AK39472341',
'AK39472358',
'AK39472389',
'AK39472442',
'AK39472452',
'AK39472489',
'AK39472661',
'AK39472682',
'AK39472771',
'AK39472773',
'AK39472785',
'AK39472802',
'AK39472826',
'AK39472865',
'AK39472871',
'AK39472964',
'AK39472972',
'AK39473050',
'AK39473056',
'AK39473078',
'AK39473083',
'AK39473086',
'AK39557339',
'AK39557364',
'AK39557365',
'AK39557369',
'AK39557370',
'AK39557375',
'AK39557376',
'AK39557414',
'AK39557429',
'AK39557438',
'AK39557439',
'AK39557459',
'AK39557460',
'AK39557461',
'AK39557462',
'AK39557464',
'AK39557465',
'AK39557495',
'AK39557507',
'AK39557509',
'AK39557510',
'AK39557511',
'AK39557559',
'AK39557649',
'AK39557650',
'AK39557677',
'AK39557717',
'AK39557718',
'AK39557725',
'AK39557726',
'AK39557736',
'AK39557756',
'AK39557757',
'AK39557758',
'AK39557761',
'AK39557762',
'AK39557769',
'AK39557796',
'AK39557818',
'AK39557819',
'AK39557833',
'AK39557839',
'AK39557847',
'AK39557848',
'AK39557868',
'AK39557878',
'AK39557879',
'AK39557881',
'AK39557902',
'AK39557918',
'AK39557958',
'AK39557975',
'AK39557990',
'AK39557994',
'AK39557996',
'AK39558044',
'AK39558726',
'AK39558731',
'AK39558757',
'AK39558762',
'AK39558763',
'AK39558764',
'AK39558765',
'AK39558767',
'AK39558769',
'AK39558770',
'AK39558776',
'AK39558777',
'AK39558780',
'AK39558781',
'AK39558794',
'AK39558795',
'AK39558796',
'AK39558797',
'AK39558810',
'AK39558811',
'AK39558814',
'AK39558819',
'AK39558865',
'AK39558866',
'AK39558868',
'AK39558870',
'AK39558871',
'AK39558887',
'AK39558891',
'AK39558892',
'AK39558893',
'AK39558898',
'AK39558902',
'AK39558904',
'AK39558912',
'AK39598754',
'AK39598765',
'AK39598766',
'AK39598767',
'AK39598768',
'AK39598778',
'AK39598780',
'AK39598870',
'AK39598873',
'AK39598887',
'AK39598888',
'AK39598908',
'AK39598909',
'AK39598910',
'AK39598934',
'AK39598935',
'AK39598940',
'AK39598941',
'AK39598950',
'AK39598990',
'AK39599013',
'AK39599022',
'AK39599067',
'AK39599089',
'AK39599090',
'AK39599094',
'AK39599099',
'AK39599145',
'AK39599158',
'AK39599171',
'AK39599172',
'AK39599200',
'AK39599201',
'AK39599214',
'AK39599217',
'AK39600020',
'AK39600026',
'AK39600028',
'AK39600043',
'AK39600046',
'AK39600047',
'AK39600050',
'AK39600052',
'AK39600058',
'AK39600074',
'AK39692959',
'AK39692960',
'AK39692976',
'AK39692977',
'AK39693002',
'AK39693009',
'AK40018069',
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
$this->logger->info('_data_patch_20191119_1500_CancelRegister.php end');

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
