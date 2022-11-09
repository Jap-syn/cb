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

/**
 * アプリケーションクラスです。
 *
 */
use Coral\Base\Application\BaseApplicationAbstract;
use Coral\Base\BaseLog;
use Zend\Db\Adapter\Adapter;
use Zend\Config\Reader\Ini;
use models\Table\ATablePayingAndSales;
use models\Table\TableUser;
use models\Table\TableEnterpriseCampaign;
use models\Table\TablePayingAndSales;
use models\Table\TableSystemProperty;
use models\Logic\LogicCampaign;

class Application extends BaseApplicationAbstract {
    protected $_application_id = 'tools';

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

            // データベースアダプタをiniファイルから初期化します
            $configPath = __DIR__ . '/../module/cbadmin/config/config.ini';

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

$this->logger->info('createPayingAndSales.php start');

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

            // トランザクション開始
            $this->dbAdapter->getDriver()->getConnection()->beginTransaction();

            // 加盟店顧客テーブルを更新する(申込ステータスの2:完了化)
            $this->createPayingAndSales($userId);

            // コミット
            $this->dbAdapter->getDriver()->getConnection()->commit();

$this->logger->info('createPayingAndSales.php end');
            $exitCode = 0;

        } catch( \Exception $e ) {
            $this->dbAdapter->getDriver()->getConnection()->rollback();
            // エラーログを出力
            if ( isset($this->logger) ) {
$this->logger->err($e->getMessage());
            }
        }

        // 終了コードを指定して処理終了
        exit($exitCode);

    }

    /**
     * 2-1. 対象の注文を検索し、全件ループ
     *
     * @param int $prmUserId ユーザーID
     */
    protected function createPayingAndSales($prmUserId) {

        // 対象注文データの抽出
        $sql = <<<EOQ
SELECT *
FROM   T_Order o
WHERE  1 = 1
AND    o.DataStatus >= 41
AND    o.DataStatus < 91
AND    NOT EXISTS
       (SELECT *
        FROM   T_PayingAndSales pas
        WHERE  pas.OrderSeq = o.OrderSeq
       )
EOQ;
        $orderList = $this->dbAdapter->query($sql)->execute();

        foreach ($orderList as $orderRow) {
            $logic = new LogicCampaign($this->dbAdapter);
            $mdlsys = new TableSystemProperty($this->dbAdapter);
            $pasTable = new TablePayingAndSales($this->dbAdapter);
            $mdlatpas = new ATablePayingAndSales($this->dbAdapter);

            // 2-1-1. 注文に紐づく請求手数料を取得する。
            $campaign = $logic->getCampaignInfo($this->_enterpriseId, $orderRow['SiteId']);

            // 2-1-2. 税込率を取得する。
            $taxRate = ($mdlsys->getTaxRateAt(date('Y-m-d')) / 100);

            // 請求手数料
            $claimFee = (int)($campaign['ClaimFeeBS'] + ($campaign['ClaimFeeBS'] * $taxRate));

            // 算出した税込金額を上書き。
            $campaign['ClaimFeeBS'] = $claimFee;

            $pasRow = $pasTable->newRow(
                $orderRow['OrderSeq'],
                $orderRow['UseAmount'],
                $campaign['SettlementFeeRate'],
                $campaign['ClaimFeeBS']
            );

            $pasRow['RegistId'] = $prmUserId; // 登録者
            $pasRow['UpdateId'] = $prmUserId; // 更新者

            // 2-1-3. 立替・売上管理テーブルに登録するレコードを作成する。
            $seq = $pasTable->saveNew($pasRow);

            // 2-1-4. 立替・売上管理_会計テーブルに登録する。
            $mdlatpas->saveNew(array('Seq' => $seq));
        }
    }
}

Application::getInstance()->run();
