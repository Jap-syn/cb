<?php
chdir(dirname(__DIR__));

// Setup autoloading
require 'init_autoloader.php';

use Coral\Base\Application\BaseApplicationAbstract;
use Zend\Db\Adapter\Adapter;
use Zend\Config\Reader\Ini;
use Coral\Base\BaseLog;
use models\Logic\LogicCampaign;
use models\Table\TableSystemEvent;
use models\Table\TableSystemProperty;

/**
 * アプリケーションクラスです。
 * 立替・売上管理が未作成の特定データがあったので、立替・売上管理を作成するパッチです
 *
 */
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
     * メール環境
     */
    public $mail;

    /**
     * アプリケーションを実行します。
     *
     * @access public
     */
    public function run() {
        $exitCode = 1;

        try {

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

$this->logger->info('_data_patch_20151205_1700.php start');

            $globalConfig = include __DIR__ . '/../config/autoload/global.php';
            // 接続時間を設定する
            $rds_session_timezone = $globalConfig['RDS_SESSION_TIMEZONE'];
            if (isset($rds_session_timezone)) {
                $this->dbAdapter->query('SET SESSION time_zone = :time_zone')->execute(array(':time_zone'=>$rds_session_timezone));
            }

$this->dbAdapter->getDriver()->getConnection()->beginTransaction();

            // --------------------------------
            // AK23340743(SEQ:23340286)
            // --------------------------------
            $oseq = 23340286;
            $this->registPayingAndSales($oseq);

            // --------------------------------
            // AK23340746(SEQ:23340289)
            // --------------------------------
            $oseq = 23340289;
            $this->registPayingAndSales($oseq);

$this->dbAdapter->getDriver()->getConnection()->commit();

$this->logger->info('_data_patch_20151205_1700.php end');
            $exitCode = 0; // 正常終了

        } catch( \Exception $e ) {
$this->dbAdapter->getDriver()->getConnection()->rollback();
            // エラーログを出力
            if ( isset($this->logger) ) {
$this->logger->err($e->getMessage());
$this->logger->err($e->getTraceAsString());

            }
        }

        // 終了コードを指定して処理終了
        exit($exitCode);

    }

    /**
     * 立替・売上管理を作成する
     * @param unknown $oseq
     */
    private function registPayingAndSales($oseq) {
        try {

            // 注文情報取得
            $order = $this->getOrderTable()->find($oseq)->current();
            if(!$order) {
                throw new \Exception(sprintf('cannot found order data: oseq = %s', $oseq));
            }

            // 立替・売上データの作成
            $ent = $this->getEnterpriseByOrderSeq($oseq);

            // キャンペーン期間中はキャンペーン情報で更新/登録する
            // 注文のサイト情報を取得する
            $sid = $order['SiteId'];
            // 詳細情報取得
            $logic = new LogicCampaign($this->dbAdapter);
            $campaign = $logic->getCampaignInfo($ent['EnterpriseId'], $sid);

            // 取得したデータをマージする
            $ent = array_merge($ent, $campaign);
            // 請求手数料(別送)を税込み金額に変換
            $mdlSysP = new TableSystemProperty($this->dbAdapter);
            $ent['ClaimFeeBS'] = $mdlSysP->getIncludeTaxAmount(date('Y-m-d'), $ent['ClaimFeeBS']);

            $savedata = $this->getPayingAndSalesTable()->newRow($oseq, $order['UseAmount'], $ent['SettlementFeeRate'], $ent['ClaimFeeBS']);
            $savedata = array_merge($savedata, array('RegistId' => 1, 'UpdateId' => 1));
            $seq_pas = $this->getPayingAndSalesTable()->saveNew($savedata);

            // 注文サマリを更新
            $this->getOrderSummaryTable()->updateSummary($oseq, 1);

            // AT_PayingAndSales登録
            $mdl_atpas = new \models\Table\ATablePayingAndSales($this->dbAdapter);
            $mdl_atpas->saveNew(array('Seq' => $seq_pas));

            return $this;
        } catch(\Exception $innerError) {
            throw $innerError;
        }
    }

    /**
     * 指定注文を所有する事業者のデータを取得する
     *
     * @param int $oseq 注文SEQ
     * @return array 事業者データ
     */
    public function getEnterpriseByOrderSeq($oseq) {
        $order = $this->getOrderTable()->find($oseq)->current();
        if(!$order) {
            throw new \Exception(sprintf('cannot found order data: oseq = %s', $oseq));
        }
        $ent = $this->getEnterpriseTable()->find($order['EnterpriseId'])->current();
        return $ent;
    }


    /**
     * TableOrderのインスタンスを取得する
     *
     * @return TableOrder
     */
    public function getOrderTable() {
        return new \models\Table\TableOrder($this->dbAdapter);
    }

    /**
     * TableEnterpriseのインスタンスを取得する
     *
     * @return TableEnterprise
     */
    public function getEnterpriseTable() {
        return new \models\Table\TableEnterprise($this->dbAdapter);
    }

    /**
     * TableOemSettlementFeeを取得する
     *
     * @return TableOemSettlementFee
     */
    public function getOemSettlementFeeTable() {
        return new \models\Table\TableOemSettlementFee($this->dbAdapter);
    }

    /**
     * TableOrderItemsを取得する
     *
     * @return TableOrderItems
     */
    public function getOrderItemsTable() {
        return new \models\Table\TableOrderItems($this->dbAdapter);
    }

    /**
     * TableOrderSummaryを取得する
     *
     * @return TableOrderSummary
     */
    public function getOrderSummaryTable() {
        return new \models\Table\TableOrderSummary($this->dbAdapter);
    }

    /**
     * TableDeliMethodを取得する
     *
     * @return TableDeliMethod
     */
    public function getDeliveryMethodMaster() {
        return new \models\Table\TableDeliMethod($this->dbAdapter);
    }

    /**
     * TablePayingAndSalesを取得する
     *
     * @return TablePayingAndSales
     */
    public function getPayingAndSalesTable() {
        return new \models\Table\TablePayingAndSales($this->dbAdapter);
    }

    /**
     * LogicDeliveryMethodを取得する
     *
     * @return LogicDeliveryMethod
     */
    public function getDeliveryMethodLogic() {
        return new LogicDeliveryMethod($this->dbAdapter);
    }


}

Application::getInstance()->run();
