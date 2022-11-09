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
use models\Table\TableOrderSummary;
use models\Logic\LogicOrderRegister;


/**
 * アプリケーションクラスです。
 *
 */
class Application extends BaseApplicationAbstract {
    protected $_application_id = 'tools-ModifyCombinedData-batch';

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
     * @var Log
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

// バッチ開始
$this->logger->info('_data_patch_20170601_1000_ClaimFee.php start');

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

// 【サイトマスタ更新】
            // データ修正パッチ対象のデータを取得
            $sql = "";
            $sql .= " SELECT s.SiteId ";
            $sql .= "       ,s.ClaimFeeBS ";
            $sql .= "       ,s.OemClaimFee ";
            $sql .= "   FROM T_Site s ";
            $sql .= "  WHERE s.ClaimFeeBS = 160 ";

            $ri = $this->dbAdapter->query($sql)->execute(null);

            $i = 0;
            // 取得できたデータをループ
            foreach ($ri as $row) {
                $i ++;
                $sql = "";
                $sql .= " UPDATE T_Site SET ";
                $sql .= "        ClaimFeeBS = 169";
                $sql .= "  WHERE SiteId = " .$row['SiteId'] ;

                $this->dbAdapter->query($sql)->execute(null);

                if ($row['OemClaimFee'] == 160){
                    $sql = "";
                    $sql .= " UPDATE T_Site set ";
                    $sql .= "        OemClaimFee = 169";
                    $sql .= "  WHERE SiteId = " .$row['SiteId'] ;

                    $this->dbAdapter->query($sql)->execute(null);
                }

                //1000件でCommit
                if ($i % 1000 == 0) {
                    $this->dbAdapter->getDriver()->getConnection()->commit();
                    $this->dbAdapter->getDriver()->getConnection()->beginTransaction();
                }
            }
            //最終Commit
            $this->dbAdapter->getDriver()->getConnection()->commit();

            $this->dbAdapter->getDriver()->getConnection()->beginTransaction();


// 【立替・売上管理更新】
            // データ修正パッチ対象のデータを取得
            $sql = "";
            $sql .= " SELECT p.Seq";
            $sql .= "       ,p.ClaimFee ";
            $sql .= "       ,p.ChargeAmount ";
            $sql .= "   FROM T_PayingAndSales p ";
            $sql .= "   INNER JOIN T_Order o ON p.OrderSeq = o.OrderSeq ";
            $sql .= "  WHERE o.DataStatus = 41 ";
            $sql .= "    AND p.PayingControlStatus = 0 ";
            $sql .= "    AND p.ClaimFee = 172 ";

            $ry = $this->dbAdapter->query($sql)->execute(null);

            $y = 0;
            // 取得できたデータ分ループする
            foreach ($ry as $row) {
                $y ++;
                $sql = "";
                $sql .= " UPDATE T_PayingAndSales SET ";
                $sql .= "        ClaimFee = 182";
                $sql .= "       ,ChargeAmount =" .($row['ChargeAmount'] - 10) ;
                $sql .= "  WHERE Seq = " .$row['Seq'] ;

                $this->dbAdapter->query($sql)->execute(null);

                //1000件でCommit
                if ($y % 1000 == 0) {
                    $this->dbAdapter->getDriver()->getConnection()->commit();
                    $this->dbAdapter->getDriver()->getConnection()->beginTransaction();
                }
            }

            //最終Commit
            $this->dbAdapter->getDriver()->getConnection()->commit();

            $exitCode = 0; // 正常終了

// バッチ終了
$this->logger->info('UPDATE CNT T_Site:' + $i);
$this->logger->info('UPDATE CNT T_PayingAndSales:' + $y);
$this->logger->info('_data_patch_20170601_1000_ClaimFee.php end');


        } catch( \Exception $e ) {
            try{
                $this->dbAdapter->getDriver()->getConnection()->rollback();
            } catch ( \Exception $err) { }

            // エラーログを出力
            if ( isset($this->logger) ) {
$this->logger->err($e->getMessage());
$this->logger->err($e->getTraceAsString());
            }
        }
        // 終了コードを指定して処理終了
        exit($exitCode);
    }
}

Application::getInstance()->run();
