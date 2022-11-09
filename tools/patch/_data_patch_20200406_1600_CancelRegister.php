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
$this->logger->info('_data_patch_20200406_1600_CancelRegister.php start');

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
'AK43763020',
'AK43491920',
'AK43726920',
'AK43270230',
'AK43298830',
'AK43698140',
'AK43521540',
'AK43443740',
'AK43780160',
'AK43698160',
'AK43497560',
'AK43702660',
'AK43724470',
'AK43761670',
'AK43714970',
'AK43673280',
'AK43721880',
'AK43698190',
'AK43698101',
'AK43670701',
'AK43637801',
'AK43467111',
'AK43677111',
'AK43686211',
'AK43646511',
'AK43637611',
'AK43500421',
'AK43698231',
'AK43270241',
'AK43819241',
'AK43330061',
'AK43608861',
'AK43330071',
'AK43695271',
'AK43330081',
'AK43538581',
'AK43780881',
'AK43543802',
'AK43559902',
'AK43753212',
'AK43686412',
'AK43373322',
'AK43685132',
'AK43541632',
'AK43491632',
'AK43799042',
'AK43532842',
'AK43298842',
'AK43698052',
'AK43698152',
'AK43799452',
'AK43665552',
'AK43613652',
'AK43608852',
'AK43533952',
'AK43698162',
'AK43753362',
'AK43562962',
'AK43698172',
'AK43762472',
'AK43698182',
'AK43689182',
'AK43698192',
'AK43757892',
'AK43698103',
'AK43698203',
'AK43690503',
'AK43467113',
'AK43698213',
'AK43670713',
'AK43737813',
'AK43689913',
'AK43531033',
'AK43698233',
'AK43755333',
'AK43650633',
'AK43737733',
'AK43698043',
'AK43507343',
'AK43562443',
'AK43330053',
'AK43780853',
'AK43811263',
'AK43737763',
'AK43698073',
'AK43690173',
'AK43698173',
'AK43592573',
'AK43698083',
'AK43501683',
'AK43698093',
'AK43500993',
'AK43670704',
'AK43747904',
'AK43670714',
'AK43538024',
'AK43698224',
'AK43699424',
'AK43700624',
'AK43443744',
'AK43685954',
'AK43698064',
'AK43698164',
'AK43826264',
'AK43661864',
'AK43608864',
'AK43383674',
'AK43530084',
'AK43698184',
'AK43517684',
'AK43709094',
'AK43698194',
'AK43640494',
'AK43798994',
'AK43698205',
'AK43467115',
'AK43681715',
'AK43715325',
'AK43521825',
'AK43758925',
'AK43698035',
'AK43500535',
'AK43640535',
'AK43751935',
'AK43698145',
'AK43330055',
'AK43826155',
'AK43787455',
'AK43700955',
'AK43697075',
'AK43698175',
'AK43613775',
'AK43254385',
'AK43817195',
'AK43698195',
'AK43328795',
'AK43496895',
'AK43613506',
'AK43640706',
'AK43753216',
'AK43670716',
'AK43669716',
'AK43502126',
'AK43765126',
'AK43698226',
'AK43757526',
'AK43640536',
'AK43613836',
'AK43298836',
'AK43641936',
'AK43707046',
'AK43270246',
'AK43691446',
'AK43640846',
'AK43759056',
'AK43698166',
'AK43529766',
'AK43722176',
'AK43698176',
'AK43553376',
'AK43776776',
'AK43330086',
'AK43698096',
'AK43592596',
'AK43669796',
'AK43847007',
'AK43698107',
'AK43669207',
'AK43613407',
'AK43529907',
'AK43698217',
'AK43698027',
'AK43270227',
'AK43809327',
'AK43792927',
'AK43698037',
'AK43809637',
'AK43655737',
'AK43698047',
'AK43507247',
'AK43706947',
'AK43708057',
'AK43698057',
'AK43698157',
'AK43737757',
'AK43522957',
'AK43639267',
'AK43695767',
'AK43681867',
'AK43510967',
'AK43524967',
'AK43330077',
'AK43745077',
'AK43698077',
'AK43698177',
'AK43613477',
'AK43613577',
'AK43501877',
'AK43698087',
'AK43507487',
'AK43762587',
'AK43791197',
'AK43698208',
'AK43538408',
'AK43793708',
'AK43679618',
'AK43799028',
'AK43698138',
'AK43639938',
'AK43653048',
'AK43613748',
'AK43737748',
'AK43543158',
'AK43270258',
'AK43286958',
'AK43698168',
'AK43692468',
'AK43659668',
'AK43663078',
'AK43613378',
'AK43744578',
'AK43744878',
'AK43676978',
'AK43687188',
'AK43560788',
'AK43663298',
'AK43687398',
'AK43799009',
'AK43670709',
'AK43737809',
'AK43698019',
'AK43662119',
'AK43539719',
'AK43804229',
'AK43698039',
'AK43507439',
'AK43330049',
'AK43698149',
'AK43328249',
'AK43656449',
'AK43330059',
'AK43679359',
'AK43756069',
'AK43270269',
'AK43501669',
'AK43479969',
'AK43698079',
'AK43645979',
'AK43698089',
'AK43686199',
'AK43534399',
'AK43758799',
'AK43798999',
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
$this->logger->info('_data_patch_20200406_1600_CancelRegister.php end');

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
