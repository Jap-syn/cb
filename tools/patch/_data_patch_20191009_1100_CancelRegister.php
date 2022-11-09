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
$this->logger->info('_data_patch_20191009_1100_CancelRegister.php start');

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
                    'AK38718266',
                    'AK38745636',
                    'AK38745688',
                    'AK38746236',
                    'AK38752325',
                    'AK38754816',
                    'AK38760477',
                    'AK38766499',
                    'AK38766500',
                    'AK38766624',
                    'AK38771590',
                    'AK38776285',
                    'AK38776288',
                    'AK38783681',
                    'AK38789164',
                    'AK38790009',
                    'AK38790054',
                    'AK38793244',
                    'AK38798450',
                    'AK38798453',
                    'AK38809197',
                    'AK38809912',
                    'AK38818475',
                    'AK38820431',
                    'AK38822317',
                    'AK38823719',
                    'AK38833726',
                    'AK38834252',
                    'AK38835116',
                    'AK38835677',
                    'AK38835686',
                    'AK38845744',
                    'AK38851628',
                    'AK38851905',
                    'AK38867870',
                    'AK38868131',
                    'AK38868549',
                    'AK38868680',
                    'AK38873561',
                    'AK38876289',
                    'AK38883304',
                    'AK38883372',
                    'AK38888412',
                    'AK38895265',
                    'AK38901170',
                    'AK38911909',
                    'AK38926318',
                    'AK38947092',
                    'AK38960967',
                    'AK38969949',
                    'AK38975334',
                    'AK38984752',
                    'AK38985370',
                    'AK38991133',
                    'AK38991153',
                    'AK38993915',
                    'AK39014953',
                    'AK39016680',
                    'AK39017592',
                    'AK39019392',
                    'AK39046137',
                    'AK39057178',
                    'AK39057934',
                    'AK39065149',
                    'AK39080150',
                    'AK39080858',
                    'AK39085410',
                    'AK39087214',
                    'AK39100320',
                    'AK39108546',
                    'AK39153222',
                    'AK39153350',
                    'AK39160917',
                    'AK39184067',
                    'AK39191140',
                    'AK39207023',
                    'AK39209520',
                    'AK39213267',
                    'AK39213340',
                    'AK39257160',
                    'AK39353430',
                    'AK39387840',
                    'AK39399869',
                    'AK39588739',
                    'AK39312300',
                    'AK39311400',
                    'AK39304600',
                    'AK39304110',
                    'AK39313510',
                    'AK39309510',
                    'AK39310710',
                    'AK39305620',
                    'AK39315720',
                    'AK39312920',
                    'AK39313230',
                    'AK39304230',
                    'AK39303630',
                    'AK39307830',
                    'AK39309830',
                    'AK39313340',
                    'AK39305340',
                    'AK39301350',
                    'AK39306350',
                    'AK39308350',
                    'AK39310650',
                    'AK39313950',
                    'AK39305950',
                    'AK39303260',
                    'AK39304460',
                    'AK39313560',
                    'AK39314860',
                    'AK39301170',
                    'AK39230270',
                    'AK39302670',
                    'AK39308870',
                    'AK39305970',
                    'AK39302180',
                    'AK39306180',
                    'AK39308480',
                    'AK39304590',
                    'AK39313790',
                    'AK39312601',
                    'AK39307011',
                    'AK39306711',
                    'AK39312221',
                    'AK39314221',
                    'AK39305631',
                    'AK39311931',
                    'AK39306241',
                    'AK39305541',
                    'AK39312741',
                    'AK39303051',
                    'AK39312151',
                    'AK39315351',
                    'AK39309351',
                    'AK39313551',
                    'AK39314061',
                    'AK39304461',
                    'AK39308561',
                    'AK39313661',
                    'AK39312071',
                    'AK39301171',
                    'AK39310671',
                    'AK39306771',
                    'AK39312881',
                    'AK39302981',
                    'AK39311191',
                    'AK39304191',
                    'AK39301391',
                    'AK39316002',
                    'AK39308002',
                    'AK39315102',
                    'AK39312402',
                    'AK39303502',
                    'AK39313602',
                    'AK39312702',
                    'AK39304702',
                    'AK39311902',
                    'AK39303902',
                    'AK39310112',
                    'AK39317112',
                    'AK39302412',
                    'AK39322412',
                    'AK39317612',
                    'AK39306122',
                    'AK39312822',
                    'AK39308822',
                    'AK39304132',
                    'AK39311332',
                    'AK39304432',
                    'AK39311842',
                    'AK39306842',
                    'AK39309052',
                    'AK39306152',
                    'AK39303252',
                    'AK39306662',
                    'AK39313762',
                    'AK39308862',
                    'AK39306962',
                    'AK39316172',
                    'AK39314372',
                    'AK39313672',
                    'AK39312872',
                    'AK39304082',
                    'AK39316082',
                    'AK39307282',
                    'AK39302382',
                    'AK39315482',
                    'AK39306582',
                    'AK39308582',
                    'AK39310682',
                    'AK39313492',
                    'AK39312592',
                    'AK39315792',
                    'AK39301992',
                    'AK39310003',
                    'AK39316303',
                    'AK39303503',
                    'AK39310603',
                    'AK39303513',
                    'AK38907713',
                    'AK39311023',
                    'AK39309123',
                    'AK39307133',
                    'AK39308133',
                    'AK39303333',
                    'AK39310433',
                    'AK39305433',
                    'AK39305633',
                    'AK39312833',
                    'AK39230143',
                    'AK39316143',
                    'AK39301343',
                    'AK39316353',
                    'AK39304653',
                    'AK39303263',
                    'AK39312663',
                    'AK39308963',
                    'AK39307173',
                    'AK39230273',
                    'AK39312373',
                    'AK39313673',
                    'AK39307973',
                    'AK39301383',
                    'AK39315483',
                    'AK39313583',
                    'AK39312993',
                    'AK39309993',
                    'AK39315304',
                    'AK39307304',
                    'AK39316404',
                    'AK39306704',
                    'AK39315414',
                    'AK39314614',
                    'AK39302224',
                    'AK39315524',
                    'AK39311924',
                    'AK39306234',
                    'AK39313434',
                    'AK39311834',
                    'AK39314934',
                    'AK39304044',
                    'AK38907944',
                    'AK39301354',
                    'AK39305754',
                    'AK39301854',
                    'AK39311854',
                    'AK39316954',
                    'AK39316064',
                    'AK39316164',
                    'AK39319564',
                    'AK39304664',
                    'AK39304764',
                    'AK39311964',
                    'AK39305174',
                    'AK39301274',
                    'AK39312874',
                    'AK39301384',
                    'AK39313884',
                    'AK39305094',
                    'AK39304194',
                    'AK39314894',
                    'AK39306105',
                    'AK39305605',
                    'AK39314705',
                    'AK39309705',
                    'AK39312905',
                    'AK39313315',
                    'AK39305515',
                    'AK39301715',
                    'AK39305915',
                    'AK38908915',
                    'AK39301125',
                    'AK39302125',
                    'AK39315425',
                    'AK39302525',
                    'AK39230235',
                    'AK39311235',
                    'AK39316435',
                    'AK39312535',
                    'AK39301145',
                    'AK39312245',
                    'AK39309545',
                    'AK39316645',
                    'AK39302745',
                    'AK39323055',
                    'AK39308055',
                    'AK39307355',
                    'AK39317355',
                    'AK39322655',
                    'AK39305755',
                    'AK39312855',
                    'AK39323855',
                    'AK39305855',
                    'AK39305265',
                    'AK39310465',
                    'AK39311565',
                    'AK39301665',
                    'AK39311665',
                    'AK39305765',
                    'AK39306865',
                    'AK39304965',
                    'AK39306375',
                    'AK39304685',
                    'AK39316885',
                    'AK39316095',
                    'AK39308095',
                    'AK39302395',
                    'AK39302695',
                    'AK39311795',
                    'AK39312795',
                    'AK39314006',
                    'AK39373106',
                    'AK39319406',
                    'AK39316706',
                    'AK39317706',
                    'AK39313016',
                    'AK39308016',
                    'AK39312716',
                    'AK39312816',
                    'AK39311126',
                    'AK39304526',
                    'AK39301626',
                    'AK39301726',
                    'AK39311726',
                    'AK39316826',
                    'AK39309536',
                    'AK39313636',
                    'AK39312836',
                    'AK39305836',
                    'AK39312936',
                    'AK39304046',
                    'AK39306046',
                    'AK39301546',
                    'AK39313546',
                    'AK39312846',
                    'AK39313946',
                    'AK39312156',
                    'AK39315156',
                    'AK39303356',
                    'AK39315166',
                    'AK39304266',
                    'AK39305266',
                    'AK39312566',
                    'AK39305566',
                    'AK39308566',
                    'AK39313666',
                    'AK39313076',
                    'AK39302376',
                    'AK39303476',
                    'AK39313576',
                    'AK39301876',
                    'AK39306976',
                    'AK39306086',
                    'AK39316386',
                    'AK39312886',
                    'AK39304596',
                    'AK39314796',
                    'AK39306996',
                    'AK39311107',
                    'AK39307107',
                    'AK39306807',
                    'AK39315117',
                    'AK39302217',
                    'AK39308217',
                    'AK39306617',
                    'AK39305717',
                    'AK39312817',
                    'AK39303917',
                    'AK39316127',
                    'AK39304227',
                    'AK39305427',
                    'AK39315037',
                    'AK39305437',
                    'AK39305737',
                    'AK39313047',
                    'AK39316147',
                    'AK39312947',
                    'AK39310057',
                    'AK39303357',
                    'AK39305657',
                    'AK39307757',
                    'AK39309757',
                    'AK39311857',
                    'AK39315267',
                    'AK39313667',
                    'AK39314667',
                    'AK39306667',
                    'AK39313777',
                    'AK39302487',
                    'AK39313587',
                    'AK39307587',
                    'AK39305887',
                    'AK39302297',
                    'AK39314597',
                    'AK39317897',
                    'AK39313108',
                    'AK39305108',
                    'AK39307308',
                    'AK39301408',
                    'AK39302608',
                    'AK39317708',
                    'AK39303018',
                    'AK39305218',
                    'AK39309628',
                    'AK39313928',
                    'AK39313038',
                    'AK39307338',
                    'AK39305938',
                    'AK39313548',
                    'AK39315948',
                    'AK39301558',
                    'AK39305558',
                    'AK39305658',
                    'AK39308668',
                    'AK39313968',
                    'AK39302378',
                    'AK39304478',
                    'AK39301578',
                    'AK39307578',
                    'AK39311188',
                    'AK38917388',
                    'AK39308588',
                    'AK39315098',
                    'AK39324298',
                    'AK39305398',
                    'AK39303598',
                    'AK39302698',
                    'AK39315998',
                    'AK39313109',
                    'AK39313209',
                    'AK39308309',
                    'AK39307409',
                    'AK39312709',
                    'AK39305809',
                    'AK39314419',
                    'AK39305419',
                    'AK39315419',
                    'AK39305229',
                    'AK39305429',
                    'AK39308629',
                    'AK39301729',
                    'AK39311829',
                    'AK39312829',
                    'AK39305929',
                    'AK39312239',
                    'AK39312739',
                    'AK39311839',
                    'AK39301149',
                    'AK39302149',
                    'AK39303349',
                    'AK39304449',
                    'AK39308449',
                    'AK39322749',
                    'AK39312159',
                    'AK39313159',
                    'AK39302259',
                    'AK39304259',
                    'AK39304359',
                    'AK39314359',
                    'AK39307359',
                    'AK39305659',
                    'AK39308759',
                    'AK39311859',
                    'AK39307859',
                    'AK39309369',
                    'AK39307769',
                    'AK39314589',
                    'AK39313299',
                    'AK39306299',
                    'AK39313499',
                    'AK39306599',
                    'AK39305999',
                    'AK39316999',
                    'AB38957648',
                    'AB39236874',
                    'AB39236936',
                    'AB39339418',
                    'AB39339432',
                    'AB39339470',
                    'AB39339479',
                    'AB39339482',
                    'AB39339504',
                    'AB39339597',
                    'AB39339699',
                    'AB39339887',
                    'AB39339951',
                    'AB39339983',
                    'AB39340030',
                    'AB39340069',
                    'AB39340119',
                    'AB39340547',
                    'AB39340663',
                    'AB39340964',
                    'AB39340965',
                    'AB39341013',
                    'AB39341056',
                    'AB39341259',
                    'AB39341275',
                    'AB39341522',
                    'AB39341648',
                    'AB39342700',
                    'AB39342744',
                    'AB39343279',
                    'AB39343285',
                    'AB39343399',
                    'AB39343646',
                    'AB39343656',
                    'AB39343717',
                    'AB39343823',
                    'AB39343889',
                    'AB39343935',
                    'AB39344132',
                    'AB39344252',
                    'AB39344336',
                    'AB39344348',
                    'AB39344396',
                    'AB39344406',
                    'AB39344640',
                    'AB39345910',
                    'AB39346326',
                    'AB39347027',

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
$this->logger->info('_data_patch_20191009_1100_CancelRegister.php end');

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
