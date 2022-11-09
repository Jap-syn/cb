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
$this->logger->info('_data_patch_20191210_1700_CancelRegister.php start');

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
'AK39310448',
'AK39313168',
'AK39759383',
'AK39762766',
'AK39763844',
'AK39764244',
'AK39764710',
'AK39766440',
'AK39769519',
'AK39779649',
'AK40487722',
'AK40487791',
'AK40487796',
'AK40487816',
'AK40487855',
'AK40487859',
'AK40487883',
'AK40488385',
'AK40488544',
'AK40489361',
'AK40489879',
'AK40490466',
'AK40490509',
'AK40490590',
'AK40491141',
'AK40491178',
'AK40491213',
'AK40491218',
'AK40491271',
'AK40491284',
'AK40491301',
'AK40491378',
'AK40491435',
'AK40491496',
'AK40491499',
'AK40491529',
'AK40491704',
'AK40491737',
'AK40491738',
'AK40491743',
'AK40492035',
'AK40492164',
'AK40492167',
'AK40492170',
'AK40492233',
'AK40492254',
'AK40492258',
'AK40492268',
'AK40492289',
'AK40492308',
'AK40492344',
'AK40492425',
'AK40492640',
'AK40492646',
'AK40492781',
'AK40492787',
'AK40492826',
'AK40492880',
'AK40492901',
'AK40492908',
'AK40493105',
'AK40493128',
'AK40493138',
'AK40493159',
'AK40493176',
'AK40493189',
'AK40493214',
'AK40493231',
'AK40493333',
'AK40493489',
'AK40493507',
'AK40493555',
'AK40493590',
'AK40493591',
'AK40493593',
'AK40493602',
'AK40493614',
'AK40493619',
'AK40493655',
'AK40493657',
'AK40493722',
'AK40493759',
'AK40493902',
'AK40493939',
'AK40494072',
'AK40494087',
'AK40494132',
'AK40494143',
'AK40494188',
'AK40494263',
'AK40494394',
'AK40494472',
'AK40494487',
'AK40494558',
'AK40494588',
'AK40494673',
'AK40494793',
'AK40494963',
'AK40495041',
'AK40495094',
'AK40495223',
'AK40495277',
'AK40495282',
'AK40495427',
'AK40495770',
'AK40495795',
'AK40495832',
'AK40496017',
'AK40496329',
'AK40496355',
'AK40497494',
'AK40497505',
'AK40497864',
'AK40497875',
'AK40497951',
'AK40497965',
'AK40497977',
'AK40497983',
'AK40498083',
'AK40498092',
'AK40498166',
'AK40498182',
'AK40498230',
'AK40498236',
'AK40498275',
'AK40498334',
'AK40498339',
'AK40498343',
'AK40498368',
'AK40498377',
'AK40498420',
'AK40498437',
'AK40498478',
'AK40498540',
'AK40498564',
'AK40498571',
'AK40498575',
'AK40498588',
'AK40498607',
'AK40498645',
'AK40498646',
'AK40498648',
'AK40498722',
'AK40498744',
'AK40498784',
'AK40498831',
'AK40498953',
'AK40499122',
'AK40499173',
'AK40499236',
'AK40499308',
'AK40499520',
'AK40499547',
'AK40499678',
'AK40499709',
'AK40499742',
'AK40499745',
'AK40499750',
'AK40499767',
'AK40499782',
'AK40499809',
'AK40499856',
'AK40499857',
'AK40499986',
'AK40500062',
'AK40500092',
'AK40500132',
'AK40500142',
'AK40500145',
'AK40500187',
'AK40500244',
'AK40500296',
'AK40500393',
'AK40500449',
'AK40500646',
'AK40500694',
'AK40500717',
'AK40500760',
'AK40500876',
'AK40501057',
'AK40501067',
'AK40501081',
'AK40501215',
'AK40501430',
'AK40501487',
'AK40501593',
'AK40501597',
'AK40501608',
'AK40501621',
'AK40501693',
'AK40501711',
'AK40501734',
'AK40501774',
'AK40501783',
'AK40501785',
'AK40501945',
'AK40502002',
'AK40502113',
'AK40502118',
'AK40502120',
'AK40502123',
'AK40502129',
'AK40502130',
'AK40502142',
'AK40502150',
'AK40502188',
'AK40502213',
'AK40502237',
'AK40502253',
'AK40502294',
'AK40502333',
'AK40502417',
'AK40502699',
'AK40678192',
'AK40678220',
'AK40678229',
'AK40678245',
'AK40678246',
'AK40678263',
'AK40678295',
'AK40678340',
'AK40678393',
'AK40678396',
'AK40678471',
'AK40678482',
'AK40678497',
'AK40678500',
'AK40678503',
'AK40678516',
'AK40678528',
'AK40678535',
'AK40678542',
'AK40678544',
'AK40678547',
'AK40678574',
'AK40678633',
'AK40678653',
'AK40678766',
'AK40678809',
'AK40678882',
'AK40678909',
'AK40678969',
'AK40678974',
'AK40679018',
'AK40679050',
'AK40679063',
'AK40679065',
'AK40679070',
'AK40679071',
'AK40679075',
'AK40679076',
'AK40679085',
'AK40679104',
'AK40679115',
'AK40679138',
'AK40679151',
'AK40679175',
'AK40679213',
'AK40679219',
'AK40679449',
'AK40679519',
'AK40679614',
'AK40679683',
'AK40679773',
'AK40679830',
'AK40679859',
'AK40679872',
'AK40679885',
'AK40679906',
'AK40680004',
'AK40680076',
'AK40680094',
'AK40680143',
'AK40680146',
'AK40680189',
'AK40680199',
'AK40680238',
'AK40680250',
'AK40680257',
'AK40680272',
'AK40680283',
'AK40680289',
'AK40680304',
'AK40680323',
'AK40680334',
'AK40680341',
'AK40680359',
'AK40680567',
'AK40680591',
'AK40680613',
'AK40680692',
'AK40680704',
'AK40680720',
'AK40680776',
'AK40680822',
'AK40680824',
'AK40680879',
'AK40680914',
'AK40680920',
'AK40680952',
'AK40680957',
'AK40680962',
'AK40680977',
'AK40680988',
'AK40681005',
'AK40681054',
'AK40681218',
'AK40681287',
'AK40681301',
'AK40681337',
'AK40681394',
'AK40681402',
'AK40681417',
'AK40681461',
'AK40681525',
'AK40681536',
'AK40681543',
'AK40681578',
'AK40681609',
'AK40681634',
'AK40681696',
'AK40681720',
'AK40681744',
'AK40681764',
'AK40681878',
'AK40681885',
'AK40681920',
'AK40681938',
'AK40681950',
'AK40681975',
'AK40681996',
'AK40682006',
'AK40682019',
'AK40682093',
'AK40682203',
'AK40682209',
'AK40682231',
'AK40682248',
'AK40682276',
'AK40682300',
'AK40682304',
'AK40682315',
'AK40682317',
'AK40682356',
'AK40682453',
'AK40682499',
'AK40682509',
'AK40682675',
'AK40682678',
'AK40682705',
'AK40682706',
'AK40682708',
'AK40682810',
'AK40682815',
'AK40682824',
'AK40682850',
'AK40682919',
'AK40682953',
'AK40682966',
'AK40683010',
'AK40683175',
'AK40683180',
'AK40683210',
'AK40683287',
'AK40683312',
'AK40683355',
'AK40683364',
'AK40683433',
'AK40683480',
'AK40683486',
'AK40683509',
'AK40683520',
'AK40683523',
'AK40683548',
'AK40683559',
'AK40683741',
'AK40683742',
'AK40683743',
'AK40683744',
'AK40683757',
'AK40683777',
'AK40683782',
'AK40683785',
'AK40683816',
'AK40683821',
'AK40683832',
'AK40683848',
'AK40683866',
'AK40683873',
'AK40683891',
'AK40683894',
'AK40683899',
'AK40683919',
'AK40683920',
'AK40683963',
'AK40684106',
'AK40684170',
'AK40684173',
'AK40684187',
'AK40684194',
'AK40684196',
'AK40684223',
'AK40684311',
'AK40684325',
'AK40684408',
'AK40684504',
'AK40684508',
'AK40684532',
'AK40684565',
'AK40684610',
'AK40684629',
'AK40684633',
'AK40684651',
'AK40684746',
'AK40684759',
'AK40684772',
'AK40684773',
'AK40684834',
'AK40684847',
'AK40684849',
'AK40684860',
'AK40684878',
'AK40684881',
'AK40684884',
'AK40684932',
'AK40685007',
'AK40685037',
'AK40685039',
'AK40685057',
'AK40685066',
'AK40685082',
'AK40685086',
'AK40685142',
'AK40685158',
'AK40685182',
'AK40685245',
'AK40685261',
'AK40685275',
'AK40685281',
'AK40685285',
'AK40685286',
'AK40685316',
'AK40685384',
'AK40685402',
'AK40685416',
'AK40685465',
'AK40685471',
'AK40685481',
'AK40685484',
'AK40685495',
'AK40685532',
'AK40685672',
'AK40685710',
'AK40685751',
'AK40685771',
'AK40685802',
'AK40685944',
'AK40685955',
'AK40685974',
'AK40686033',
'AK40686080',
'AK40686081',
'AK40686124',
'AK40686160',
'AK40686161',
'AK40686174',
'AK40686221',
'AK40686229',
'AK40686230',
'AK40686237',
'AK40686246',
'AK40686325',
'AK40686350',
'AK40686363',
'AK40686404',
'AK40686411',
'AK40686420',
'AK40686426',
'AK40686436',
'AK40686438',
'AK40686455',
'AK40686501',
'AK40686515',
'AK40686522',
'AK40686575',
'AK40686585',
'AK40686607',
'AK40686612',
'AK40686653',
'AK40686659',
'AK40686691',
'AK40686706',
'AK40686746',
'AK40686766',
'AK40686807',
'AK40686852',
'AK40686918',
'AK40686940',
'AK40686957',
'AK40686960',
'AK40686961',
'AK40686965',
'AK40686996',
'AK40687005',
'AK40687051',
'AK40687072',
'AK40687094',
'AK40687118',
'AK40687326',
'AK40687329',
'AK40687357',
'AK40687422',
'AK40687429',
'AK40687504',
'AK40687530',
'AK40687596',
'AK40687690',
'AK40687730',
'AK40687857',
'AK40687882',
'AK40687892',
'AK40687945',
'AK40687968',
'AK40688099',
'AK40688132',
'AK40688141',
'AK40688218',
'AK40688230',
'AK40688231',
'AK40688276',
'AK40688302',
'AK40688334',
'AK40688410',
'AK40688425',
'AK40688449',
'AK40688456',
'AK40688475',
'AK40688525',
'AK40688529',
'AK40688600',
'AK40688625',
'AK40688643',
'AK40688662',
'AK40688669',
'AK40688712',
'AK40688721',
'AK40688790',
'AK40688914',
'AK40688936',
'AK40688958',
'AK40688966',
'AK40688970',
'AK40689022',
'AK40689039',
'AK40689057',
'AK40689075',
'AK40689077',
'AK40689202',
'AK40689275',
'AK40689296',
'AK40689367',
'AK40689388',
'AK40689503',
'AK40689527',
'AK40689659',
'AK40689716',
'AK40689722',
'AK40689775',
'AK40689818',
'AK40689891',
'AK40690020',
'AK40690047',
'AK40690105',
'AK40690164',
'AK40690209',
'AK40690216',
'AK40690272',
'AK40690276',
'AK40690284',
'AK40690373',
'AK40690426',
'AK40690470',
'AK40690534',
'AK40690564',
'AK40690777',
'AK40690823',
'AK40690835',
'AK40690877',
'AK40690927',
'AK40690982',
'AK40690992',
'AK40691000',
'AK40691041',
'AK40691070',
'AK40691107',
'AK40691183',
'AK40691305',
'AK40691336',
'AK40691387',
'AK40691409',
'AK40691417',
'AK40691429',
'AK40691471',
'AK40691476',
'AK40691777',
'AK40691783',
'AK40691873',
'AK40692026',
'AK40692073',
'AK40692081',
'AK40692132',
'AK40692170',
'AK40692191',
'AK40692300',
'AK40692325',
'AK40692328',
'AK40692339',
'AK40692347',
'AK40692352',
'AK40692363',
'AK40692364',
'AK40692365',
'AK40692367',
'AK40692438',
'AK40692488',
'AK40692651',
'AK40692752',
'AK40692763',
'AK40692765',
'AK40692781',
'AK40692852',
'AK40692887',
'AK40692928',
'AK40692939',
'AK40692960',
'AK40693005',
'AK40693009',
'AK40693070',
'AK40693079',
'AK40693143',
'AK40693145',
'AK40693154',
'AK40693171',
'AK40693315',
'AK40693368',
'AK40693386',
'AK40693463',
'AK40693470',
'AK40693539',
'AK40693568',
'AK40693576',
'AK40693602',
'AK40693614',
'AK40693626',
'AK40693634',
'AK40693642',
'AK40693650',
'AK40693661',
'AK40693671',
'AK40693690',
'AK40693697',
'AK40693745',
'AK40693759',
'AK40693772',
'AK40693806',
'AK40693882',
'AK40693895',
'AK40694000',
'AK40694037',
'AK40694071',
'AK40694099',
'AK40694120',
'AK40694139',
'AK40694292',
'AK40694298',
'AK40694312',
'AK40694397',
'AK40694436',
'AK40694477',
'AK40694487',
'AK40694530',
'AK40694531',
'AK40694534',
'AK40694540',
'AK40694553',
'AK40694558',
'AK40694564',
'AK40694567',
'AK40694586',
'AK40694620',
'AK40694621',
'AK40694634',
'AK40694644',
'AK40694650',
'AK40694651',
'AK40694653',
'AK40694689',
'AK40694762',
'AK40694896',
'AK40694955',
'AK40694994',
'AK40695010',
'AK40695069',
'AK40695136',
'AK40695171',
'AK40695315',
'AK40695316',
'AK40695390',
'AK40695546',
'AK40695560',
'AK40695628',
'AK40695641',
'AK40695672',
'AK40695789',
'AK40695935',
'AK40695981',
'AK40696013',
'AK40696124',
'AK40696150',
'AK40696227',
'AK40696251',
'AK40696273',
'AK40696288',
'AK40696315',
'AK40696757',
'AK40696784',
'AK40697191',
'AK40697208',
'AK40697300',
'AK40697473',
'AK40698233',
'AK40698246',
'AK40698506',
'AK40698539',
'AK40698609',
'AK40698894',
'AK40699084',
'AK40699106',
'AK40699316',
'AK40699362',
'AK40699445',
'AK40699476',
'AK40699601',
'AK40699631',
'AK40699638',
'AK40699644',
'AK40699796',
'AK40700528',
'AK40700685',
'AK40702308',
'AK40702450',
'AK40702561',
'AK40704940',
'AK40705251',
'AK40705268',
'AK40824615',
'AK40824681',
'AK40824793',
'AK40824817',
'AK40824840',
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
$this->logger->info('_data_patch_20191210_1700_CancelRegister.php end');

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
