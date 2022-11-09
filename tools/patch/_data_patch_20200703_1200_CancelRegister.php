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
$this->logger->info('_data_patch_20200703_1200_CancelRegister.php start');

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
'AK44821886',
'AK45320167',
'AK45493346',
'AK45493425',
'AK45503102',
'AK45503105',
'AK45503119',
'AK45503126',
'AK45503142',
'AK45503155',
'AK45535097',
'AK45548113',
'AK45550628',
'AK45550730',
'AK45575375',
'AK45597416',
'AK45613800',
'AK45613822',
'AK45613829',
'AK45613844',
'AK45613853',
'AK45613865',
'AK45613902',
'AK45613906',
'AK45613911',
'AK45613922',
'AK45613930',
'AK45613935',
'AK45613967',
'AK45614092',
'AK45633153',
'AK45633171',
'AK45633176',
'AK45633187',
'AK45633204',
'AK45707457',
'AK45715699',
'AK45761009',
'AK45761015',
'AK45761021',
'AK45761027',
'AK45761057',
'AK45761061',
'AK45798657',
'AK45803967',
'AK45831853',
'AK45831944',
'AK45831982',
'AK45831991',
'AK45831999',
'AK45832006',
'AK45835839',
'AK45868539',
'AK45876340',
'AK45884189',
'AK45884226',
'AK45884350',
'AK45884361',
'AK45890456',
'AK45891147',
'AK45894532',
'AK45896360',
'AK45896368',
'AK45914171',
'AK45919400',
'AK45926268',
'AK45935204',
'AK45935754',
'AK45936751',
'AK45946906',
'AK45952976',
'AK45953304',
'AK45957509',
'AK45958784',
'AK45958805',
'AK45958813',
'AK45958820',
'AK45958824',
'AK45958829',
'AK45958863',
'AK45958919',
'AK45958928',
'AK45958939',
'AK45958952',
'AK45958961',
'AK45958978',
'AK45959024',
'AK45959128',
'AK45959130',
'AK45959132',
'AK45959137',
'AK45959234',
'AK45959238',
'AK45959246',
'AK45959249',
'AK45959252',
'AK45959269',
'AK45959278',
'AK45959286',
'AK45959293',
'AK45959299',
'AK45959330',
'AK45959340',
'AK45959341',
'AK45959343',
'AK45959354',
'AK45959356',
'AK45959357',
'AK45959364',
'AK45959376',
'AK45959379',
'AK45959388',
'AK45959390',
'AK45959393',
'AK45959394',
'AK45959397',
'AK45959409',
'AK45959411',
'AK45959415',
'AK45959422',
'AK45959425',
'AK45959427',
'AK45959447',
'AK45959454',
'AK45959460',
'AK45959475',
'AK45959504',
'AK45959510',
'AK45959525',
'AK45959534',
'AK45959567',
'AK45959589',
'AK45959613',
'AK45959630',
'AK45959646',
'AK45959657',
'AK45959701',
'AK45959730',
'AK45965304',
'AK45975827',
'AK45983649',
'AK45983659',
'AK45983673',
'AK45987035',
'AK45987039',
'AK45987053',
'AK45987060',
'AK45987095',
'AK45995432',
'AK46038318',
'AK46065479',
'AK46065488',
'AK46065492',
'AK46065501',
'AK46065511',
'AK46065523',
'AK46065546',
'AK46065561',
'AK46065565',
'AK46065568',
'AK46065576',
'AK46065585',
'AK46065611',
'AK46065616',
'AK46065621',
'AK46065626',
'AK46065636',
'AK46065652',
'AK46065655',
'AK46065659',
'AK46065666',
'AK46065670',
'AK46065674',
'AK46065676',
'AK46065682',
'AK46065683',
'AK46065686',
'AK46065688',
'AK46070763',
'AK46123608',
'AK46123616',
'AK46137321',
'AK46137331',
'AK46137333',
'AK46140044',
'AK46150266',
'AK46150271',
'AK46150273',
'AK46150279',
'AK46158390',
'AK46158400',
'AK46158411',
'AK46158421',
'AK46158433',
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
$this->logger->info('_data_patch_20200703_1200_CancelRegister.php end');

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
