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
$this->logger->info('_data_patch_20201111_1750_CancelRegister.php start');

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
'AK47442926',
'AK47500814',
'AK47623672',
'AK47764520',
'AK48047808',
'AK48137980',
'AK48196048',
'AK48227569',
'AK48542699',
'AK47423133',
'AK47646394',
'AK47801742',
'AK47907576',
'AK46072443',
'AK45285236',
'AK47708590',
'AK47746488',
'AK47751721',
'AK47753046',
'AK48108520',
'AK47820586',
'AK48343384',
'AK47803357',
'AK47993787',
'AK48082594',
'AK48201308',
'AK48201331',
'AK48201336',
'AK48201351',
'AK48201420',
'AK48233607',
'AK48233614',
'AK48233622',
'AK48233696',
'AK48233718',
'AK48319483',
'AK48319574',
'AK48343599',
'AK48343623',
'AK48343680',
'AK48343738',
'AK48506841',
'AK48506907',
'AK48506922',
'AK48543439',
'AK48543445',
'AK48543520',
'AK48565066',
'AK48565091',
'AK47504921',
'AK46812320',
'AK46846113',
'AK47209386',
'AK47765722',
'AK47820860',
'AK47842640',
'AK48076286',
'AK48475609',
'AK48315993',
'AK47841808',
'AK48074711',
'AK48074872',
'AK48255551',
'AK48255727',
'AK48238847',
'AK47847903',
'AK47866978',
'AK48280881',
'AK48281523',
'AK48292516',
'AK48538658',
'AK47755305',
'AK47795385',
'AK47795423',
'AK47795579',
'AK47795637',
'AK47795839',
'AK47819191',
'AK47897831',
'AK47924320',
'AK47924912',
'AK47924961',
'AK48043529',
'AK48043606',
'AK48044299',
'AK48080305',
'AK48080491',
'AK48201473',
'AK48201615',
'AK48201671',
'AK48310089',
'AK48310431',
'AK48310502',
'AK48337539',
'AK48337629',
'AK48377899',
'AK48377966',
'AK48378003',
'AK48378072',
'AK48378183',
'AK48378218',
'AK48380824',
'AK48467625',
'AK48508808',
'AK48539500',
'AK48557702',
'AK48557758',
'AK48557777',
'AK47850242',
'AK48300959',
'AK48384348',
'AK47348296',
'AK47348558',
'AK47429454',
'AK47429495',
'AK47429497',
'AK47429501',
'AK47429508',
'AK47429531',
'AK47429536',
'AK47429568',
'AK47429569',
'AK47429585',
'AK47429588',
'AK47429590',
'AK47429593',
'AK47429596',
'AK47429607',
'AK47542144',
'AK47542158',
'AK47542165',
'AK47542177',
'AK47542182',
'AK47542184',
'AK47542186',
'AK47542192',
'AK47542196',
'AK47542244',
'AK47542245',
'AK47542249',
'AK47542250',
'AK47542262',
'AK47542266',
'AK47542283',
'AK47542289',
'AK47542326',
'AK47542342',
'AK47542358',
'AK47542370',
'AK47542378',
'AK47542379',
'AK47542398',
'AK47545522',
'AK47660967',
'AK47660969',
'AK47660983',
'AK47660987',
'AK47660992',
'AK47661019',
'AK47661029',
'AK47661032',
'AK47661043',
'AK47661047',
'AK47661059',
'AK47661072',
'AK47661077',
'AK47661083',
'AK47661090',
'AK47661107',
'AK47661119',
'AK47661121',
'AK47661124',
'AK47661134',
'AK47661144',
'AK47661145',
'AK47661151',
'AK47661153',
'AK47661161',
'AK47661165',
'AK47661168',
'AK47661188',
'AK47661200',
'AK47661213',
'AK47661218',
'AK47661221',
'AK47661224',
'AK47686913',
'AK47686939',
'AK47686947',
'AK47686959',
'AK47686966',
'AK47687006',
'AK47687013',
'AK47687019',
'AK47687021',
'AK47687023',
'AK47687027',
'AK47687035',
'AK47687039',
'AK47687050',
'AK47687052',
'AK47687056',
'AK47687062',
'AK47687064',
'AK47687089',
'AK47687091',
'AK47687097',
'AK47687101',
'AK47687108',
'AK47687110',
'AK47687112',
'AK47687129',
'AK47687134',
'AK47687173',
'AK47687180',
'AK47687187',
'AK47687200',
'AK47705717',
'AK47705761',
'AK47705765',
'AK47705794',
'AK47705796',
'AK47705797',
'AK47705873',
'AK47705902',
'AK47705907',
'AK47705919',
'AK47705930',
'AK47705939',
'AK47705943',
'AK47705982',
'AK47705988',
'AK47706005',
'AK47706011',
'AK47706012',
'AK47706015',
'AK47720708',
'AK47720716',
'AK47720735',
'AK47720755',
'AK47720770',
'AK47720786',
'AK47720787',
'AK47720789',
'AK47720816',
'AK47720828',
'AK47720848',
'AK47720858',
'AK47720871',
'AK47720887',
'AK47720898',
'AK47720917',
'AK47720925',
'AK47720944',
'AK47720948',
'AK47720956',
'AK47720963',
'AK47720975',
'AK47720977',
'AK47720981',
'AK47720997',
'AK47720999',
'AK47721016',
'AK47721070',
'AK47721110',
'AK47721151',
'AK47721180',
'AK47721198',
'AK47749659',
'AK47749677',
'AK47749697',
'AK47749706',
'AK47749719',
'AK47749728',
'AK47749730',
'AK47749731',
'AK47749740',
'AK47749750',
'AK47749761',
'AK47749782',
'AK47749792',
'AK47749795',
'AK47749802',
'AK47749812',
'AK47749814',
'AK47749816',
'AK47749821',
'AK47749831',
'AK47749832',
'AK47749834',
'AK47749835',
'AK47749858',
'AK47749873',
'AK47749879',
'AK47749885',
'AK47749891',
'AK47749910',
'AK47749919',
'AK47749932',
'AK47749935',
'AK47749938',
'AK47749941',
'AK47749942',
'AK47749944',
'AK47749945',
'AK47749947',
'AK47749957',
'AK47749966',
'AK47749974',
'AK47749980',
'AK47749981',
'AK47750002',
'AK47750003',
'AK47750005',
'AK47750007',
'AK47750009',
'AK47750010',
'AK47750015',
'AK47750023',
'AK47750025',
'AK47750054',
'AK47750057',
'AK47750066',
'AK47750075',
'AK47750076',
'AK47750077',
'AK47750079',
'AK47750084',
'AK47750091',
'AK47750093',
'AK47750101',
'AK47750107',
'AK47750108',
'AK47750112',
'AK47750118',
'AK47750128',
'AK47750136',
'AK47750138',
'AK47750160',
'AK47750169',
'AK47750175',
'AK47750180',
'AK47750185',
'AK47750186',
'AK47750188',
'AK47750189',
'AK47750190',
'AK47750197',
'AK47750200',
'AK47775083',
'AK47775098',
'AK47775102',
'AK47809680',
'AK47809689',
'AK47809715',
'AK47809746',
'AK47809772',
'AK47809803',
'AK47809883',
'AK47809885',
'AK47809931',
'AK47809958',
'AK47809980',
'AK47837036',
'AK47837037',
'AK47837039',
'AK47837041',
'AK47837042',
'AK47837043',
'AK47837045',
'AK47856474',
'AK47856483',
'AK47856499',
'AK47856523',
'AK47871351',
'AK47871358',
'AK47871366',
'AK47871389',
'AK47871391',
'AK47871405',
'AK47871430',
'AK47871505',
'AK47884690',
'AK47884692',
'AK47884694',
'AK47884709',
'AK47884733',
'AK47884766',
'AK47884772',
'AK47884781',
'AK47884782',
'AK47884794',
'AK47884824',
'AK47884859',
'AK47884915',
'AK47884942',
'AK47913182',
'AK47913201',
'AK47913203',
'AK47913206',
'AK47913213',
'AK47913221',
'AK47913222',
'AK47913232',
'AK47913237',
'AK47913240',
'AK47913244',
'AK47913247',
'AK47913262',
'AK47913281',
'AK47913297',
'AK47913300',
'AK47913326',
'AK47913351',
'AK47913353',
'AK47913359',
'AK47913374',
'AK47913376',
'AK47913387',
'AK47913407',
'AK47913414',
'AK47913418',
'AK47913434',
'AK47913451',
'AK47913486',
'AK47913489',
'AK47913500',
'AK47913502',
'AK47913513',
'AK47913535',
'AK47913560',
'AK47913570',
'AK47913578',
'AK47913587',
'AK47948395',
'AK47948406',
'AK47948409',
'AK47948412',
'AK47948465',
'AK47948485',
'AK47948507',
'AK47948525',
'AK47948565',
'AK47948568',
'AK47948577',
'AK47948622',
'AK47948629',
'AK47948638',
'AK47948653',
'AK47948656',
'AK47948663',
'AK47948687',
'AK47948692',
'AK47948694',
'AK47948731',
'AK48026403',
'AK48026428',
'AK48026442',
'AK48026445',
'AK48026460',
'AK48026484',
'AK48026520',
'AK48026560',
'AK48026562',
'AK48026572',
'AK48026579',
'AK48026601',
'AK48026624',
'AK48026634',
'AK48026645',
'AK48026719',
'AK48026833',
'AK48026873',
'AK48026909',
'AK48026965',
'AK48026987',
'AK48027017',
'AK48027027',
'AK48027062',
'AK48066158',
'AK48066173',
'AK48066181',
'AK48066190',
'AK48066199',
'AK48066203',
'AK48066234',
'AK48066236',
'AK48066246',
'AK48066248',
'AK48066253',
'AK48066274',
'AK48066278',
'AK48066295',
'AK48066297',
'AK48066339',
'AK48066403',
'AK48066419',
'AK48089997',
'AK48090005',
'AK48090011',
'AK48090019',
'AK48090021',
'AK48090024',
'AK48090034',
'AK48090045',
'AK48090046',
'AK48090047',
'AK48090053',
'AK48090060',
'AK48090095',
'AK48090097',
'AK48090098',
'AK48090113',
'AK48090124',
'AK48090132',
'AK48090139',
'AK48090143',
'AK48090144',
'AK48090150',
'AK48090151',
'AK48090176',
'AK48090193',
'AK48090203',
'AK48090206',
'AK48090210',
'AK48090212',
'AK48090213',
'AK48090230',
'AK48090272',
'AK48090298',
'AK48090299',
'AK48090303',
'AK48090306',
'AK48090313',
'AK48090314',
'AK48090320',
'AK48090323',
'AK48090331',
'AK48090332',
'AK48090338',
'AK48090348',
'AK48090349',
'AK48090373',
'AK48107581',
'AK48107583',
'AK48107605',
'AK48107641',
'AK48107644',
'AK48107656',
'AK48107661',
'AK48107666',
'AK48107672',
'AK48107680',
'AK48107699',
'AK48107701',
'AK48107708',
'AK48107717',
'AK48107735',
'AK48107772',
'AK48107783',
'AK48107787',
'AK48107816',
'AK48107818',
'AK48107824',
'AK48107835',
'AK48107844',
'AK48107886',
'AK48107903',
'AK48124698',
'AK48124719',
'AK48124746',
'AK48124749',
'AK48124758',
'AK48124771',
'AK48124774',
'AK48124776',
'AK48124786',
'AK48124808',
'AK48124809',
'AK48124816',
'AK48124819',
'AK48124845',
'AK48124860',
'AK48124864',
'AK48124866',
'AK48124874',
'AK48124895',
'AK48124907',
'AK48124915',
'AK48124921',
'AK48124923',
'AK48124927',
'AK48124935',
'AK48124955',
'AK48124972',
'AK48124974',
'AK48152183',
'AK48152219',
'AK48152223',
'AK48152232',
'AK48152237',
'AK48152249',
'AK48152276',
'AK48152277',
'AK48152281',
'AK48152283',
'AK48152284',
'AK48152298',
'AK48152311',
'AK48152372',
'AK48152377',
'AK48152383',
'AK48152428',
'AK48152433',
'AK48152435',
'AK48152440',
'AK48152442',
'AK48186314',
'AK48186316',
'AK48186317',
'AK48186318',
'AK48186319',
'AK48186321',
'AK48186322',
'AK48186326',
'AK48186328',
'AK48186331',
'AK48186332',
'AK48186338',
'AK48186341',
'AK48186343',
'AK48186344',
'AK48186345',
'AK48186348',
'AK48186349',
'AK48209796',
'AK48209803',
'AK48209807',
'AK48209813',
'AK48209815',
'AK48209820',
'AK48209821',
'AK48209832',
'AK48209845',
'AK48209871',
'AK48209922',
'AK48209936',
'AK48209944',
'AK48209992',
'AK48210000',
'AK48210013',
'AK48210037',
'AK48210040',
'AK48210044',
'AK48210053',
'AK48210088',
'AK48210092',
'AK48210129',
'AK48240834',
'AK48240837',
'AK48240841',
'AK48240867',
'AK48240868',
'AK48240897',
'AK48240899',
'AK48240909',
'AK48240912',
'AK48240914',
'AK48240919',
'AK48240934',
'AK48240955',
'AK48240983',
'AK48240993',
'AK48240999',
'AK48271333',
'AK48271340',
'AK48271361',
'AK48271375',
'AK48271405',
'AK48271416',
'AK48271436',
'AK48271480',
'AK48271488',
'AK48271508',
'AK48271512',
'AK48271531',
'AK48271584',
'AK48271598',
'AK48271615',
'AK48271618',
'AK48271630',
'AK48271641',
'AK48271649',
'AK48271653',
'AK48271656',
'AK48271671',
'AK48271674',
'AK48271677',
'AK48271688',
'AK48271707',
'AK48271710',
'AK48271721',
'AK48271763',
'AK48271770',
'AK48271771',
'AK48271784',
'AK48286899',
'AK48286907',
'AK48286939',
'AK48286945',
'AK48286947',
'AK48286962',
'AK48286984',
'AK48287003',
'AK48287027',
'AK48287082',
'AK48287088',
'AK48287091',
'AK48287117',
'AK48287125',
'AK48325735',
'AK48325760',
'AK48325762',
'AK48325767',
'AK48325793',
'AK48325809',
'AK48325813',
'AK48325814',
'AK48325832',
'AK48354189',
'AK48354191',
'AK48354192',
'AK48354193',
'AK48354195',
'AK48354196',
'AK48354198',
'AK48354199',
'AK48354201',
'AK48354203',
'AK48354204',
'AK48354206',
'AK48354207',
'AK48354211',
'AK48354212',
'AK48354213',
'AK48354215',
'AK48354216',
'AK48354218',
'AK48354219',
'AK48354220',
'AK48388903',
'AK48388905',
'AK48388920',
'AK48388925',
'AK48388931',
'AK48388933',
'AK48388970',
'AK48389047',
'AK48389074',
'AK48389082',
'AK48389123',
'AK48389237',
'AK48389273',
'AK48389297',
'AK48404270',
'AK48404438',
'AK48404469',
'AK48404532',
'AK48419897',
'AK48419908',
'AK48419915',
'AK48419970',
'AK48419985',
'AK48419991',
'AK48420004',
'AK48420007',
'AK48420022',
'AK48420041',
'AK48420043',
'AK48420045',
'AK48420050',
'AK48420051',
'AK48420056',
'AK48420058',
'AK48420059',
'AK48420060',
'AK48420063',
'AK48420073',
'AK48420078',
'AK48420087',
'AK48420099',
'AK48420108',
'AK48420116',
'AK48420128',
'AK48420134',
'AK48420151',
'AK48420167',
'AK48420169',
'AK48437665',
'AK48437782',
'AK48437802',
'AK48437804',
'AK48437807',
'AK48437819',
'AK48437820',
'AK48437826',
'AK48437834',
'AK48437852',
'AK48437878',
'AK48437886',
'AK48437899',
'AK48437913',
'AK48437928',
'AK48437955',
'AK48442143',
'AK48457032',
'AK48457034',
'AK48457039',
'AK48457042',
'AK48457044',
'AK48457047',
'AK48457048',
'AK48457050',
'AK48457052',
'AK48457057',
'AK48484922',
'AK48484947',
'AK48484957',
'AK48484961',
'AK48484965',
'AK48484968',
'AK48484974',
'AK48484976',
'AK48484985',
'AK48553858',
'AK48186340',
'AK48345357',
'AK48395521',
'AK48425673',
'AK48448598',
'AK48455431',
'AK48529093',
'AK47825567',
'AK47948712',
'AK47275219',
'AK48173248',
'AK48254663',
'AK48258302',
'AK48262330',
'AK48272539',
'AK48276641',
'AK48276725',
'AK48298970',
'AK48312616',
'AK48339412',
'AK48404072',
'AK48438373',
'AK48498499',
'AK48506632',
'AK48511121',
'AK48515737',
'AB47980147',
'AB47980156',
'AB47980224',
'AB47980250',
'AB47980446',
'AB47980466',
'AB47981724',
'AB47982261',
'AB47982280',
'AB47982285',
'AB47982534',
'AB47983276',
'AB47983300',
'AB47983313',
'AB47983325',
'AB47983331',
'AB47983340',
'AB47983434',
'AB47983509',
'AB47983743',
'AB47983827',
'AB47983962',
'AB47984095',
'AB47984514',
'AB47984666',
'AB47984672',
'AB47984749',
'AB47984842',
'AB47985357',
'AB47985517',
'AB47986188',
'AB47986273',
'AB47986287',
'AB47986343',
'AB47986429',
'AB47986905',
'AB47986983',
'AB47986990',
'AB47987066',
'AB47987600',
'AB47987621',
'AB47987673',
'AB47988367',
'AB47988716',
'AB47988752',
'AB47989040',
'AB47989276',
'AB47989279',
'AB47989426',
'AK48135818',
'AK47819645',
'AK47819687',
'AK47896742',
'AK47921798',
'AK47921805',
'AK47921852',
'AK48075400',
'AK48137371',
'AK48137567',
'AK48137702',
'AK48224451',
'AK48308100',
'AK48335817',
'AK48501315',
'AK48501381',
'AK48501974',
'AK48530562',
'AK48584962',
'AK48109702',
'AK48344435',
'AK47613546',
'AK47820232',
'AK47840450',
'AK47897694',
'AK47978480',
'AK48046369',
'AK48168075',
'AK48225469',
'AK48463573',
'AK47619326',
'AK47685178',
'AK47713182',
'AK47862142',
'AK47910965',
'AK48025747',
'AK48100276',
'AK48171744',
'AK48179244',
'AK48179267',
'AK48179335',
'AK48181354',
'AK48200193',
'AK48289312',
'AK48420744',
'AK48454320',
'AK47812837',
'AK47936318',
'AK48054740',
'AK48054771',
'AK48054802',
'AK48143695',
'AK48143696',
'AK47093384',
'AK47739124',
'AK47750698',
'AK47750700',
'AK48066994',
'AK48090865',
'AK48210712',
'AK48210715',
'AK48272337',
'AK48272344',
'AK48272352',
'AK48272356',
'AK47406018',
'AK47429662',
'AK47429667',
'AK47461522',
'AK47512313',
'AK47528379',
'AK47572008',
'AK47597387',
'AK47597391',
'AK47597397',
'AK47661233',
'AK47687073',
'AK47687077',
'AK47721055',
'AK47721056',
'AK47721057',
'AK47721063',
'AK47750062',
'AK47809940',
'AK47809948',
'AK47809949',
'AK47833686',
'AK47833694',
'AK47856585',
'AK47856593',
'AK47885115',
'AK47885120',
'AK47913597',
'AK47913598',
'AK47948740',
'AK48026818',
'AK48026820',
'AK48027032',
'AK48066356',
'AK48090105',
'AK48107933',
'AK48107938',
'AK48125010',
'AK48152417',
'AK48185345',
'AK48241131',
'AK48241133',
'AK48271502',
'AK48287157',
'AK48287160',
'AK48325927',
'AK48325929',
'AK48389175',
'AK48389176',
'AK48404399',
'AB46769049',
'AK48290998',
'AK48395950',
'AK47487886',
'AK47731226',
'AK47978186',
'AK48540971',
'AK46229274',
'AK48582456',
'AK48582462',
'AK48582468',
'AK48582497',
'AK48582517',
'AK48582535',
'AK48582565',
'AK48582591',
'AK48582605',
'AK48582662',
'AK48697501',
'AK48726151',
'AK48726163',
'AK48726175',
'AK48770536',
'AK48963198',
'AK48963222',
'AK48979843',
'AK48979896',
'AK49028623',
'AK49145031',
'AK49145147',
'AK49145198',
'AK49282139',
'AK49282145',
'AK49282151',
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
$this->logger->info('_data_patch_20201111_1750_CancelRegister.php end');

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
