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
$this->logger->info('_data_patch_20200817_1400_CancelRegister.php start');

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
'AK45715000',
'AK45696000',
'AK45040100',
'AK45684100',
'AK45677400',
'AK45687400',
'AK45702500',
'AK45695500',
'AK45669500',
'AK45700600',
'AK45651600',
'AK44451700',
'AK45636700',
'AK45673800',
'AK45674800',
'AK45705800',
'AK45728800',
'AK45640900',
'AK45665900',
'AK43907900',
'AK45730010',
'AK45683010',
'AK44610110',
'AK45704110',
'AK45674110',
'AK45685210',
'AK45662410',
'AK45034410',
'AK45650610',
'AK45708610',
'AK45700810',
'AK45716810',
'AK45710910',
'AK45714020',
'AK45695020',
'AK45701220',
'AK45701320',
'AK45667320',
'AK45694420',
'AK45718420',
'AK45714620',
'AK45694620',
'AK45735620',
'AK45650820',
'AK45704820',
'AK45044820',
'AK45694920',
'AK45666920',
'AK45689920',
'AK45622030',
'AK45692130',
'AK45725130',
'AK45688130',
'AK45682230',
'AK45668230',
'AK45671330',
'AK45693330',
'AK45696330',
'AK45690430',
'AK45752430',
'AK45727430',
'AK45653630',
'AK45745630',
'AK45621730',
'AK45710830',
'AK45640830',
'AK45722830',
'AK45642930',
'AK45704930',
'AK45678930',
'AK45638040',
'AK45704240',
'AK45708240',
'AK45704340',
'AK45650540',
'AK45752540',
'AK45689740',
'AK45621840',
'AK45037840',
'AK43908840',
'AK45732940',
'AK45702050',
'AK45722050',
'AK45654050',
'AK45731150',
'AK45665150',
'AK45706350',
'AK45687350',
'AK45634450',
'AK45688450',
'AK45679450',
'AK45692550',
'AK45690650',
'AK45692750',
'AK45666850',
'AK45692950',
'AK45654950',
'AK45694950',
'AK45706950',
'AK45702060',
'AK45037060',
'AK45693160',
'AK45652260',
'AK45665360',
'AK45667360',
'AK45621460',
'AK45689560',
'AK45738660',
'AK45674760',
'AK45695760',
'AK45690860',
'AK45701860',
'AK45722860',
'AK45724860',
'AK45718860',
'AK45689860',
'AK45704070',
'AK45714070',
'AK45725070',
'AK45671170',
'AK45652270',
'AK45689270',
'AK45665370',
'AK45738370',
'AK45688370',
'AK45752470',
'AK45712570',
'AK45662570',
'AK45641670',
'AK45042770',
'AK45687770',
'AK45666870',
'AK45692970',
'AK45695970',
'AK45689970',
'AK45695080',
'AK45688080',
'AK45640180',
'AK45665180',
'AK45700280',
'AK45691280',
'AK45652280',
'AK45693280',
'AK45666280',
'AK45621380',
'AK45704380',
'AK45729380',
'AK45721480',
'AK45705480',
'AK45687480',
'AK45638580',
'AK45730780',
'AK45642880',
'AK45663880',
'AK45683880',
'AK45666880',
'AK45662980',
'AK45691190',
'AK45705190',
'AK45688190',
'AK45715290',
'AK45643490',
'AK45654490',
'AK45685490',
'AK45696490',
'AK45705590',
'AK45686590',
'AK45696590',
'AK45687690',
'AK45692790',
'AK45682890',
'AK45665890',
'AK45704001',
'AK45745101',
'AK45732201',
'AK45665201',
'AK45638201',
'AK44456301',
'AK45690401',
'AK45634401',
'AK45694401',
'AK45705401',
'AK45725401',
'AK45694501',
'AK45712601',
'AK45668601',
'AK45638701',
'AK45703801',
'AK45734801',
'AK45688901',
'AK45715011',
'AK45721211',
'AK45741211',
'AK45697211',
'AK45621511',
'AK45705611',
'AK45692711',
'AK45687811',
'AK43887811',
'AK45712911',
'AK45713911',
'AK45666911',
'AK45687911',
'AK45736021',
'AK45666021',
'AK45740121',
'AK45673121',
'AK45725121',
'AK45664321',
'AK45702421',
'AK45687421',
'AK45697421',
'AK45701521',
'AK45692521',
'AK45705521',
'AK45718521',
'AK45689521',
'AK45681621',
'AK45673621',
'AK45672721',
'AK45706721',
'AK45621821',
'AK45704821',
'AK45749031',
'AK45041131',
'AK45643131',
'AK45705131',
'AK45697531',
'AK45714631',
'AK45666731',
'AK45728831',
'AK45666931',
'AK45653041',
'AK45673041',
'AK45705041',
'AK45707041',
'AK43222141',
'AK45703141',
'AK45730241',
'AK45036241',
'AK45637241',
'AK45033341',
'AK45704341',
'AK45035341',
'AK45711441',
'AK45664441',
'AK45707441',
'AK45045541',
'AK45836541',
'AK45690641',
'AK44468641',
'AK45701741',
'AK45696741',
'AK45639741',
'AK45704841',
'AK45676841',
'AK45705941',
'AK45699941',
'AK45732051',
'AK45685051',
'AK45666051',
'AK45704251',
'AK45664351',
'AK45674351',
'AK45689351',
'AK45700451',
'AK45042551',
'AK45692551',
'AK45696551',
'AK45667551',
'AK45690851',
'AK45705851',
'AK45027851',
'AK45048951',
'AK45693061',
'AK45694261',
'AK45682361',
'AK45725361',
'AK45640461',
'AK45666461',
'AK45717461',
'AK45699561',
'AK45673661',
'AK45695761',
'AK45688761',
'AK45700961',
'AK45704961',
'AK45690071',
'AK45653071',
'AK45746071',
'AK45690171',
'AK45723171',
'AK45704271',
'AK45665371',
'AK45752471',
'AK45683471',
'AK45725471',
'AK45747571',
'AK45698671',
'AK45747771',
'AK45694871',
'AK45687871',
'AK45621971',
'AK45025971',
'AK45657971',
'AK43909971',
'AK45643081',
'AK45686081',
'AK45660281',
'AK45703281',
'AK45665381',
'AK45666381',
'AK45687381',
'AK45666481',
'AK45690681',
'AK45663681',
'AK45704681',
'AK45705681',
'AK45688681',
'AK45676781',
'AK45737781',
'AK45738781',
'AK45650981',
'AK45680981',
'AK44556981',
'AK45712091',
'AK45652091',
'AK45715091',
'AK45683191',
'AK45692291',
'AK45701491',
'AK45660591',
'AK45643591',
'AK43884791',
'AK45707991',
'AK45748002',
'AK45719002',
'AK45703202',
'AK45687202',
'AK45752302',
'AK45714302',
'AK45690402',
'AK45701402',
'AK45690502',
'AK45701502',
'AK45651702',
'AK45687702',
'AK45701802',
'AK45646802',
'AK45684902',
'AK45673012',
'AK45691112',
'AK45045112',
'AK45653212',
'AK45685212',
'AK45688212',
'AK45688312',
'AK45676412',
'AK45639412',
'AK45641712',
'AK45723712',
'AK45004712',
'AK45668812',
'AK45034912',
'AK45705912',
'AK45699912',
'AK44463122',
'AK45667222',
'AK45731322',
'AK45692322',
'AK45634422',
'AK45679422',
'AK45689422',
'AK45712522',
'AK45667522',
'AK45634622',
'AK45684622',
'AK45695622',
'AK45674722',
'AK45685722',
'AK45722822',
'AK45703822',
'AK45727032',
'AK45671132',
'AK45713132',
'AK45655132',
'AK45667132',
'AK45729132',
'AK45720232',
'AK45690232',
'AK45688232',
'AK45650332',
'AK45687332',
'AK45719632',
'AK45711732',
'AK45735732',
'AK45666832',
'AK45688832',
'AK45705932',
'AK45749142',
'AK45642242',
'AK45703242',
'AK45713242',
'AK45653242',
'AK45621442',
'AK45687442',
'AK45658442',
'AK45621542',
'AK45691542',
'AK45665542',
'AK45681742',
'AK45674742',
'AK45836742',
'AK45689742',
'AK45681842',
'AK45045942',
'AK45671052',
'AK45704052',
'AK45707052',
'AK45687052',
'AK45705152',
'AK45636152',
'AK45687152',
'AK45699252',
'AK45688352',
'AK45665452',
'AK45712552',
'AK45665552',
'AK45666552',
'AK45667552',
'AK45687552',
'AK45658552',
'AK45673652',
'AK45634652',
'AK45705752',
'AK45715752',
'AK45676752',
'AK44464852',
'AK45705062',
'AK43898062',
'AK45689062',
'AK45651262',
'AK45694262',
'AK45669262',
'AK45681362',
'AK45654462',
'AK45685462',
'AK45703562',
'AK45710662',
'AK45737662',
'AK44559662',
'AK45663762',
'AK44559762',
'AK45672962',
'AK45666072',
'AK45686072',
'AK45708072',
'AK45733272',
'AK45692372',
'AK45652472',
'AK45712572',
'AK45720672',
'AK45704672',
'AK45686672',
'AK45653772',
'AK45703872',
'AK45713872',
'AK45704872',
'AK45706082',
'AK45686082',
'AK45708182',
'AK45668182',
'AK45675282',
'AK45729282',
'AK45703382',
'AK45667382',
'AK45738382',
'AK45621482',
'AK45687582',
'AK45706682',
'AK45666682',
'AK45686682',
'AK45687682',
'AK45656882',
'AK45715982',
'AK45745982',
'AK45738982',
'AK45686092',
'AK45688092',
'AK45836192',
'AK45659192',
'AK45640292',
'AK45666292',
'AK45029292',
'AK45660392',
'AK45672392',
'AK45688392',
'AK45704492',
'AK45705592',
'AK45729592',
'AK45682692',
'AK45836692',
'AK45621792',
'AK45727792',
'AK45737792',
'AK45657792',
'AK45678792',
'AK45688792',
'AK45716892',
'AK45705203',
'AK45020303',
'AK45681303',
'AK45713303',
'AK45666303',
'AK45707303',
'AK45727303',
'AK45667303',
'AK45698303',
'AK45651403',
'AK45702403',
'AK45667403',
'AK45677403',
'AK45687403',
'AK45752503',
'AK45684603',
'AK45726603',
'AK45737603',
'AK45724703',
'AK43895703',
'AK45717703',
'AK45728703',
'AK45702903',
'AK45687903',
'AK45744013',
'AK45687113',
'AK45714213',
'AK45687213',
'AK45690313',
'AK45665413',
'AK45633513',
'AK45689613',
'AK45010713',
'AK45751713',
'AK45640813',
'AK45665813',
'AK45695813',
'AK45726813',
'AK45687813',
'AK45636023',
'AK45667023',
'AK45748023',
'AK45738123',
'AK45731223',
'AK45708223',
'AK45710323',
'AK45664323',
'AK45684323',
'AK45694323',
'AK45692423',
'AK43915423',
'AK45701523',
'AK45704523',
'AK45836523',
'AK45700623',
'AK45640623',
'AK45665623',
'AK45737623',
'AK45691723',
'AK45685723',
'AK45705823',
'AK45715823',
'AK45720923',
'AK45719923',
'AK45689923',
'AK45622033',
'AK45034033',
'AK45699033',
'AK45704133',
'AK45728133',
'AK45704233',
'AK45706233',
'AK45704333',
'AK45733433',
'AK45705433',
'AK45677433',
'AK45729433',
'AK45700633',
'AK45713633',
'AK45836633',
'AK45666633',
'AK45745733',
'AK45706733',
'AK45666833',
'AK45709833',
'AK45704933',
'AK45678933',
'AK45692043',
'AK45700143',
'AK45657143',
'AK45669243',
'AK45703443',
'AK45685443',
'AK45676443',
'AK45703543',
'AK45686643',
'AK45681843',
'AK45703843',
'AK45690943',
'AK45743153',
'AK45667153',
'AK45704253',
'AK45702453',
'AK45694453',
'AK45685453',
'AK45692553',
'AK45694653',
'AK45688653',
'AK45690853',
'AK45742853',
'AK45663853',
'AK45726953',
'AK45709953',
'AK45718063',
'AK45643163',
'AK45684163',
'AK45621263',
'AK45705263',
'AK45688263',
'AK45690363',
'AK45702363',
'AK45743363',
'AK45721463',
'AK45031463',
'AK45661463',
'AK45673563',
'AK45715663',
'AK45687663',
'AK45688863',
'AK45689863',
'AK45689963',
'AK45710173',
'AK45661173',
'AK45691173',
'AK45694173',
'AK45667173',
'AK45693273',
'AK45668273',
'AK45715373',
'AK44994473',
'AK45705473',
'AK45659473',
'AK45732573',
'AK45704673',
'AK45705673',
'AK45655673',
'AK44459673',
'AK45007773',
'AK45687773',
'AK45649773',
'AK45711873',
'AK45705873',
'AK45706873',
'AK44414973',
'AK44437973',
'AK45734183',
'AK45667183',
'AK45688283',
'AK45711383',
'AK45718383',
'AK45752483',
'AK45714483',
'AK45689483',
'AK45666583',
'AK45018683',
'AK45709683',
'AK45682883',
'AK44464883',
'AK45686883',
'AK45650093',
'AK45688193',
'AK45710293',
'AK45690293',
'AK45681293',
'AK45712293',
'AK45704293',
'AK45696293',
'AK45667293',
'AK45718293',
'AK45714393',
'AK45689393',
'AK45699393',
'AK45692493',
'AK45685493',
'AK45686493',
'AK45714593',
'AK45705593',
'AK45715593',
'AK45668593',
'AK45712693',
'AK45685693',
'AK45836693',
'AK45696693',
'AK45700793',
'AK45635793',
'AK45649793',
'AK45684893',
'AK45666893',
'AK45688893',
'AK45704993',
'AK45690004',
'AK45697004',
'AK45705104',
'AK45667104',
'AK45693204',
'AK45687204',
'AK45640304',
'AK45653304',
'AK45699404',
'AK45671504',
'AK45651604',
'AK45669604',
'AK45664704',
'AK45694704',
'AK44416804',
'AK45688804',
'AK45738904',
'AK45700014',
'AK45710014',
'AK45682014',
'AK45654014',
'AK45690114',
'AK45685214',
'AK45724314',
'AK45677314',
'AK45681414',
'AK45734414',
'AK45677414',
'AK45697514',
'AK45650614',
'AK45687614',
'AK45690714',
'AK45687714',
'AK45701814',
'AK45744814',
'AK45669814',
'AK45699814',
'AK45740914',
'AK45621914',
'AK45697024',
'AK45642124',
'AK45738124',
'AK45688124',
'AK45665224',
'AK45649324',
'AK45690424',
'AK45665424',
'AK45685624',
'AK45747624',
'AK45687624',
'AK43885724',
'AK45689724',
'AK45745824',
'AK45687824',
'AK45677924',
'AK45643034',
'AK45690134',
'AK44932134',
'AK45704134',
'AK45709234',
'AK45692334',
'AK45704334',
'AK45676334',
'AK45710434',
'AK45705434',
'AK45689534',
'AK45693634',
'AK45735634',
'AK45666634',
'AK45027634',
'AK45667634',
'AK45621734',
'AK45687734',
'AK45621934',
'AK45641934',
'AK45678934',
'AK45060144',
'AK45706244',
'AK45657344',
'AK45710444',
'AK45633444',
'AK45697444',
'AK45700544',
'AK45621544',
'AK45694544',
'AK45646544',
'AK45689544',
'AK44469644',
'AK45704744',
'AK45686744',
'AK45663844',
'AK45735844',
'AK45666944',
'AK45692054',
'AK45741154',
'AK45675154',
'AK45688254',
'AK45672354',
'AK45703354',
'AK45698354',
'AK45676454',
'AK45649554',
'AK45705754',
'AK45687754',
'AK45700854',
'AK45690854',
'AK45721854',
'AK45682854',
'AK45654854',
'AK45705854',
'AK45632954',
'AK45737954',
'AK45622064',
'AK45667164',
'AK45708164',
'AK45707464',
'AK45650564',
'AK45708564',
'AK45733664',
'AK45665664',
'AK45666664',
'AK45688664',
'AK45738864',
'AK45683964',
'AK45686964',
'AK45686074',
'AK45703174',
'AK45665174',
'AK45655274',
'AK45728274',
'AK45703374',
'AK45666374',
'AK45723474',
'AK44555474',
'AK45836574',
'AK45681774',
'AK45740874',
'AK45651874',
'AK45699874',
'AK45712974',
'AK45690084',
'AK45666084',
'AK45665184',
'AK45014284',
'AK45729284',
'AK45681384',
'AK45662484',
'AK45621584',
'AK45716684',
'AK45652884',
'AK45712984',
'AK45673984',
'AK45689984',
'AK45007094',
'AK45729094',
'AK45023194',
'AK45693294',
'AK45714294',
'AK45699294',
'AK45690394',
'AK45683494',
'AK45665494',
'AK45698494',
'AK45641594',
'AK44605594',
'AK45696594',
'AK45714894',
'AK45688894',
'AK45705994',
'AK45675994',
'AK45667994',
'AK45740005',
'AK45681005',
'AK45715005',
'AK45666005',
'AK45682105',
'AK45713105',
'AK45635105',
'AK45666105',
'AK45653205',
'AK45664205',
'AK45684205',
'AK45715205',
'AK45687205',
'AK45701305',
'AK45671305',
'AK45674305',
'AK45666305',
'AK45669305',
'AK45667405',
'AK44432505',
'AK44471705',
'AK45713705',
'AK45689705',
'AK45665905',
'AK45729905',
'AK45690115',
'AK45665215',
'AK45687215',
'AK45708315',
'AK45642415',
'AK45656415',
'AK45676415',
'AK45720515',
'AK45665515',
'AK45709515',
'AK45715615',
'AK45701715',
'AK45723715',
'AK45704715',
'AK45667715',
'AK45040815',
'AK45735915',
'AK45670025',
'AK45673025',
'AK45715025',
'AK45725025',
'AK45660125',
'AK43861125',
'AK45671125',
'AK45726225',
'AK45650325',
'AK45702325',
'AK45665325',
'AK45687325',
'AK45714425',
'AK45026425',
'AK45836425',
'AK45710525',
'AK45718525',
'AK45689525',
'AK45704625',
'AK45686625',
'AK45666825',
'AK45699825',
'AK45705925',
'AK45665925',
'AK45685925',
'AK45053035',
'AK45714135',
'AK45647135',
'AK45720335',
'AK45683335',
'AK45705335',
'AK45666335',
'AK45727335',
'AK45730435',
'AK45055535',
'AK45700635',
'AK45715635',
'AK45737835',
'AK45722935',
'AK45692935',
'AK45669345',
'AK45670445',
'AK45713445',
'AK45676445',
'AK45667445',
'AK45665545',
'AK45690645',
'AK45686645',
'AK45027645',
'AK45701845',
'AK45681845',
'AK45709845',
'AK45681945',
'AK45732945',
'AK45664055',
'AK45003155',
'AK45700255',
'AK45691255',
'AK45703255',
'AK45684255',
'AK45696255',
'AK45690355',
'AK45745355',
'AK45666355',
'AK45698355',
'AK45713455',
'AK45743455',
'AK45704455',
'AK45666455',
'AK45696555',
'AK45689555',
'AK45747655',
'AK45661755',
'AK45705755',
'AK45687755',
'AK45689755',
'AK45010955',
'AK45710065',
'AK45712065',
'AK45692065',
'AK45655065',
'AK45718065',
'AK45704165',
'AK45665165',
'AK45707165',
'AK45667165',
'AK45719265',
'AK45679365',
'AK45752465',
'AK45705465',
'AK45706465',
'AK45696465',
'AK45667465',
'AK45650565',
'AK45728565',
'AK45633665',
'AK45687665',
'AK45689665',
'AK45642765',
'AK45705765',
'AK45055765',
'AK45666765',
'AK45688765',
'AK45689865',
'AK45633965',
'AK45646965',
'AK45705075',
'AK45715075',
'AK45666075',
'AK45686075',
'AK45667075',
'AK45745175',
'AK45667275',
'AK45646375',
'AK45738375',
'AK45640475',
'AK45673575',
'AK45689575',
'AK45746675',
'AK45678775',
'AK45660875',
'AK45664875',
'AK45665875',
'AK45701975',
'AK45691975',
'AK45690085',
'AK45685085',
'AK45717085',
'AK45678285',
'AK45688285',
'AK45725385',
'AK45635385',
'AK45026385',
'AK45746385',
'AK45687385',
'AK45698385',
'AK45752485',
'AK45699485',
'AK45667585',
'AK45722885',
'AK45663885',
'AK45035885',
'AK45621985',
'AK45692095',
'AK45642195',
'AK43952195',
'AK45728295',
'AK45679295',
'AK45737395',
'AK45722495',
'AK45705595',
'AK45715795',
'AK45668795',
'AK45703895',
'AK45714006',
'AK45742106',
'AK45666106',
'AK45747106',
'AK43218106',
'AK45700306',
'AK45711306',
'AK45673306',
'AK45714306',
'AK45730406',
'AK45709506',
'AK45735606',
'AK45666606',
'AK45686606',
'AK45727606',
'AK45704706',
'AK45689706',
'AK45705806',
'AK45686806',
'AK45687806',
'AK44997806',
'AK45687906',
'AK45704016',
'AK45694116',
'AK45726216',
'AK45688216',
'AK45739216',
'AK45703316',
'AK45653316',
'AK45666316',
'AK45688316',
'AK45727416',
'AK45679416',
'AK45707516',
'AK45692616',
'AK45702716',
'AK45692816',
'AK45704816',
'AK45023916',
'AK45016126',
'AK45690226',
'AK45731526',
'AK45703526',
'AK44467526',
'AK45621626',
'AK45652626',
'AK45714626',
'AK45705626',
'AK45687626',
'AK44648626',
'AK45631926',
'AK45684926',
'AK45689926',
'AK45678136',
'AK45652236',
'AK45685236',
'AK45836236',
'AK45670336',
'AK45704336',
'AK45669336',
'AK45687436',
'AK45689436',
'AK45715536',
'AK45705636',
'AK45686636',
'AK45681936',
'AK45723936',
'AK45685936',
'AK45678936',
'AK44932146',
'AK45683146',
'AK45707146',
'AK45650346',
'AK45690346',
'AK45688346',
'AK45678546',
'AK45711646',
'AK45691646',
'AK45703646',
'AK45713646',
'AK45714646',
'AK45687646',
'AK45635746',
'AK45706746',
'AK45737746',
'AK45690846',
'AK45666946',
'AK45659056',
'AK45704156',
'AK45645156',
'AK45748156',
'AK45704256',
'AK45701356',
'AK45685456',
'AK45687456',
'AK45659456',
'AK45694556',
'AK45718556',
'AK45703656',
'AK45668656',
'AK44550756',
'AK45685856',
'AK45621956',
'AK45705956',
'AK45710066',
'AK45682166',
'AK45665166',
'AK45696166',
'AK45695266',
'AK45737366',
'AK45704466',
'AK45688466',
'AK45665566',
'AK45665666',
'AK45039666',
'AK45682866',
'AK45635866',
'AK45713076',
'AK45682176',
'AK45668176',
'AK45652276',
'AK45035276',
'AK45030376',
'AK45691376',
'AK45665376',
'AK45694476',
'AK45699476',
'AK45655576',
'AK45724676',
'AK45836676',
'AK45685776',
'AK45716776',
'AK45654876',
'AK45037876',
'AK45658876',
'AK45681976',
'AK45704976',
'AK45635976',
'AK45711186',
'AK45694186',
'AK45702586',
'AK45642786',
'AK45685786',
'AK45696786',
'AK45697786',
'AK45699786',
'AK45621886',
'AK45713886',
'AK45666886',
'AK45691096',
'AK45714096',
'AK45694196',
'AK45694296',
'AK45721396',
'AK45654396',
'AK45752496',
'AK45713496',
'AK45686496',
'AK45704596',
'AK45679596',
'AK45652696',
'AK45713696',
'AK45691796',
'AK45745796',
'AK45747796',
'AK45707896',
'AK45724996',
'AK43907996',
'AK45667007',
'AK45667107',
'AK45671207',
'AK45681207',
'AK45684207',
'AK45636207',
'AK45683407',
'AK45700507',
'AK45730507',
'AK45687507',
'AK45631607',
'AK45704607',
'AK45701707',
'AK45688707',
'AK45723907',
'AK45666907',
'AK45621217',
'AK45665217',
'AK45685217',
'AK45689217',
'AK45742317',
'AK43901417',
'AK45635417',
'AK45722517',
'AK45715517',
'AK45666717',
'AK45700817',
'AK45683817',
'AK45699917',
'AK45722027',
'AK45724027',
'AK45707127',
'AK45700227',
'AK45750227',
'AK45694227',
'AK45692327',
'AK45673327',
'AK45727327',
'AK45689527',
'AK45693627',
'AK45644727',
'AK45716727',
'AK45684037',
'AK45705037',
'AK45667037',
'AK45651137',
'AK44456137',
'AK45666137',
'AK45715337',
'AK45752437',
'AK45714437',
'AK45715537',
'AK45665537',
'AK45641637',
'AK45703637',
'AK45653737',
'AK45704737',
'AK45662837',
'AK45673837',
'AK45666837',
'AK45722937',
'AK45688047',
'AK44454147',
'AK45687147',
'AK45679347',
'AK45705447',
'AK45666447',
'AK45684547',
'AK45705547',
'AK45735547',
'AK45749547',
'AK45700647',
'AK45650847',
'AK45690847',
'AK45691847',
'AK45635847',
'AK45734947',
'AK45697947',
'AK45678947',
'AK45654057',
'AK45684257',
'AK45685257',
'AK45725357',
'AK45734457',
'AK45700557',
'AK45660557',
'AK45657557',
'AK44425657',
'AK45666757',
'AK45667757',
'AK45720857',
'AK45732857',
'AK45693857',
'AK45695957',
'AK45693267',
'AK45704467',
'AK45686667',
'AK45681767',
'AK45666767',
'AK45702867',
'AK45712867',
'AK45688867',
'AK45666967',
'AK45727967',
'AK45692177',
'AK45684177',
'AK45707177',
'AK45010277',
'AK45641277',
'AK45635277',
'AK45688277',
'AK45669377',
'AK45706477',
'AK45659577',
'AK45701777',
'AK45725777',
'AK45696777',
'AK45652877',
'AK45700087',
'AK45736087',
'AK45718187',
'AK45693287',
'AK45665387',
'AK45836387',
'AK45636487',
'AK45687487',
'AK45689487',
'AK45700587',
'AK45703587',
'AK45713587',
'AK45705587',
'AK45705687',
'AK45646687',
'AK45746687',
'AK45666687',
'AK45731787',
'AK45675887',
'AK45666887',
'AK45621987',
'AK45702987',
'AK45667987',
'AK45700097',
'AK45028097',
'AK45671197',
'AK45712197',
'AK45703197',
'AK45704597',
'AK45659697',
'AK45621897',
'AK45668897',
'AK45643997',
'AK45707997',
'AK45681108',
'AK45744208',
'AK45708208',
'AK45031308',
'AK45733308',
'AK45621408',
'AK45722408',
'AK45721508',
'AK45703508',
'AK45743508',
'AK45684508',
'AK45665508',
'AK45649508',
'AK45731608',
'AK45705708',
'AK45667708',
'AK45697808',
'AK45740908',
'AK45652908',
'AK45684908',
'AK45706908',
'AK45666908',
'AK45690018',
'AK45705018',
'AK45696018',
'AK45638018',
'AK45683118',
'AK45686218',
'AK45710418',
'AK45664418',
'AK45721518',
'AK45698518',
'AK45672718',
'AK43872718',
'AK45717718',
'AK45660818',
'AK45694818',
'AK45705818',
'AK45665818',
'AK45716818',
'AK45707818',
'AK45704918',
'AK45704028',
'AK45705328',
'AK45688328',
'AK45698328',
'AK45709328',
'AK45714428',
'AK45655428',
'AK45666428',
'AK45729428',
'AK45621528',
'AK45710628',
'AK45750628',
'AK45714628',
'AK45647628',
'AK45701728',
'AK45641728',
'AK45649728',
'AK45686828',
'AK45681928',
'AK45702928',
'AK45654928',
'AK45678928',
'AK45714038',
'AK45692138',
'AK45687238',
'AK45690338',
'AK45688338',
'AK45709438',
'AK45730538',
'AK45665638',
'AK45689638',
'AK45685738',
'AK45648738',
'AK45645938',
'AK45697938',
'AK45709938',
'AK45634048',
'AK45705048',
'AK45706048',
'AK45707148',
'AK45688248',
'AK45650348',
'AK45711348',
'AK45672348',
'AK45700448',
'AK45672448',
'AK45704448',
'AK45685448',
'AK45666548',
'AK45689548',
'AK45666648',
'AK45677648',
'AK45688648',
'AK45686748',
'AK43915848',
'AK45691948',
'AK45666948',
'AK44564058',
'AK45701158',
'AK45703258',
'AK45694258',
'AK45665658',
'AK45706658',
'AK45688658',
'AK45701758',
'AK45711758',
'AK45724758',
'AK45670858',
'AK45681858',
'AK45639858',
'AK45653958',
'AK45705958',
'AK45666068',
'AK45647068',
'AK45716168',
'AK45652368',
'AK45715368',
'AK45665368',
'AK45695368',
'AK45621468',
'AK45716668',
'AK45705868',
'AK45653968',
'AK45698278',
'AK45699278',
'AK45667478',
'AK45729478',
'AK45665578',
'AK45667578',
'AK45648578',
'AK45698578',
'AK45733678',
'AK45705678',
'AK45687678',
'AK45674778',
'AK45713878',
'AK44431088',
'AK45723088',
'AK45641188',
'AK45691188',
'AK45684188',
'AK45702288',
'AK45705388',
'AK45836388',
'AK45705588',
'AK45732688',
'AK45652788',
'AK45692788',
'AK45687788',
'AK45729788',
'AK45036988',
'AK45665098',
'AK45706098',
'AK45666098',
'AK45679098',
'AK45687198',
'AK45703398',
'AK45690498',
'AK45692498',
'AK45648498',
'AK45654598',
'AK45657698',
'AK45702798',
'AK45704009',
'AK45667009',
'AK45730209',
'AK45715209',
'AK45683309',
'AK45741409',
'AK45695409',
'AK45667409',
'AK45667509',
'AK45719509',
'AK45644609',
'AK45666609',
'AK45702709',
'AK45692709',
'AK45650809',
'AK45687809',
'AK45681909',
'AK45723119',
'AK45643119',
'AK45714219',
'AK45635219',
'AK45685219',
'AK45658219',
'AK45691319',
'AK45701419',
'AK45696419',
'AK45701519',
'AK45621519',
'AK45621719',
'AK45731819',
'AK45692819',
'AK45635819',
'AK45690919',
'AK45704919',
'AK45680129',
'AK45710229',
'AK45688229',
'AK45649229',
'AK45681329',
'AK45682429',
'AK45689429',
'AK45744529',
'AK45671629',
'AK45704629',
'AK45734629',
'AK45685629',
'AK45638629',
'AK45650829',
'AK45694829',
'AK45732929',
'AK45665929',
'AK45695929',
'AK45678929',
'AK45705039',
'AK45667039',
'AK45654139',
'AK45689139',
'AK45665239',
'AK45667239',
'AK45678239',
'AK45704339',
'AK45689339',
'AK45690439',
'AK43861439',
'AK45703439',
'AK45707439',
'AK45667439',
'AK45689539',
'AK45699539',
'AK45720639',
'AK45672639',
'AK45694739',
'AK45641839',
'AK44455939',
'AK45676939',
'AK45669049',
'AK45666449',
'AK43877449',
'AK45733549',
'AK45673549',
'AK45684549',
'AK45684849',
'AK45660949',
'AK45714949',
'AK45730159',
'AK45731259',
'AK45641259',
'AK45727259',
'AK45714359',
'AK45703459',
'AK45725459',
'AK45662559',
'AK45665559',
'AK45717559',
'AK45662659',
'AK45686659',
'AK45674759',
'AK45705759',
'AK45706759',
'AK45665859',
'AK45687859',
'AK45704959',
'AK45666959',
'AK45690069',
'AK45713069',
'AK45712169',
'AK45692169',
'AK45674169',
'AK45667169',
'AK45687169',
'AK45690269',
'AK45663269',
'AK45728269',
'AK45687469',
'AK45688469',
'AK45710669',
'AK45687669',
'AK45740769',
'AK45642769',
'AK45705769',
'AK45703869',
'AK45687869',
'AK45728869',
'AK45688869',
'AK45704969',
'AK45666969',
'AK45738969',
'AK45668969',
'AK45703179',
'AK45692379',
'AK45704379',
'AK45704479',
'AK45694479',
'AK45695479',
'AK45677479',
'AK45687479',
'AK45715579',
'AK45700679',
'AK45666779',
'AK45701879',
'AK45696879',
'AK45657979',
'AK45674089',
'AK45648089',
'AK45678189',
'AK45703289',
'AK45715289',
'AK45681389',
'AK45692389',
'AK44453389',
'AK45691489',
'AK45666489',
'AK45675889',
'AK45665989',
'AK45689989',
'AK45711199',
'AK45694199',
'AK45665199',
'AK45689199',
'AK45699199',
'AK43910299',
'AK45666299',
'AK45690399',
'AK45701399',
'AK45026399',
'AK45679399',
'AK45704499',
'AK45621599',
'AK45672599',
'AK44603599',
'AK45682699',
'AK45706699',
'AK44447699',
'AK45689699',
'AK44550799',
'AK45674799',
'AK45665799',
'AK45670999',
'AK45704999',
'AK45689999',
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
$this->logger->info('_data_patch_20200817_1400_CancelRegister.php end');

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