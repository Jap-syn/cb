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
$this->logger->info('_data_patch_20200701_1710_CancelRegister.php start');

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
'AK39462121',
'AK39463403',
'AK39481834',
'AK39482012',
'AK39482180',
'AK39500305',
'AK39519550',
'AK39519882',
'AK39536287',
'AK39542637',
'AK39542640',
'AK39551139',
'AK39567553',
'AK39590317',
'AK39590570',
'AK39613717',
'AK39613916',
'AK39613926',
'AK39614294',
'AK39625069',
'AK39625221',
'AK39625269',
'AK39625473',
'AK39625990',
'AK39629798',
'AK39629932',
'AK39629959',
'AK39629961',
'AK39633852',
'AK39634235',
'AK39634447',
'AK39634733',
'AK39644773',
'AK39644849',
'AK39644888',
'AK39654545',
'AK39655042',
'AK39655043',
'AK39655405',
'AK39688596',
'AK39688771',
'AK39688868',
'AK39689100',
'AK39702490',
'AK39702735',
'AK39711936',
'AK39712194',
'AK39712204',
'AK39712291',
'AK39712669',
'AK39712839',
'AK39712932',
'AK39712966',
'AK39721038',
'AK39768740',
'AK39768817',
'AK39768990',
'AK39796651',
'AK39796695',
'AK39796747',
'AK39796927',
'AK39796973',
'AK39797156',
'AK39797174',
'AK39797381',
'AK39797701',
'AK39833181',
'AK39833271',
'AK39833328',
'AK39833782',
'AK39834001',
'AK39834160',
'AK39859807',
'AK39860178',
'AK39860196',
'AK39861223',
'AK39861810',
'AK39862189',
'AK39883831',
'AK39883960',
'AK39883963',
'AK39883984',
'AK39884070',
'AK39884542',
'AK39885678',
'AK39910386',
'AK39910883',
'AK39911166',
'AK39911428',
'AK39911518',
'AK39918909',
'AK39919045',
'AK39919062',
'AK39919089',
'AK39919117',
'AK39919231',
'AK39919351',
'AK39919399',
'AK39919737',
'AK39919812',
'AK39919984',
'AK39920006',
'AK39920047',
'AK39920173',
'AK39920242',
'AK39920273',
'AK39920275',
'AK39920320',
'AK39920329',
'AK39920334',
'AK39920550',
'AK39920628',
'AK39920701',
'AK39921017',
'AK39921400',
'AK39921446',
'AK39921576',
'AK39921589',
'AK39921632',
'AK39921961',
'AK39921977',
'AK39922318',
'AK39922328',
'AK39922376',
'AK39922382',
'AK39922409',
'AK39922422',
'AK39922457',
'AK39922513',
'AK39922561',
'AK39922604',
'AK39922608',
'AK39922614',
'AK39922624',
'AK39922627',
'AK39922657',
'AK39922691',
'AK39922812',
'AK39922855',
'AK39930123',
'AK39930201',
'AK39930272',
'AK39930414',
'AK39949537',
'AK39949770',
'AK39949910',
'AK39949930',
'AK39950022',
'AK39967511',
'AK39968075',
'AK39968213',
'AK39968297',
'AK39968304',
'AK39968319',
'AK39968327',
'AK39968424',
'AK40002919',
'AK40003032',
'AK40003042',
'AK40003651',
'AK40013142',
'AK40013368',
'AK40013530',
'AK40013560',
'AK40013664',
'AK40013794',
'AK40013886',
'AK40013976',
'AK40014095',
'AK40014388',
'AK40014470',
'AK40014597',
'AK40014631',
'AK40014646',
'AK40014813',
'AK40014834',
'AK40014853',
'AK40015505',
'AK40015623',
'AK40015896',
'AK40016058',
'AK40016163',
'AK40016275',
'AK40016308',
'AK40028612',
'AK40028658',
'AK40028674',
'AK40028812',
'AK40028948',
'AK40029224',
'AK40029613',
'AK40045220',
'AK40045371',
'AK40045750',
'AK40045905',
'AK40093168',
'AK40093380',
'AK40093385',
'AK40093685',
'AK40093759',
'AK40094416',
'AK40094425',
'AK40111894',
'AK40112155',
'AK40112157',
'AK40112329',
'AK40112342',
'AK40130905',
'AK40131047',
'AK40145155',
'AK40145193',
'AK40145290',
'AK40145422',
'AK40145520',
'AK40146530',
'AK40146548',
'AK40146797',
'AK40154814',
'AK40154889',
'AK40155569',
'AK40155633',
'AK40155877',
'AK40155983',
'AK40159141',
'AK40159190',
'AK40159366',
'AK40159787',
'AK40159861',
'AK40159986',
'AK40160638',
'AK40160649',
'AK40160654',
'AK40160868',
'AK40160880',
'AK40160906',
'AK40160984',
'AK40174081',
'AK40174458',
'AK40191729',
'AK40192111',
'AK40192346',
'AK40198913',
'AK40198972',
'AK40199183',
'AK40213023',
'AK40213660',
'AK40214086',
'AK40224251',
'AK40225116',
'AK40225919',
'AK40225929',
'AK40230626',
'AK40230776',
'AK40254763',
'AK40254921',
'AK40255061',
'AK40255296',
'AK40260736',
'AK40264569',
'AK40265682',
'AK40266014',
'AK40267116',
'AK40267377',
'AK40267500',
'AK40272111',
'AK40272221',
'AK40272280',
'AK40272362',
'AK40290974',
'AK40296051',
'AK40296708',
'AK40297176',
'AK40297371',
'AK40297482',
'AK40298510',
'AK40300710',
'AK40318356',
'AK40334559',
'AK40334718',
'AK40334793',
'AK40356723',
'AK40358542',
'AK40358861',
'AK40359352',
'AK40361150',
'AK40361259',
'AK40361309',
'AK40361348',
'AK40361789',
'AK40361877',
'AK40362228',
'AK40363904',
'AK40380668',
'AK40380858',
'AK40381110',
'AK40381204',
'AK40381449',
'AK40382274',
'AK40382416',
'AK40382565',
'AK40382874',
'AK40382983',
'AK40383381',
'AK40383466',
'AK40383580',
'AK40390988',
'AK40391016',
'AK40396095',
'AK40396114',
'AK40399541',
'AK40399689',
'AK40399960',
'AK40403530',
'AK40403849',
'AK40404057',
'AK40405080',
'AK40407548',
'AK40407624',
'AK40408234',
'AK40408346',
'AK40494878',
'AK40497152',
'AK40497733',
'AK40522484',
'AK40522494',
'AK40527403',
'AK40528019',
'AK40528372',
'AK40529832',
'AK40529845',
'AK40530010',
'AK40530860',
'AK40535594',
'AK40535813',
'AK40536112',
'AK40538011',
'AK40538492',
'AK40538656',
'AK40538669',
'AK40539647',
'AK40549635',
'AK40549874',
'AK40550031',
'AK40550619',
'AK40552988',
'AK40555106',
'AK40555185',
'AK40582743',
'AK40582766',
'AK40590960',
'AK40591204',
'AK40591380',
'AK40591771',
'AK40591953',
'AK40592353',
'AK40592400',
'AK40592554',
'AK40592640',
'AK40593069',
'AK40593868',
'AK40593916',
'AK40594111',
'AK40594544',
'AK40594851',
'AK40595092',
'AK40598015',
'AK40598495',
'AK40630879',
'AK40630928',
'AK40631522',
'AK40631714',
'AK40631823',
'AK40631834',
'AK40632111',
'AK40632199',
'AK40632409',
'AK40632424',
'AK40632443',
'AK40632577',
'AK40632807',
'AK40633366',
'AK40633509',
'AK40635161',
'AK40635767',
'AK40635826',
'AK40636150',
'AK40636466',
'AK40636740',
'AK40637073',
'AK40638218',
'AK40638245',
'AK40638277',
'AK40638442',
'AK40639312',
'AK40640535',
'AK40640559',
'AK40640831',
'AK40641028',
'AK40641064',
'AK40641358',
'AK40641849',
'AK40641923',
'AK40641929',
'AK40641974',
'AK40642046',
'AK40642070',
'AK40642127',
'AK40642203',
'AK40642443',
'AK40642452',
'AK40642613',
'AK40643230',
'AK40643425',
'AK40643890',
'AK40644401',
'AK40644905',
'AK40645607',
'AK40645637',
'AK40645643',
'AK40645699',
'AK40645728',
'AK40645798',
'AK40645982',
'AK40646004',
'AK40646032',
'AK40646518',
'AK40646583',
'AK40646593',
'AK40646656',
'AK40646892',
'AK40648035',
'AK40648349',
'AK40649101',
'AK40649627',
'AK40654829',
'AK40655020',
'AK40655214',
'AK40655374',
'AK40655590',
'AK40656170',
'AK40656348',
'AK40656731',
'AK40657730',
'AK40657739',
'AK40658230',
'AK40658809',
'AK40659435',
'AK40659442',
'AK40659932',
'AK40660678',
'AK40661062',
'AK40661128',
'AK40661175',
'AK40661306',
'AK40661635',
'AK40661772',
'AK40662106',
'AK40662283',
'AK40662365',
'AK40662370',
'AK40662563',
'AK40662628',
'AK40662793',
'AK40663065',
'AK40663549',
'AK40663670',
'AK40663771',
'AK40663851',
'AK40663902',
'AK40664030',
'AK40664043',
'AK40664170',
'AK40664205',
'AK40664580',
'AK40664786',
'AK40664846',
'AK40665292',
'AK40665398',
'AK40665465',
'AK40665698',
'AK40665720',
'AK40665754',
'AK40666013',
'AK40666235',
'AK40666329',
'AK40666335',
'AK40666377',
'AK40667040',
'AK40667101',
'AK40667112',
'AK40667279',
'AK40668040',
'AK40668246',
'AK40668278',
'AK40668283',
'AK40668411',
'AK40668582',
'AK40668631',
'AK40668694',
'AK40668850',
'AK40669063',
'AK40669281',
'AK40669382',
'AK40669515',
'AK40669535',
'AK40669722',
'AK40670390',
'AK40670604',
'AK40670843',
'AK40670980',
'AK40671022',
'AK40671080',
'AK40671082',
'AK40706611',
'AK40706848',
'AK40707219',
'AK40708131',
'AK40709358',
'AK40710059',
'AK40710231',
'AK40711524',
'AK40711619',
'AK40711739',
'AK40712681',
'AK40712719',
'AK40712994',
'AK40713063',
'AK40713717',
'AK40713804',
'AK40713814',
'AK40713903',
'AK40714097',
'AK40714405',
'AK40714940',
'AK40715273',
'AK40716093',
'AK40716211',
'AK40716557',
'AK40717279',
'AK40717318',
'AK40717519',
'AK40717802',
'AK40718031',
'AK40718228',
'AK40718274',
'AK40718340',
'AK40718439',
'AK40718822',
'AK40719664',
'AK40759619',
'AK40765169',
'AK40765273',
'AK40798773',
'AK40798868',
'AK40800904',
'AK40800967',
'AK40800991',
'AK40801300',
'AK40801468',
'AK40801517',
'AK40801614',
'AK40802032',
'AK40802296',
'AK40802394',
'AK40802408',
'AK40803410',
'AK40804023',
'AK40804394',
'AK40804847',
'AK40805107',
'AK40805796',
'AK40816010',
'AK40816300',
'AK40816359',
'AK40816490',
'AK40817084',
'AK40817696',
'AK40817997',
'AK40818252',
'AK40820005',
'AK40858658',
'AK40858900',
'AK40859161',
'AK40859313',
'AK40859435',
'AK40859445',
'AK40859703',
'AK40860674',
'AK40861255',
'AK40862425',
'AK40862685',
'AK40898996',
'AK40899185',
'AK40899277',
'AK40899477',
'AK40899479',
'AK40899493',
'AK40899592',
'AK40899959',
'AK40900607',
'AK40901211',
'AK40902102',
'AK40902150',
'AK40902373',
'AK40902398',
'AK40903120',
'AK40903762',
'AK40904432',
'AK40905329',
'AK40906300',
'AK40906520',
'AK40906536',
'AK40907085',
'AK40908950',
'AK40909091',
'AK40909223',
'AK40909596',
'AK40910067',
'AK40910098',
'AK40910264',
'AK40919506',
'AK40919564',
'AK40919668',
'AK40966124',
'AK40967320',
'AK40967611',
'AK40968032',
'AK40969340',
'AK40970308',
'AK40970688',
'AK40971566',
'AK40971688',
'AK40971957',
'AK40972144',
'AK40972164',
'AK40972616',
'AK40972625',
'AK40973514',
'AK40990007',
'AK40990120',
'AK41032185',
'AK41032323',
'AK41032411',
'AK41032914',
'AK41034366',
'AK41035250',
'AK41035554',
'AK41035832',
'AK41036038',
'AK41036177',
'AK41036541',
'AK41037542',
'AK41038045',
'AK41038153',
'AK41038161',
'AK41038226',
'AK41056108',
'AK41059636',
'AK41061015',
'AK41061154',
'AK41061231',
'AK41061403',
'AK41061585',
'AK41062292',
'AK41062437',
'AK41063409',
'AK41063612',
'AK41064287',
'AK41064506',
'AK41064556',
'AK41064591',
'AK41064819',
'AK41065034',
'AK41065181',
'AK41065251',
'AK41065380',
'AK41065532',
'AK41065609',
'AK41065950',
'AK41066134',
'AK41082531',
'AK41108573',
'AK41131569',
'AK41131893',
'AK41132963',
'AK41132981',
'AK41133898',
'AK41134400',
'AK41134740',
'AK41134846',
'AK41134904',
'AK41135183',
'AK41135389',
'AK41136051',
'AK41138167',
'AK41139392',
'AK41139871',
'AK41140499',
'AK41140689',
'AK41140847',
'AK41140896',
'AK41141043',
'AK41142019',
'AK41142054',
'AK41142363',
'AK41142622',
'AK41143174',
'AK41143369',
'AK41204866',
'AK41205543',
'AK41206091',
'AK41206252',
'AK41206301',
'AK41206329',
'AK41206542',
'AK41206916',
'AK41207035',
'AK41207046',
'AK41207153',
'AK41207242',
'AK41207369',
'AK41207482',
'AK41207740',
'AK41207791',
'AK41208066',
'AK41208110',
'AK41208472',
'AK41209323',
'AK41209382',
'AK41211323',
'AK41211416',
'AK41211599',
'AK41211931',
'AK41212216',
'AK41212223',
'AK41212258',
'AK41212449',
'AK41212472',
'AK41212678',
'AK41212909',
'AK41212960',
'AK41213204',
'AK41213477',
'AK41213498',
'AK41213763',
'AK41213926',
'AK41213978',
'AK41214243',
'AK41214362',
'AK41214458',
'AK41214749',
'AK41267613',
'AK41268575',
'AK41268796',
'AK41269737',
'AK41270255',
'AK41270490',
'AK41270650',
'AK41271298',
'AK41271346',
'AK41287455',
'AK41289758',
'AK41290603',
'AK41292256',
'AK41292316',
'AK41292899',
'AK41303902',
'AK41304080',
'AK41316645',
'AK41316675',
'AK41316751',
'AK41316998',
'AK41317119',
'AK41317266',
'AK41317353',
'AK41317535',
'AK41317694',
'AK41317763',
'AK41317780',
'AK41318093',
'AK41318099',
'AK41318272',
'AK41318343',
'AK41318384',
'AK41318501',
'AK41318803',
'AK41318811',
'AK41318870',
'AK41319607',
'AK41319967',
'AK41320235',
'AK41320401',
'AK41320448',
'AK41320711',
'AK41320829',
'AK41320904',
'AK41320957',
'AK41321018',
'AK41321041',
'AK41321384',
'AK41321488',
'AK41321659',
'AK41321905',
'AK41321956',
'AK41322042',
'AK41322219',
'AK41322344',
'AK41322632',
'AK41322776',
'AK41322789',
'AK41323661',
'AK41323834',
'AK41324267',
'AK41324656',
'AK41324735',
'AK41324914',
'AK41324971',
'AK41325331',
'AK41325374',
'AK41325492',
'AK41325953',
'AK41326131',
'AK41326399',
'AK41326845',
'AK41326884',
'AK41327017',
'AK41327081',
'AK41327118',
'AK41327183',
'AK41327198',
'AK41327274',
'AK41327554',
'AK41328071',
'AK41328282',
'AK41329719',
'AK41330410',
'AK41330439',
'AK41330663',
'AK41331276',
'AK41331521',
'AK41331944',
'AK41332125',
'AK41332657',
'AK41332691',
'AK41332774',
'AK41333086',
'AK41333295',
'AK41333307',
'AK41333371',
'AK41333390',
'AK41333559',
'AK41333678',
'AK41334133',
'AK41334152',
'AK41334629',
'AK41334745',
'AK41334850',
'AK41334881',
'AK41334904',
'AK41335108',
'AK41335299',
'AK41335338',
'AK41335378',
'AK41335419',
'AK41335687',
'AK41336080',
'AK41336210',
'AK41336605',
'AK41336950',
'AK41337232',
'AK41337295',
'AK41337425',
'AK41337447',
'AK41337979',
'AK41338049',
'AK41338082',
'AK41338138',
'AK41338697',
'AK41339437',
'AK41339463',
'AK41339476',
'AK41339553',
'AK41339630',
'AK41339672',
'AK41343546',
'AK41344026',
'AK41344047',
'AK41344135',
'AK41344235',
'AK41345289',
'AK41345316',
'AK41345992',
'AK41346182',
'AK41346305',
'AK41346572',
'AK41346595',
'AK41346643',
'AK41346745',
'AK41346987',
'AK41347056',
'AK41347591',
'AK41348440',
'AK41348496',
'AK41380851',
'AK41381978',
'AK41382003',
'AK41383644',
'AK41383728',
'AK41383939',
'AK41384261',
'AK41384571',
'AK41384820',
'AK41385048',
'AK41385051',
'AK41385252',
'AK41385492',
'AK41385673',
'AK41385786',
'AK41386199',
'AK41386554',
'AK41386899',
'AK41387229',
'AK41387326',
'AK41387393',
'AK41389470',
'AK41389602',
'AK41389660',
'AK41390154',
'AK41390316',
'AK41390662',
'AK41390837',
'AK41390969',
'AK41391185',
'AK41391190',
'AK41391192',
'AK41391580',
'AK41391765',
'AK41391940',
'AK41392145',
'AK41392169',
'AK41392582',
'AK41392823',
'AK41393237',
'AK41393451',
'AK41393676',
'AK41393809',
'AK41393924',
'AK41394009',
'AK41394078',
'AK41394336',
'AK41394416',
'AK41394587',
'AK41394799',
'AK41394988',
'AK41396314',
'AK41396359',
'AK41396882',
'AK41396908',
'AK41397054',
'AK41398930',
'AK41398949',
'AK41399248',
'AK41399258',
'AK41399286',
'AK41399968',
'AK41400056',
'AK41400176',
'AK41400310',
'AK41400633',
'AK41400923',
'AK41401197',
'AK41401237',
'AK41401416',
'AK41401901',
'AK41402052',
'AK41402293',
'AK41402445',
'AK41402722',
'AK41403213',
'AK41418691',
'AK41419110',
'AK41420211',
'AK41420995',
'AK41421145',
'AK41421468',
'AK41422125',
'AK41422215',
'AK41422256',
'AK41422545',
'AK41422669',
'AK41422765',
'AK41423008',
'AK41423764',
'AK41423840',
'AK41424364',
'AK41424625',
'AK41424682',
'AK41424794',
'AK41424827',
'AK41430102',
'AK41430166',
'AK41430232',
'AK41430288',
'AK41430742',
'AK41430838',
'AK41431008',
'AK41431629',
'AK41432130',
'AK41432324',
'AK41447729',
'AK41447793',
'AK41447876',
'AK41448184',
'AK41448231',
'AK41449709',
'AK41449795',
'AK41449966',
'AK41450321',
'AK41450754',
'AK41454673',
'AK41454731',
'AK41454853',
'AK41456276',
'AK41456596',
'AK41457564',
'AK41457722',
'AK41457967',
'AK41458179',
'AK41458210',
'AK41477699',
'AK41521614',
'AK41521905',
'AK41522641',
'AK41522966',
'AK41523275',
'AK41523698',
'AK41523961',
'AK41524149',
'AK41524500',
'AK41524522',
'AK41524565',
'AK41524840',
'AK41524887',
'AK41524890',
'AK41525666',
'AK41525766',
'AK41526037',
'AK41526099',
'AK41526899',
'AK41533190',
'AK41533202',
'AK41552488',
'AK41552553',
'AK41552577',
'AK41552703',
'AK41552710',
'AK41552730',
'AK41552766',
'AK41552801',
'AK41552940',
'AK41552967',
'AK41553487',
'AK41553838',
'AK41553984',
'AK41553994',
'AK41554030',
'AK41554072',
'AK41554235',
'AK41554304',
'AK41554465',
'AK41554705',
'AK41554709',
'AK41555196',
'AK41555521',
'AK41555563',
'AK41556153',
'AK41556646',
'AK41556699',
'AK41557725',
'AK41558220',
'AK41558292',
'AK41558355',
'AK41558426',
'AK41558454',
'AK41558459',
'AK41558538',
'AK41558615',
'AK41558654',
'AK41558684',
'AK41558694',
'AK41558699',
'AK41558916',
'AK41558935',
'AK41558940',
'AK41559206',
'AK41559291',
'AK41559423',
'AK41559734',
'AK41559838',
'AK41560210',
'AK41560494',
'AK41560554',
'AK41560808',
'AK41561080',
'AK41561092',
'AK41561096',
'AK41561130',
'AK41561585',
'AK41581026',
'AK41581061',
'AK41581603',
'AK41581640',
'AK41583653',
'AK41583667',
'AK41584909',
'AK41585065',
'AK41585110',
'AK41585353',
'AK41585379',
'AK41585453',
'AK41585576',
'AK41585753',
'AK41585845',
'AK41585998',
'AK41586282',
'AK41586444',
'AK41586496',
'AK41586695',
'AK41587040',
'AK41587175',
'AK41587194',
'AK41587480',
'AK41587513',
'AK41587579',
'AK41587629',
'AK41589163',
'AK41592129',
'AK41593153',
'AK41593275',
'AK41593676',
'AK41593749',
'AK41593804',
'AK41593980',
'AK41594332',
'AK41606725',
'AK41612145',
'AK41644862',
'AK41649586',
'AK41649593',
'AK41649674',
'AK41649678',
'AK41649926',
'AK41650042',
'AK41650890',
'AK41650970',
'AK41651036',
'AK41651585',
'AK41651634',
'AK41651860',
'AK41651904',
'AK41652121',
'AK41652263',
'AK41652365',
'AK41652376',
'AK41652420',
'AK41652429',
'AK41652431',
'AK41652503',
'AK41652567',
'AK41652598',
'AK41652611',
'AK41652617',
'AK41652674',
'AK41652733',
'AK41652790',
'AK41652912',
'AK41653489',
'AK41653697',
'AK41653809',
'AK41653939',
'AK41655817',
'AK41656080',
'AK41656094',
'AK41656106',
'AK41656121',
'AK41656125',
'AK41656149',
'AK41656155',
'AK41656163',
'AK41656173',
'AK41656229',
'AK41656286',
'AK41656304',
'AK41656314',
'AK41656534',
'AK41657275',
'AK41657656',
'AK41658076',
'AK41658521',
'AK41658525',
'AK41658536',
'AK41659086',
'AK41659472',
'AK41676292',
'AK41676635',
'AK41678076',
'AK41678666',
'AK41678807',
'AK41678885',
'AK41679093',
'AK41679562',
'AK41679628',
'AK41680019',
'AK41680522',
'AK41680556',
'AK41680780',
'AK41681101',
'AK41681432',
'AK41681603',
'AK41681748',
'AK41682108',
'AK41682316',
'AK41682348',
'AK41682516',
'AK41682614',
'AK41682888',
'AK41683139',
'AK41683333',
'AK41683411',
'AK41685975',
'AK41686355',
'AK41686424',
'AK41686691',
'AK41686963',
'AK41687024',
'AK41687795',
'AK41687848',
'AK41687947',
'AK41687985',
'AK41688463',
'AK41688718',
'AK41688767',
'AK41689039',
'AK41689733',
'AK41689766',
'AK41690245',
'AK41690336',
'AK41690669',
'AK41690730',
'AK41739534',
'AK41742246',
'AK41742422',
'AK41742970',
'AK41743246',
'AK41743556',
'AK41743915',
'AK41744530',
'AK41744642',
'AK41744843',
'AK41744892',
'AK41745097',
'AK41745286',
'AK41745686',
'AK41799621',
'AK41799872',
'AK41804359',
'AK41806418',
'AK41813770',
'AK41815062',
'AK42294978',
'AK42295192',
'AK42295220',
'AK42295575',
'AK42295700',
'AK42295736',
'AK42296064',
'AK42296143',
'AK42296439',
'AK42296709',
'AK42296740',
'AK42296976',
'AK42297115',
'AK42297183',
'AK42297417',
'AK42297616',
'AK42297841',
'AK42297883',
'AK42297904',
'AK42298255',
'AK42299715',
'AK42299739',
'AK42299753',
'AK42299777',
'AK42299992',
'AK42300016',
'AK42300287',
'AK42300604',
'AK42300630',
'AK42301052',
'AK42301400',
'AK42301693',
'AK42302465',
'AK42303091',
'AK42303167',
'AK42303554',
'AK42303788',
'AK42303831',
'AK42303868',
'AK42304052',
'AK42307317',
'AK42307856',
'AK42308024',
'AK42308199',
'AK42309880',
'AK42310245',
'AK42310324',
'AK42310340',
'AK42310743',
'AK42311016',
'AK42311742',
'AK42312465',
'AK42312546',
'AK42312715',
'AK42313572',
'AK42313692',
'AK42313792',
'AK42313800',
'AK42313852',
'AK42314053',
'AK42321267',
'AK42321606',
'AK42322279',
'AK42322575',
'AK42323219',
'AK42323231',
'AK42323454',
'AK42324050',
'AK42324059',
'AK42326642',
'AK42328027',
'AK42328703',
'AK42328955',
'AK42329057',
'AK42330407',
'AK42330436',
'AK42331397',
'AK42332783',
'AK42332973',
'AK42333055',
'AK42333136',
'AK42333155',
'AK42333270',
'AK42333272',
'AK42333686',
'AK42333771',
'AK42334151',
'AK42334280',
'AK42334603',
'AK42334757',
'AK42335123',
'AK42335268',
'AK42336078',
'AK42342089',
'AK42343350',
'AK42343464',
'AK42343587',
'AK42344316',
'AK42344725',
'AK42344860',
'AK42344892',
'AK42344914',
'AK42345165',
'AK42345645',
'AK42345869',
'AK42345989',
'AK42346161',
'AK42346172',
'AK42346842',
'AK42347017',
'AK42347047',
'AK42347052',
'AK42347517',
'AK42348059',
'AK42348118',
'AK42348220',
'AK42348395',
'AK42348420',
'AK42348770',
'AK42348992',
'AK42348997',
'AK42349040',
'AK42349119',
'AK42349270',
'AK42349290',
'AK42349338',
'AK42349365',
'AK42349485',
'AK42349503',
'AK42349513',
'AK42349556',
'AK42349560',
'AK42349564',
'AK42349567',
'AK42349588',
'AK42349610',
'AK42349642',
'AK42349701',
'AK42349770',
'AK42349871',
'AK42349889',
'AK42350050',
'AK42350060',
'AK42350343',
'AK42350346',
'AK42350559',
'AK42350808',
'AK42351165',
'AK42351346',
'AK42351474',
'AK42351493',
'AK42351555',
'AK42351739',
'AK42351822',
'AK42351847',
'AK42352049',
'AK42352071',
'AK42352129',
'AK42352138',
'AK42352143',
'AK42352150',
'AK42352164',
'AK42352391',
'AK42352473',
'AK42352600',
'AK42352637',
'AK42352866',
'AK42352869',
'AK42353102',
'AK42353185',
'AK42353439',
'AK42353762',
'AK42354105',
'AK42354224',
'AK42354249',
'AK42354389',
'AK42354428',
'AK42354479',
'AK42354507',
'AK42361043',
'AK42361218',
'AK42361448',
'AK42362302',
'AK42364124',
'AK42364138',
'AK42364770',
'AK42365343',
'AK42372765',
'AK42373233',
'AK42373406',
'AK42373425',
'AK42373650',
'AK42373660',
'AK42374166',
'AK42374537',
'AK42375315',
'AK42375635',
'AK42377251',
'AK42377840',
'AK42378220',
'AK42378333',
'AK42378770',
'AK42378802',
'AK42378868',
'AK42379099',
'AK42379157',
'AK42379488',
'AK42420857',
'AK42425820',
'AK42425924',
'AK42427238',
'AK42427285',
'AK42427434',
'AK42427713',
'AK42428537',
'AK42428585',
'AK42429367',
'AK42429396',
'AK42429997',
'AK42430892',
'AK42431062',
'AK42431251',
'AK42431826',
'AK42432366',
'AK42432450',
'AK42433285',
'AK42433389',
'AK42433525',
'AK42437405',
'AK42437677',
'AK42446968',
'AK42447586',
'AK42447941',
'AK42447961',
'AK42448314',
'AK42448339',
'AK42448754',
'AK42449234',
'AK42449570',
'AK42450461',
'AK42450844',
'AK42451894',
'AK42509140',
'AK42512925',
'AK42514413',
'AK42514582',
'AK42514958',
'AK42761697',
'AK42761704',
'AK42761935',
'AK42763607',
'AK42764497',
'AK42766108',
'AK42766250',
'AK42766841',
'AK42767263',
'AK42767657',
'AK42789178',
'AK42789417',
'AK42789523',
'AK42790692',
'AK42790753',
'AK42791247',
'AK42791786',
'AK42811222',
'AK42811485',
'AK42811597',
'AK42812074',
'AK42812660',
'AK42812823',
'AK42812919',
'AK42813003',
'AK42813251',
'AK42813313',
'AK42813323',
'AK42813898',
'AK42814086',
'AK42814159',
'AK42814527',
'AK42814886',
'AK42831430',
'AK42831474',
'AK42831640',
'AK42831726',
'AK42831752',
'AK42831781',
'AK42877670',
'AK42879486',
'AK42879619',
'AK42879653',
'AK42880510',
'AK42880531',
'AK42880707',
'AK42881227',
'AK42881578',
'AK42881588',
'AK42881793',
'AK42882999',
'AK42901493',
'AK42901521',
'AK42901853',
'AK42902210',
'AK42902353',
'AK42902409',
'AK42902537',
'AK42902574',
'AK42902691',
'AK42925412',
'AK42925810',
'AK42950717',
'AK42950870',
'AK42951165',
'AK42951273',
'AK42951283',
'AK42951330',
'AK42951341',
'AK42951806',
'AK42951890',
'AK42952048',
'AK42952295',
'AK42952370',
'AK42952390',
'AK42952401',
'AK42952411',
'AK42982595',
'AK42982693',
'AK42982804',
'AK42984555',
'AK42984614',
'AK42985772',
'AK42987333',
'AK42987417',
'AK42987493',
'AK42987980',
'AK42988168',
'AK42988197',
'AK43023865',
'AK43023916',
'AK43024967',
'AK43025710',
'AK43025808',
'AK43025902',
'AK43026454',
'AK43027038',
'AK43029279',
'AK43030603',
'AK43030751',
'AK43030884',
'AK43050401',
'AK43050575',
'AK43050619',
'AK43050792',
'AK43051037',
'AK43051400',
'AK43051875',
'AK43051914',
'AK43052189',
'AK43052221',
'AK43052339',
'AK43052627',
'AK43052834',
'AK43053030',
'AK43053110',
'AK43053364',
'AK43053429',
'AK43053575',
'AK43053642',
'AK43053674',
'AK43054558',
'AK43115593',
'AK43115611',
'AK43208910',
'AK43209381',
'AK43209699',
'AK43287484',
'AK43290314',
'AK43290318',
'AK43291005',
'AK43291047',
'AK43301928',
'AK43305856',
'AK43308612',
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
$this->logger->info('_data_patch_20200701_1710_CancelRegister.php end');

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
