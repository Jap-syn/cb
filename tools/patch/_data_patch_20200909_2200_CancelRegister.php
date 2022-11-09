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
$this->logger->info('_data_patch_20200909_2200_CancelRegister.php start');

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
'SE24827067',
'SE25004230',
'SE25065236',
'SE25043411',
'SE25143105',
'SE25187633',
'SE25187544',
'SE25228375',
'SE25379190',
'SE25253230',
'SE25422939',
'SE25435851',
'SE25420768',
'SE25467145',
'SE25447417',
'SE25375968',
'SE25479618',
'SE25440564',
'SE25440172',
'SE25301551',
'SE25566567',
'SE25640707',
'SE25526543',
'SE25522501',
'SE25608171',
'SE25504614',
'SE25690401',
'SE25731797',
'SE25583983',
'SE25761586',
'SE25765509',
'SE25826277',
'SE25601683',
'SE25939090',
'SE26040045',
'SE26346534',
'SE26347623',
'SE26484829',
'SE26348191',
'SE26523814',
'SE26493932',
'SE26681766',
'SE26738062',
'SE26867668',
'SE26873377',
'SE26905996',
'SE26874606',
'SE26983501',
'SE27089603',
'SE27194455',
'SE27229607',
'SE27468587',
'SE27774800',
'SE27935737',
'SE28133700',
'SE28169637',
'SE28062043',
'SE28010422',
'SE28236437',
'SE28242865',
'SE28347487',
'SE28369053',
'SE28324701',
'SE28314189',
'SE28468397',
'SE28413503',
'SE28572977',
'SE28763563',
'SE28580145',
'SE28838299',
'SE28830952',
'SE28644556',
'SE28799138',
'SE28762919',
'SE28762882',
'SE28986648',
'SE28756775',
'SE28986669',
'SE29125636',
'SE29158741',
'SE28893186',
'SE29198643',
'SE29139997',
'SE28909769',
'SE29249094',
'SE29191974',
'SE29214165',
'SE29316662',
'SE29336094',
'SE29334331',
'SE29473175',
'SE29810829',
'SE29586639',
'SE29425660',
'SE29664679',
'SE29801265',
'SE29739014',
'SE30009690',
'SE30001890',
'SE29814396',
'SE29907635',
'SE29818190',
'SE30074935',
'SE30044035',
'SE29816258',
'SE30205536',
'SE30222334',
'SE30192785',
'SE30303299',
'SE30112340',
'SE29969400',
'SE30074998',
'SE30357776',
'SE30453476',
'SE30365903',
'SE30321913',
'SE30404181',
'SE30281824',
'SE30281071',
'SE30513656',
'SE30392958',
'SE30621425',
'SE30637105',
'SE30456358',
'SE30735906',
'SE30426145',
'SE30790680',
'SE30758645',
'SE30820131',
'SE30850828',
'SE30687257',
'SE31007562',
'SE30983202',
'SE31061172',
'SE31031462',
'SE30948762',
'SE30946303',
'SE31060149',
'SE31035621',
'SE31145167',
'SE30992535',
'SE30992540',
'SE31198065',
'SE30963577',
'SE31300416',
'SE31136736',
'SE31335345',
'SE31271911',
'SE31396116',
'SE31446426',
'SE31463224',
'SE31389073',
'SE31472416',
'SE31537121',
'SE31470814',
'SE31492856',
'SE31234452',
'SE31682878',
'SE31755355',
'SE31749915',
'SE31648945',
'SE31789161',
'SE31856094',
'SE31814611',
'SE31856692',
'SE32051488',
'SE32070604',
'SE32120628',
'SE32177491',
'SE32204764',
'SE31972558',
'SE32164721',
'SE31985579',
'SE31851643',
'SE32070146',
'SE32050880',
'SE32243945',
'SE32375156',
'SE32243926',
'SE32414516',
'SE32514585',
'SE32549947',
'SE32541043',
'SE32530069',
'SE32407536',
'SE32745521',
'SE32752586',
'SE32766819',
'SE32827178',
'SE32708138',
'SE32850482',
'SE32540514',
'SE32616387',
'SE32616406',
'SE32601674',
'SE33006656',
'SE32857770',
'SE32787349',
'SE33200285',
'SE33198773',
'SE33198857',
'SE33304595',
'SE33359116',
'SE33214018',
'SE33418477',
'SE33511331',
'SE33196173',
'SE33196255',
'SE33600477',
'SE33202393',
'SE33636002',
'SE33578984',
'SE33691992',
'SE33672929',
'SE33663896',
'SE33670525',
'SE33510943',
'SE33599827',
'SE33902145',
'SE33537505',
'SE33537548',
'SE33995586',
'SE34023194',
'SE33641192',
'SE34052120',
'SE33725462',
'SE33659296',
'SE34100161',
'SE33791783',
'SE34107212',
'SE34151432',
'SE34146477',
'SE34185060',
'SE34218580',
'SE34535695',
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
                    $logic->applies($oseq, $reason, $reasonCode, 0, false, $userId);
                    $this->logger->info('<CancelRegister> [' . $orderId . '] Complete!! ');
                } catch(OrderCancelException $oce) {
                    $this->logger->warn('<CancelRegister> [' . $orderId . '] Order Is Not Cancel Message = ' . $oce->getMessage());
                    $this->logger->warn('<CancelRegister> [' . $orderId . '] ' . $oce->getTraceAsString());
                }

            }

            // $this->dbAdapter->getDriver()->getConnection()->rollback();
            $this->dbAdapter->getDriver()->getConnection()->commit();

            $exitCode = 0; // 正常終了
$this->logger->info('_data_patch_20200909_2200_CancelRegister.php end');

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
