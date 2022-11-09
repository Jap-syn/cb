<?php
chdir(dirname(__DIR__));

// Setup autoloading
require 'init_autoloader.php';

use Coral\Base\Application\BaseApplicationAbstract;
use Zend\Db\Adapter\Adapter;
use Zend\Config\Reader\Ini;
use models\Logic\LogicMypage;
use Coral\Base\BaseLog;

/**
 * アプリケーションクラスです。
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
     * @var Adapter
     */
    public $dbAdapterNewOrderApi;

    /**
     * ログクラス
     *
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

            $configPath = __DIR__ . '/../module/cbadmin/config/config.ini';
            // データベースアダプタをiniファイルから初期化します
            $data = array();
            if (file_exists($configPath))
            {
                $reader = new Ini();
                $data = $reader->fromFile($configPath);
            }

            $this->dbAdapter = new Adapter($data['database']);

            // マイページ用
            $configPathNewOrderApi = __DIR__ . '/config/new_order_api.ini';
            // データベースアダプタをiniファイルから初期化します
            $dataMypage = array();
            if (file_exists($configPathNewOrderApi))
            {
                $readerNewOrderApi = new Ini();
                $dataNewOrderApi = $readerNewOrderApi->fromFile($configPathNewOrderApi);
            }

            $this->dbAdapterNewOrderApi = new Adapter($dataNewOrderApi['database']);

            // ログ設定の読み込み
            $logConfig = $data['log'];
            // 標準ログクラス初期化
            $this->logger = BaseLog::createFromArray( $logConfig );

            $this->logger->info('removePast2YearsOrderData.php start');

            $globalConfig = include __DIR__ . '/../config/autoload/global.php';
            // 接続時間を設定する
            $rds_session_timezone = $globalConfig['RDS_SESSION_TIMEZONE'];
            if (isset($rds_session_timezone)) {
                $this->dbAdapter->query('SET SESSION time_zone = :time_zone')->execute(array(':time_zone'=>$rds_session_timezone));
                $this->dbAdapterNewOrderApi->query('SET SESSION time_zone = :time_zone')->execute(array(':time_zone'=>$rds_session_timezone));
            }

            // 主処理実行
            $this->_exec();

            $this->logger->info('removePast2YearsOrderData.php end');
            $exitCode = 0; // 正常終了

        } catch(\Exception $e) {
            $this->dbAdapterNewOrderApi->query("UPDATE T_SyncControl SET SyncFlg=0, UpdateDate='".date('Y-m-d H:i:s')."' WHERE TableName='RemoveNewOrderApi'")->execute(null);
            // エラーログを出力
            if ( isset($this->logger) ) {
                $this->logger->err($e->getMessage());
            }
            $this->logger->info('removePast2YearsOrderData.php error end');
        }

        // 終了コードを指定して処理終了
        exit($exitCode);

    }

    private function _exec() {
        $is_error = false;

        $ctl = $this->dbAdapterNewOrderApi->query("UPDATE T_SyncControl SET SyncFlg=1, UpdateDate='".date('Y-m-d H:i:s')."' WHERE SyncFlg=0 AND TableName='RemoveNewOrderApi'")->execute(null);
        if ($ctl == 0) {
            $this->logger->info('多重起動のため処理終了.');
            return;
        }

        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->beginTransaction();
        $this->dbAdapterNewOrderApi->query('DELETE FROM T_Order WHERE RegistDate < '.date("Y-m-d", strtotime("-2 year")))->execute(null);
        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->commit();

        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->beginTransaction();
        $this->dbAdapterNewOrderApi->query('DELETE FROM AT_Order WHERE NOT EXISTS (SELECT * FROM T_Order o WHERE o.OrderSeq=AT_Order.OrderSeq)')->execute(null);
        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->commit();

        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->beginTransaction();
        $this->dbAdapterNewOrderApi->query('DELETE FROM T_Cancel WHERE NOT EXISTS (SELECT * FROM T_Order o WHERE o.OrderSeq=T_Cancel.OrderSeq)')->execute(null);
        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->commit();

        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->beginTransaction();
        $this->dbAdapterNewOrderApi->query('DELETE FROM T_CjMailHistory WHERE NOT EXISTS (SELECT * FROM T_Order o WHERE o.OrderSeq=T_CjMailHistory.OrderSeq)')->execute(null);
        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->commit();

        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->beginTransaction();
        $this->dbAdapterNewOrderApi->query('DELETE FROM T_CjOrderIdControl WHERE NOT EXISTS (SELECT * FROM T_Order o WHERE o.OrderSeq=T_CjOrderIdControl.OrderSeq)')->execute(null);
        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->commit();

        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->beginTransaction();
        $this->dbAdapterNewOrderApi->query('DELETE FROM T_CjResult WHERE NOT EXISTS (SELECT * FROM T_Order o WHERE o.OrderSeq=T_CjResult.OrderSeq)')->execute(null);
        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->commit();

        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->beginTransaction();
        $this->dbAdapterNewOrderApi->query('DELETE FROM T_CjResult_Detail WHERE NOT EXISTS (SELECT * FROM T_Order o WHERE o.OrderSeq=T_CjResult_Detail.OrderSeq)')->execute(null);
        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->commit();

        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->beginTransaction();
        $this->dbAdapterNewOrderApi->query('DELETE FROM T_CjResult_Error WHERE NOT EXISTS (SELECT * FROM T_Order o WHERE o.OrderSeq=T_CjResult_Error.OrderSeq)')->execute(null);
        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->commit();

        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->beginTransaction();
        $this->dbAdapterNewOrderApi->query('DELETE FROM T_ClaimControl WHERE NOT EXISTS (SELECT * FROM T_Order o WHERE o.OrderSeq=T_ClaimControl.OrderSeq)')->execute(null);
        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->commit();

        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->beginTransaction();
        $this->dbAdapterNewOrderApi->query('DELETE FROM T_ClaimHistory WHERE NOT EXISTS (SELECT * FROM T_Order o WHERE o.OrderSeq=T_ClaimHistory.OrderSeq)')->execute(null);
        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->commit();

        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->beginTransaction();
        $this->dbAdapterNewOrderApi->query('DELETE FROM T_CreditLog WHERE NOT EXISTS (SELECT * FROM T_Order o WHERE o.OrderSeq=T_CreditLog.OrderSeq)')->execute(null);
        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->commit();

        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->beginTransaction();
        $this->dbAdapterNewOrderApi->query('DELETE FROM T_JtcResult WHERE NOT EXISTS (SELECT * FROM T_Order o WHERE o.OrderSeq=T_JtcResult.OrderSeq)')->execute(null);
        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->commit();

        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->beginTransaction();
        $this->dbAdapterNewOrderApi->query('DELETE FROM T_JtcResult_Detail WHERE NOT EXISTS (SELECT * FROM T_Order o WHERE o.OrderSeq=T_JtcResult_Detail.OrderSeq)')->execute(null);
        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->commit();

        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->beginTransaction();
        $this->dbAdapterNewOrderApi->query('DELETE FROM T_MailSendHistory WHERE NOT EXISTS (SELECT * FROM T_Order o WHERE o.OrderSeq=T_MailSendHistory.OrderSeq)')->execute(null);
        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->commit();

        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->beginTransaction();
        $this->dbAdapterNewOrderApi->query('DELETE FROM T_OemSettlementFee WHERE NOT EXISTS (SELECT * FROM T_Order o WHERE o.OrderSeq=T_OemSettlementFee.OrderSeq)')->execute(null);
        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->commit();

        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->beginTransaction();
        $this->dbAdapterNewOrderApi->query('DELETE FROM T_OrderAddInfo WHERE NOT EXISTS (SELECT * FROM T_Order o WHERE o.OrderSeq=T_OrderAddInfo.OrderSeq)')->execute(null);
        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->commit();

        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->beginTransaction();
        $this->dbAdapterNewOrderApi->query('DELETE FROM T_OrderHistory WHERE NOT EXISTS (SELECT * FROM T_Order o WHERE o.OrderSeq=T_OrderHistory.OrderSeq)')->execute(null);
        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->commit();

        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->beginTransaction();
        $this->dbAdapterNewOrderApi->query('DELETE FROM T_OrderItems WHERE NOT EXISTS (SELECT * FROM T_Order o WHERE o.OrderSeq=T_OrderItems.OrderSeq)')->execute(null);
        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->commit();

        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->beginTransaction();
        $this->dbAdapterNewOrderApi->query('DELETE FROM T_OrderNotClose WHERE NOT EXISTS (SELECT * FROM T_Order o WHERE o.OrderSeq=T_OrderNotClose.OrderSeq)')->execute(null);
        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->commit();

        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->beginTransaction();
        $this->dbAdapterNewOrderApi->query('DELETE FROM T_CreditTransferAlert WHERE NOT EXISTS (SELECT * FROM T_Order o WHERE o.OrderSeq=T_CreditTransferAlert.OrderSeq)')->execute(null);
        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->commit();

        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->beginTransaction();
        $this->dbAdapterNewOrderApi->query('DELETE FROM T_OrderSummary WHERE NOT EXISTS (SELECT * FROM T_Order o WHERE o.OrderSeq=T_OrderSummary.OrderSeq)')->execute(null);
        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->commit();

        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->beginTransaction();
        $this->dbAdapterNewOrderApi->query('DELETE FROM T_PayingAndSales WHERE NOT EXISTS (SELECT * FROM T_Order o WHERE o.OrderSeq=T_PayingAndSales.OrderSeq)')->execute(null);
        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->commit();

        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->beginTransaction();
        $this->dbAdapterNewOrderApi->query('DELETE FROM T_ReceiptControl WHERE NOT EXISTS (SELECT * FROM T_Order o WHERE o.OrderSeq=T_ReceiptControl.OrderSeq)')->execute(null);
        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->commit();

        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->beginTransaction();
        $this->dbAdapterNewOrderApi->query('DELETE FROM T_StampFee WHERE NOT EXISTS (SELECT * FROM T_Order o WHERE o.OrderSeq=T_StampFee.OrderSeq)')->execute(null);
        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->commit();

        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->beginTransaction();
        $this->dbAdapterNewOrderApi->query('DELETE FROM T_DeliveryDestination WHERE NOT EXISTS (SELECT * FROM T_OrderItems o WHERE o.DeliDestId=T_DeliveryDestination.DeliDestId)')->execute(null);
        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->commit();

        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->beginTransaction();
        $this->dbAdapterNewOrderApi->query('DELETE FROM AT_PayingAndSales WHERE NOT EXISTS (SELECT * FROM T_PayingAndSales o WHERE o.Seq=AT_PayingAndSales.Seq)')->execute(null);
        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->commit();

        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->beginTransaction();
        $this->dbAdapterNewOrderApi->query('DELETE FROM AT_ReceiptControl WHERE NOT EXISTS (SELECT * FROM T_ReceiptControl o WHERE o.ReceiptSeq=AT_ReceiptControl.ReceiptSeq)')->execute(null);
        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->commit();

        $this->dbAdapterNewOrderApi->query("UPDATE T_SyncControl SET SyncFlg=0, UpdateDate='".date('Y-m-d H:i:s')."' WHERE TableName='RemoveNewOrderApi'")->execute(null);
    }
}

Application::getInstance()->run();
