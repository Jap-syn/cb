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
$this->logger->info('_data_patch_20200514_1000_CancelRegister.php start');

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
'AK43910000',
'AK43871000',
'AK43846000',
'AK43949100',
'AK43852300',
'AK43890400',
'AK43892400',
'AK43865400',
'AK43947400',
'AK43890500',
'AK43851500',
'AK43850600',
'AK43886700',
'AK43863800',
'AK43868800',
'AK43914900',
'AK43887900',
'AK43848900',
'AK43903010',
'AK43950110',
'AK43890110',
'AK43861110',
'AK43840210',
'AK43910310',
'AK44061310',
'AK43914310',
'AK43854310',
'AK43899410',
'AK43840510',
'AK43904510',
'AK43906510',
'AK43848510',
'AK43895610',
'AK43916610',
'AK43866610',
'AK43905810',
'AK43916810',
'AK43900910',
'AK43910020',
'AK43910120',
'AK43908120',
'AK43904220',
'AK43950320',
'AK43949320',
'AK43909420',
'AK43843520',
'AK43947520',
'AK43897520',
'AK43873620',
'AK43916620',
'AK43947720',
'AK43853820',
'AK43908820',
'AK43858820',
'AK43909820',
'AK43860920',
'AK43842030',
'AK43907030',
'AK43852230',
'AK43900330',
'AK43850330',
'AK43913330',
'AK43899330',
'AK43892630',
'AK43843630',
'AK43153630',
'AK43896630',
'AK43888630',
'AK43901730',
'AK43852730',
'AK43894040',
'AK43909040',
'AK43848340',
'AK43898340',
'AK43851440',
'AK43903440',
'AK43873440',
'AK43885440',
'AK43843540',
'AK43947540',
'AK43897540',
'AK43910640',
'AK43947640',
'AK43849640',
'AK43859840',
'AK43841940',
'AK43915940',
'AK43946940',
'AK43952150',
'AK43917150',
'AK43842250',
'AK43845250',
'AK43840350',
'AK43912450',
'AK43885450',
'AK43874550',
'AK43894550',
'AK43947550',
'AK43914650',
'AK43866650',
'AK43847650',
'AK43901750',
'AK43862750',
'AK43843750',
'AK43890850',
'AK43853850',
'AK43914950',
'AK43890060',
'AK43849060',
'AK43949160',
'AK43842260',
'AK43909260',
'AK43898360',
'AK43951460',
'AK43902560',
'AK43910660',
'AK43951660',
'AK43913660',
'AK43868760',
'AK43915960',
'AK43866960',
'AK43867960',
'AK43901070',
'AK43840170',
'AK43895170',
'AK43877170',
'AK43888170',
'AK43905370',
'AK43143470',
'AK43895570',
'AK43846570',
'AK43897670',
'AK43890770',
'AK43851770',
'AK43886770',
'AK43909770',
'AK43910870',
'AK43948870',
'AK43864080',
'AK43909080',
'AK43903180',
'AK43907180',
'AK43848180',
'AK43860280',
'AK43864280',
'AK43886280',
'AK43949280',
'AK43896380',
'AK43909380',
'AK43899380',
'AK43851480',
'AK43904480',
'AK43894580',
'AK43901680',
'AK43893780',
'AK43848780',
'AK43910090',
'AK43861090',
'AK43912090',
'AK43908090',
'AK43895190',
'AK43916190',
'AK43899190',
'AK43849290',
'AK43869290',
'AK43889290',
'AK43899290',
'AK43903390',
'AK43916390',
'AK43891490',
'AK43847490',
'AK43897490',
'AK43845590',
'AK43910690',
'AK43856690',
'AK43842790',
'AK43952790',
'AK43914790',
'AK43946790',
'AK43915890',
'AK43886890',
'AK43853990',
'AK43854990',
'AK43846990',
'AK43912101',
'AK43908101',
'AK43849201',
'AK43840301',
'AK43851301',
'AK43893401',
'AK43851501',
'AK43843501',
'AK43853501',
'AK43899501',
'AK43843601',
'AK43884601',
'AK43864901',
'AK43902111',
'AK44061211',
'AK43900311',
'AK43873311',
'AK43895411',
'AK43843511',
'AK43847511',
'AK43911811',
'AK43948811',
'AK43913911',
'AK43863911',
'AK43905911',
'AK43859911',
'AK43910021',
'AK43903021',
'AK43886021',
'AK43917121',
'AK43857121',
'AK43914321',
'AK43909321',
'AK43911421',
'AK43912421',
'AK43868421',
'AK43843521',
'AK43893521',
'AK43947621',
'AK43915821',
'AK43946821',
'AK43911921',
'AK43842921',
'AK43860131',
'AK43898131',
'AK43911231',
'AK43854231',
'AK43950331',
'AK43852531',
'AK43916531',
'AK43892631',
'AK43869631',
'AK43888831',
'AK43909831',
'AK43910041',
'AK43913041',
'AK43894041',
'AK43910141',
'AK43873141',
'AK43848141',
'AK43909141',
'AK43912241',
'AK43948241',
'AK43949241',
'AK43910441',
'AK43910641',
'AK43848641',
'AK43893741',
'AK43895741',
'AK43950941',
'AK43842051',
'AK43890151',
'AK43898151',
'AK43910251',
'AK43845251',
'AK43854351',
'AK43894351',
'AK43915351',
'AK43848351',
'AK43902451',
'AK43907451',
'AK43854751',
'AK43850851',
'AK43860061',
'AK43843061',
'AK43872161',
'AK43898161',
'AK43949161',
'AK43915261',
'AK43841361',
'AK43913361',
'AK43854361',
'AK43906561',
'AK43884661',
'AK43841861',
'AK43911961',
'AK43895961',
'AK43946961',
'AK43899961',
'AK43890171',
'AK43845171',
'AK43910271',
'AK43906271',
'AK43850371',
'AK43947471',
'AK43910571',
'AK43851571',
'AK43868571',
'AK43910671',
'AK43852671',
'AK43894871',
'AK43866081',
'AK43848081',
'AK43850181',
'AK43953181',
'AK43888181',
'AK43844281',
'AK43878281',
'AK43900381',
'AK43871381',
'AK43852381',
'AK43888481',
'AK43912581',
'AK43847581',
'AK43898681',
'AK43154781',
'AK43948781',
'AK43949781',
'AK43916881',
'AK43911981',
'AK43912981',
'AK43842981',
'AK43853981',
'AK43899981',
'AK43852091',
'AK43874091',
'AK43843191',
'AK43917191',
'AK43894291',
'AK43916291',
'AK43909291',
'AK43840391',
'AK43902491',
'AK43843491',
'AK43947491',
'AK43908491',
'AK43853591',
'AK43893591',
'AK43854591',
'AK43842691',
'AK43892691',
'AK43909691',
'AK43846791',
'AK43947791',
'AK43860891',
'AK43862891',
'AK43888891',
'AK43853991',
'AK43845991',
'AK43916991',
'AK43848991',
'AK43912102',
'AK43904102',
'AK43849102',
'AK43910202',
'AK43905302',
'AK43915402',
'AK43909402',
'AK43904502',
'AK43864502',
'AK43899502',
'AK43854602',
'AK43910702',
'AK43851702',
'AK43901802',
'AK43884802',
'AK43946902',
'AK43891112',
'AK43893212',
'AK43885212',
'AK43906312',
'AK43912412',
'AK43858812',
'AK43901912',
'AK43894022',
'AK43853122',
'AK43949122',
'AK43840222',
'AK43852222',
'AK43846222',
'AK43850322',
'AK43903322',
'AK43910422',
'AK43864422',
'AK43242722',
'AK43907722',
'AK43947722',
'AK43841822',
'AK43914822',
'AK43908822',
'AK43854922',
'AK43915922',
'AK43950132',
'AK43844132',
'AK43849132',
'AK43841332',
'AK43853332',
'AK43845432',
'AK43913532',
'AK43908532',
'AK43909532',
'AK43849632',
'AK43841832',
'AK43142832',
'AK43905832',
'AK43913042',
'AK43844142',
'AK43917142',
'AK43893242',
'AK43899342',
'AK43902442',
'AK43904442',
'AK43907542',
'AK43844642',
'AK43895742',
'AK43900842',
'AK43912842',
'AK43853842',
'AK43910942',
'AK43845052',
'AK43915252',
'AK43845352',
'AK43900452',
'AK43951452',
'AK43844452',
'AK43864452',
'AK43910552',
'AK43850552',
'AK43947552',
'AK43908552',
'AK43910652',
'AK43861652',
'AK43914652',
'AK43842752',
'AK43846752',
'AK43852852',
'AK43847852',
'AK43842952',
'AK43908952',
'AK43902062',
'AK43863062',
'AK43947162',
'AK43909162',
'AK43903262',
'AK43894362',
'AK43951462',
'AK43888462',
'AK43902562',
'AK43853562',
'AK43895562',
'AK43861662',
'AK43846662',
'AK43848662',
'AK43952762',
'AK43869862',
'AK43862962',
'AK43915962',
'AK43856962',
'AK43948962',
'AK43164072',
'AK43896072',
'AK43909172',
'AK43953272',
'AK43863272',
'AK43843372',
'AK43915372',
'AK43901472',
'AK43849472',
'AK43949472',
'AK43914672',
'AK43895672',
'AK43947672',
'AK43946772',
'AK43842872',
'AK43894872',
'AK43895082',
'AK43852182',
'AK43892382',
'AK43907382',
'AK43848382',
'AK43868382',
'AK43892482',
'AK43952782',
'AK43914782',
'AK43844882',
'AK43905882',
'AK43915882',
'AK43844982',
'AK43864982',
'AK43847982',
'AK43910092',
'AK43851092',
'AK43842092',
'AK43842192',
'AK43863192',
'AK43906192',
'AK43840292',
'AK43890292',
'AK43914292',
'AK43854292',
'AK43889292',
'AK43900392',
'AK43891392',
'AK43853392',
'AK43230492',
'AK43902492',
'AK43906592',
'AK43861792',
'AK43890892',
'AK43844892',
'AK43898892',
'AK43909892',
'AK43900992',
'AK43897992',
'AK43897203',
'AK43855303',
'AK43916303',
'AK43848303',
'AK43909303',
'AK43891403',
'AK43892403',
'AK43843403',
'AK43885403',
'AK43949403',
'AK43899403',
'AK43947503',
'AK43900603',
'AK43845603',
'AK43901803',
'AK43844803',
'AK43915803',
'AK43885803',
'AK43851903',
'AK43891903',
'AK43901013',
'AK43913013',
'AK43900213',
'AK43893213',
'AK43871313',
'AK43909313',
'AK43843413',
'AK43916413',
'AK43873713',
'AK43843813',
'AK43844913',
'AK43909913',
'AK43901023',
'AK43851023',
'AK43843023',
'AK43915023',
'AK43858023',
'AK43890123',
'AK43857123',
'AK43948123',
'AK43840223',
'AK43855223',
'AK43913323',
'AK43844323',
'AK43906323',
'AK43949323',
'AK43891423',
'AK43892423',
'AK43218423',
'AK43910523',
'AK43843523',
'AK43864523',
'AK43915723',
'AK43946723',
'AK43845823',
'AK43149823',
'AK43911923',
'AK43913923',
'AK43844033',
'AK43865033',
'AK43916033',
'AK43910133',
'AK43904333',
'AK43914333',
'AK43854333',
'AK43885333',
'AK43916333',
'AK43847333',
'AK43909333',
'AK43840533',
'AK43947533',
'AK43862633',
'AK43910733',
'AK43905733',
'AK43896733',
'AK43901043',
'AK43903043',
'AK43842143',
'AK43955143',
'AK43885143',
'AK43846143',
'AK43876143',
'AK43917143',
'AK43840243',
'AK43902243',
'AK43891343',
'AK43907343',
'AK43852443',
'AK43843443',
'AK43891743',
'AK43915843',
'AK43845843',
'AK43946843',
'AK43911943',
'AK43907943',
'AK43847943',
'AK43947943',
'AK43910253',
'AK43913253',
'AK43886253',
'AK43909253',
'AK44061353',
'AK43914353',
'AK43889353',
'AK43140453',
'AK43911453',
'AK43842453',
'AK43894453',
'AK43949453',
'AK43850553',
'AK43849553',
'AK43899553',
'AK43910653',
'AK43851653',
'AK43915853',
'AK43855853',
'AK43916853',
'AK43894953',
'AK43915953',
'AK43844163',
'AK43864163',
'AK43907163',
'AK43910263',
'AK43914263',
'AK43891763',
'AK43844763',
'AK43850863',
'AK43915863',
'AK43915963',
'AK43899963',
'AK43842073',
'AK43905073',
'AK43948173',
'AK43860273',
'AK43914273',
'AK43906373',
'AK43897373',
'AK43910573',
'AK43902573',
'AK43848673',
'AK43869673',
'AK43862773',
'AK43913773',
'AK43893773',
'AK43849773',
'AK43910873',
'AK43851873',
'AK43894873',
'AK43913973',
'AK43854973',
'AK43847973',
'AK43909973',
'AK43840083',
'AK43898083',
'AK43889083',
'AK43907183',
'AK43864283',
'AK43902383',
'AK43906383',
'AK43905483',
'AK43871883',
'AK43893883',
'AK43884983',
'AK43912093',
'AK43846093',
'AK43900193',
'AK43902193',
'AK43893193',
'AK43917193',
'AK43858193',
'AK43872293',
'AK43901393',
'AK43848393',
'AK43916493',
'AK43951593',
'AK43916593',
'AK43890793',
'AK43952793',
'AK43946793',
'AK43948793',
'AK43905893',
'AK43898893',
'AK43914993',
'AK43892004',
'AK43843004',
'AK43845004',
'AK43912104',
'AK43908104',
'AK43894204',
'AK43908204',
'AK43850304',
'AK43841404',
'AK43894404',
'AK43910504',
'AK43902504',
'AK43892504',
'AK43905504',
'AK43913604',
'AK43893604',
'AK43895604',
'AK43860704',
'AK43911704',
'AK43913704',
'AK43884704',
'AK43946804',
'AK43909804',
'AK43851904',
'AK43858114',
'AK43915214',
'AK43914314',
'AK43861414',
'AK43147414',
'AK43849414',
'AK43897514',
'AK43908514',
'AK43901614',
'AK43841714',
'AK43895714',
'AK43908714',
'AK43900814',
'AK43844814',
'AK43907814',
'AK43947814',
'AK43843914',
'AK43915914',
'AK43948914',
'AK43910024',
'AK43844024',
'AK43915024',
'AK43865024',
'AK43234124',
'AK43917124',
'AK43898124',
'AK43914324',
'AK43894324',
'AK43908324',
'AK43948324',
'AK43949424',
'AK43896524',
'AK43916624',
'AK43910724',
'AK43217724',
'AK43848724',
'AK43849724',
'AK43911824',
'AK43863824',
'AK43907824',
'AK43899824',
'AK43850924',
'AK43846924',
'AK43948924',
'AK43901034',
'AK43842034',
'AK43879034',
'AK43851134',
'AK43845134',
'AK43907134',
'AK43909134',
'AK43911234',
'AK43863234',
'AK43845234',
'AK43841334',
'AK43916334',
'AK43908334',
'AK43886534',
'AK43889534',
'AK43851734',
'AK43911834',
'AK43946834',
'AK43916044',
'AK43898044',
'AK43902244',
'AK43903244',
'AK43845244',
'AK43898244',
'AK43916344',
'AK43854444',
'AK43908444',
'AK43847544',
'AK43899544',
'AK43916644',
'AK43868644',
'AK43843744',
'AK43914744',
'AK43899944',
'AK43902054',
'AK43842154',
'AK43852154',
'AK43853154',
'AK43914254',
'AK43909254',
'AK43899254',
'AK43915654',
'AK43893754',
'AK43915754',
'AK43911954',
'AK43885954',
'AK43910164',
'AK43891264',
'AK43910364',
'AK43840364',
'AK43885364',
'AK43898364',
'AK43891564',
'AK43892564',
'AK43898564',
'AK43862764',
'AK43858764',
'AK43902964',
'AK43914964',
'AK43913074',
'AK43886074',
'AK43898074',
'AK43853174',
'AK43843274',
'AK43893274',
'AK43916274',
'AK43885474',
'AK43909474',
'AK43895574',
'AK43947574',
'AK43851674',
'AK43914674',
'AK43854874',
'AK43907874',
'AK43908874',
'AK43909974',
'AK43873084',
'AK43844084',
'AK43915084',
'AK43860184',
'AK43866184',
'AK43849184',
'AK43889284',
'AK43910384',
'AK43951384',
'AK43847584',
'AK43850784',
'AK43916784',
'AK43946784',
'AK43875884',
'AK43856884',
'AK43898884',
'AK43846984',
'AK43899984',
'AK43842194',
'AK43847194',
'AK43845394',
'AK43949394',
'AK43912594',
'AK43884594',
'AK43948594',
'AK43242694',
'AK43895794',
'AK43906794',
'AK43946794',
'AK44060994',
'AK43906994',
'AK43888994',
'AK43917005',
'AK43849005',
'AK43886105',
'AK43849105',
'AK43849205',
'AK43842305',
'AK43915405',
'AK43845405',
'AK43860505',
'AK43892505',
'AK43885505',
'AK43906505',
'AK43897505',
'AK43843605',
'AK43905605',
'AK43916605',
'AK43846705',
'AK43896705',
'AK43898705',
'AK43905805',
'AK43902015',
'AK43858015',
'AK43912115',
'AK43894115',
'AK43844215',
'AK43897215',
'AK43871315',
'AK43843415',
'AK43848415',
'AK43900515',
'AK43898515',
'AK43854615',
'AK43855615',
'AK43895615',
'AK43947615',
'AK43861715',
'AK43843715',
'AK43896715',
'AK43895815',
'AK43906815',
'AK43946815',
'AK43892025',
'AK43910125',
'AK43842325',
'AK43852325',
'AK43843325',
'AK43915325',
'AK43845325',
'AK43915425',
'AK43916425',
'AK43909425',
'AK43853825',
'AK43893825',
'AK43914925',
'AK43955035',
'AK43952135',
'AK43844235',
'AK43864235',
'AK43851335',
'AK43907335',
'AK43909435',
'AK43840535',
'AK43842535',
'AK43905535',
'AK43891635',
'AK43897635',
'AK43910735',
'AK43897735',
'AK43885935',
'AK43910045',
'AK43898045',
'AK43897145',
'AK43843245',
'AK43914345',
'AK43845645',
'AK43895745',
'AK43911945',
'AK43947945',
'AK43909945',
'AK43901055',
'AK43907255',
'AK43905355',
'AK43948355',
'AK43888355',
'AK43909355',
'AK43840455',
'AK43914455',
'AK43947555',
'AK43871755',
'AK43900855',
'AK43870855',
'AK43912855',
'AK43843855',
'AK43946855',
'AK43910065',
'AK43902065',
'AK43845065',
'AK43917065',
'AK43951165',
'AK43917165',
'AK43844265',
'AK43947265',
'AK43842365',
'AK43913365',
'AK43844365',
'AK43948365',
'AK43949365',
'AK43914465',
'AK43910665',
'AK43909665',
'AK43849665',
'AK43951865',
'AK43893865',
'AK43911965',
'AK43892075',
'AK43855075',
'AK43843175',
'AK43856175',
'AK43917175',
'AK43891275',
'AK43914275',
'AK43845275',
'AK43909275',
'AK43840575',
'AK43911575',
'AK43852575',
'AK43893575',
'AK43894675',
'AK43885675',
'AK43217675',
'AK43884775',
'AK43861875',
'AK44060975',
'AK43860975',
'AK43847975',
'AK43901085',
'AK43906085',
'AK43842185',
'AK43917185',
'AK43840285',
'AK43845285',
'AK43916385',
'AK43908485',
'AK43948485',
'AK43887585',
'AK43846685',
'AK43913885',
'AK43909985',
'AK43911195',
'AK43910295',
'AK43912395',
'AK43845395',
'AK43846495',
'AK43861595',
'AK43907595',
'AK43872695',
'AK43863695',
'AK43894795',
'AK43915795',
'AK43900995',
'AK43894995',
'AK43856006',
'AK43947006',
'AK43869006',
'AK43890106',
'AK43912106',
'AK43854106',
'AK43909106',
'AK43912206',
'AK43848206',
'AK43949206',
'AK43844306',
'AK43885406',
'AK43906406',
'AK43841506',
'AK43845506',
'AK43952706',
'AK43868706',
'AK43842806',
'AK43864806',
'AK43850906',
'AK44061016',
'AK43894116',
'AK43895116',
'AK43848216',
'AK43899216',
'AK43904316',
'AK43915316',
'AK43910416',
'AK43894416',
'AK43845416',
'AK43952516',
'AK43864516',
'AK43899516',
'AK43888716',
'AK43889716',
'AK43913916',
'AK43900026',
'AK43886126',
'AK43916226',
'AK43848326',
'AK43889326',
'AK43910526',
'AK43852526',
'AK43913526',
'AK43905526',
'AK43865526',
'AK43908526',
'AK43853626',
'AK43849626',
'AK43906726',
'AK43946826',
'AK43854926',
'AK43946926',
'AK43896036',
'AK43910136',
'AK43860136',
'AK43885136',
'AK43911236',
'AK43868236',
'AK43910336',
'AK43850336',
'AK43911336',
'AK43952336',
'AK43891436',
'AK43843536',
'AK43914536',
'AK43844636',
'AK43916636',
'AK43908636',
'AK43844736',
'AK43845736',
'AK43915836',
'AK43895836',
'AK43849836',
'AK43852936',
'AK43894936',
'AK43910046',
'AK43902046',
'AK43914046',
'AK43911146',
'AK43844146',
'AK43917146',
'AK43949146',
'AK43844246',
'AK43854246',
'AK43947246',
'AK43948346',
'AK43897546',
'AK43868546',
'AK43899546',
'AK43902746',
'AK43893746',
'AK43900946',
'AK43892056',
'AK43874156',
'AK43910256',
'AK43950256',
'AK43852256',
'AK43901356',
'AK43947356',
'AK43849356',
'AK43862456',
'AK43870556',
'AK43911556',
'AK43913656',
'AK43896656',
'AK43949656',
'AK43912856',
'AK43896856',
'AK43900956',
'AK43841956',
'AK43851956',
'AK43948956',
'AK43910066',
'AK43910166',
'AK43843166',
'AK43917166',
'AK43947166',
'AK43862266',
'AK43910466',
'AK43842466',
'AK43845466',
'AK43947566',
'AK43899566',
'AK43912666',
'AK43907666',
'AK43947666',
'AK43855766',
'AK43843866',
'AK43913966',
'AK43889076',
'AK43907176',
'AK43893276',
'AK43865276',
'AK43848276',
'AK43860376',
'AK43911376',
'AK43915376',
'AK43949376',
'AK43851476',
'AK43852476',
'AK43907476',
'AK43867476',
'AK43915676',
'AK43893776',
'AK43916776',
'AK43867776',
'AK43951876',
'AK43853876',
'AK43853976',
'AK43890086',
'AK43843086',
'AK43898086',
'AK43854186',
'AK43917186',
'AK43897186',
'AK43844286',
'AK43856386',
'AK43900486',
'AK43872486',
'AK43894586',
'AK43906586',
'AK43847586',
'AK43845686',
'AK43848686',
'AK43865786',
'AK43853886',
'AK43947886',
'AK43911986',
'AK43854986',
'AK43861096',
'AK43902096',
'AK43908096',
'AK43850196',
'AK43855196',
'AK43904496',
'AK43217496',
'AK43952596',
'AK43862596',
'AK43845596',
'AK43916596',
'AK43886596',
'AK43952696',
'AK43948796',
'AK43951896',
'AK43846896',
'AK43844007',
'AK43848007',
'AK43849007',
'AK43912107',
'AK43908107',
'AK43849107',
'AK43950207',
'AK43843207',
'AK43904207',
'AK43947207',
'AK43905307',
'AK43863407',
'AK43854407',
'AK43915407',
'AK43910507',
'AK43870507',
'AK43894507',
'AK43893607',
'AK43842707',
'AK43848707',
'AK43913807',
'AK43843907',
'AK43910017',
'AK43901017',
'AK43909117',
'AK43852317',
'AK43854317',
'AK43894317',
'AK43915417',
'AK43904517',
'AK43947517',
'AK43901617',
'AK43913717',
'AK43916717',
'AK43946717',
'AK43948917',
'AK43855027',
'AK43914127',
'AK43894127',
'AK43840227',
'AK43896227',
'AK43907227',
'AK43845327',
'AK43849327',
'AK43843427',
'AK43894427',
'AK43905427',
'AK43840527',
'AK43851527',
'AK43842627',
'AK43885627',
'AK43863727',
'AK43144727',
'AK43847727',
'AK43862827',
'AK43915827',
'AK43946827',
'AK43892927',
'AK43894927',
'AK43896927',
'AK43948927',
'AK43909927',
'AK43916037',
'AK43910137',
'AK43885137',
'AK43913337',
'AK43871437',
'AK43853437',
'AK43897537',
'AK43899537',
'AK43853637',
'AK43914837',
'AK43884837',
'AK43915937',
'AK43908937',
'AK43891047',
'AK43850247',
'AK43908247',
'AK43902447',
'AK43890847',
'AK43912847',
'AK43152947',
'AK43946947',
'AK43914157',
'AK43917157',
'AK43895257',
'AK43948257',
'AK43848457',
'AK43948457',
'AK43909457',
'AK43893557',
'AK43947557',
'AK43858557',
'AK43842657',
'AK43895657',
'AK43947657',
'AK43848657',
'AK43841757',
'AK43853857',
'AK43863857',
'AK43859857',
'AK43225067',
'AK43845067',
'AK43947167',
'AK43890367',
'AK43909367',
'AK43914567',
'AK43888567',
'AK43869567',
'AK43951667',
'AK43946767',
'AK43915867',
'AK43869967',
'AK43890077',
'AK43851077',
'AK43893077',
'AK43845077',
'AK43865077',
'AK43843177',
'AK43855177',
'AK43875277',
'AK43869277',
'AK43841377',
'AK43905377',
'AK43896377',
'AK43950477',
'AK43183477',
'AK43916477',
'AK43949477',
'AK43844577',
'AK43950677',
'AK43912677',
'AK43947677',
'AK43895877',
'AK43916977',
'AK43864087',
'AK43908087',
'AK43844187',
'AK43910287',
'AK43947287',
'AK43893387',
'AK43948387',
'AK43842587',
'AK43893587',
'AK43848587',
'AK43905687',
'AK43916687',
'AK43846687',
'AK43913787',
'AK43847887',
'AK43224987',
'AK43901197',
'AK43917197',
'AK43845297',
'AK43885397',
'AK43916497',
'AK43853897',
'AK43866897',
'AK43908997',
'AK43913008',
'AK43855008',
'AK43852108',
'AK43843108',
'AK43886108',
'AK43889108',
'AK43917208',
'AK43239208',
'AK43906308',
'AK43853408',
'AK43899408',
'AK43844508',
'AK43915608',
'AK43892708',
'AK44061018',
'AK43916018',
'AK43886018',
'AK43908018',
'AK43868018',
'AK43894118',
'AK43869218',
'AK43864318',
'AK43915318',
'AK43894418',
'AK43913518',
'AK43847518',
'AK43909518',
'AK43860618',
'AK43916718',
'AK43869818',
'AK43232918',
'AK43842918',
'AK43901028',
'AK43854028',
'AK43911228',
'AK43862328',
'AK43906328',
'AK43916328',
'AK43860428',
'AK43843428',
'AK43894428',
'AK43847428',
'AK43868428',
'AK43949428',
'AK43908528',
'AK43898528',
'AK43916728',
'AK43896728',
'AK43897728',
'AK43946828',
'AK43910928',
'AK43946928',
'AK43852038',
'AK43894038',
'AK43840238',
'AK43916238',
'AK43862438',
'AK43843538',
'AK43910638',
'AK43841738',
'AK43915738',
'AK43885738',
'AK43906738',
'AK43863838',
'AK43916838',
'AK43946838',
'AK43886838',
'AK43858048',
'AK43949048',
'AK43852348',
'AK43906348',
'AK43885548',
'AK43899548',
'AK43910648',
'AK43955648',
'AK43914748',
'AK43849748',
'AK43152848',
'AK43853848',
'AK43946848',
'AK43842948',
'AK43862948',
'AK43915948',
'AK43849058',
'AK43952158',
'AK43913158',
'AK43843158',
'AK43917158',
'AK43900458',
'AK43952458',
'AK43864458',
'AK43908458',
'AK43895558',
'AK43843658',
'AK43845658',
'AK43847758',
'AK43894858',
'AK43902958',
'AK43915958',
'AK43890168',
'AK43903168',
'AK43949168',
'AK43900268',
'AK43910268',
'AK43914268',
'AK43843368',
'AK43906468',
'AK43887468',
'AK43851568',
'AK43896568',
'AK43856668',
'AK43947668',
'AK43949768',
'AK43914868',
'AK43900968',
'AK43911968',
'AK43915968',
'AK43903078',
'AK43949078',
'AK43890178',
'AK43850278',
'AK43873278',
'AK43909278',
'AK43843478',
'AK43867478',
'AK43887478',
'AK43842578',
'AK43870678',
'AK43844678',
'AK43916778',
'AK43910878',
'AK43906878',
'AK43842978',
'AK43853978',
'AK43140088',
'AK43952088',
'AK43843088',
'AK43910188',
'AK43840188',
'AK43893188',
'AK43889288',
'AK43861388',
'AK43896388',
'AK43867388',
'AK43890488',
'AK43947488',
'AK43902588',
'AK43853588',
'AK43948588',
'AK43864688',
'AK43915788',
'AK43848888',
'AK43889888',
'AK43912988',
'AK43888988',
'AK43857098',
'AK43899098',
'AK43848298',
'AK43947398',
'AK43948398',
'AK43949398',
'AK43916498',
'AK43951598',
'AK43846698',
'AK43891798',
'AK43948798',
'AK43948898',
'AK43910998',
'AK43901998',
'AK43862998',
'AK43863998',
'AK43912009',
'AK43913009',
'AK43915009',
'AK43892109',
'AK43901209',
'AK43902309',
'AK43892309',
'AK43948309',
'AK43840409',
'AK43858409',
'AK43901509',
'AK43847509',
'AK43897509',
'AK43861609',
'AK43970709',
'AK43851709',
'AK43843809',
'AK43844809',
'AK43946809',
'AK43859909',
'AK43842019',
'AK43843019',
'AK43855019',
'AK43865019',
'AK43948019',
'AK43243119',
'AK43916219',
'AK43906319',
'AK43910419',
'AK43860419',
'AK43951519',
'AK43952519',
'AK43906519',
'AK43947519',
'AK43871819',
'AK43847819',
'AK43947819',
'AK43887919',
'AK43860029',
'AK43909029',
'AK43912129',
'AK43846129',
'AK43949129',
'AK43864229',
'AK43844329',
'AK43949329',
'AK43910429',
'AK43840429',
'AK43850529',
'AK43903529',
'AK43897529',
'AK43900829',
'AK43841829',
'AK43873829',
'AK43915829',
'AK43916829',
'AK43889829',
'AK43841929',
'AK43894039',
'AK43910139',
'AK43912139',
'AK43844139',
'AK43912239',
'AK43840339',
'AK43867439',
'AK43911539',
'AK43905539',
'AK43879539',
'AK43843639',
'AK43908639',
'AK43892739',
'AK43864839',
'AK43847839',
'AK43852939',
'AK43946939',
'AK43866939',
'AK43908939',
'AK43899939',
'AK43900049',
'AK43853049',
'AK43849049',
'AK43947149',
'AK43947249',
'AK43848249',
'AK43907349',
'AK43840549',
'AK43893549',
'AK43894549',
'AK43907549',
'AK43897549',
'AK43914949',
'AK43915949',
'AK43873259',
'AK43915259',
'AK43900459',
'AK43910459',
'AK43915459',
'AK43850559',
'AK43905659',
'AK43915659',
'AK43847659',
'AK43841859',
'AK43884859',
'AK43947859',
'AK43950959',
'AK43894959',
'AK43949959',
'AK43910069',
'AK43844069',
'AK43910269',
'AK43894269',
'AK43858269',
'AK43910369',
'AK43901369',
'AK43950569',
'AK43885569',
'AK43947569',
'AK43908569',
'AK43848569',
'AK43853669',
'AK43916669',
'AK43862769',
'AK43914769',
'AK43912869',
'AK43842869',
'AK43894079',
'AK43908079',
'AK43950179',
'AK43851179',
'AK43894179',
'AK43949179',
'AK43907279',
'AK43843379',
'AK43896379',
'AK43949379',
'AK43916479',
'AK43896479',
'AK43902579',
'AK43900679',
'AK43851679',
'AK43853679',
'AK43901779',
'AK43853879',
'AK43893879',
'AK43915879',
'AK43913979',
'AK43853089',
'AK43844089',
'AK43902189',
'AK43845189',
'AK43948189',
'AK43914289',
'AK43844389',
'AK43901589',
'AK43916589',
'AK43849589',
'AK43910689',
'AK43946889',
'AK43907889',
'AK43914989',
'AK43847989',
'AK43898099',
'AK43851299',
'AK43916299',
'AK43898299',
'AK43843499',
'AK43906499',
'AK43851599',
'AK43844599',
'AK43844699',
'AK43908699',
'AK43854999',
'AK43846999',
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
$this->logger->info('_data_patch_20200514_1000_CancelRegister.php end');

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