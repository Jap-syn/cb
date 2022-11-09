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

            $this->logger->info('removeNewOrderApiRegistData.php start');

            $globalConfig = include __DIR__ . '/../config/autoload/global.php';
            // 接続時間を設定する
            $rds_session_timezone = $globalConfig['RDS_SESSION_TIMEZONE'];
            if (isset($rds_session_timezone)) {
                $this->dbAdapter->query('SET SESSION time_zone = :time_zone')->execute(array(':time_zone'=>$rds_session_timezone));
                $this->dbAdapterNewOrderApi->query('SET SESSION time_zone = :time_zone')->execute(array(':time_zone'=>$rds_session_timezone));
            }

            // 主処理実行
            $this->_exec();

            $this->logger->info('removeNewOrderApiRegistData.php end');
            $exitCode = 0; // 正常終了

        } catch(\Exception $e) {
            $this->dbAdapterNewOrderApi->query("UPDATE T_SyncControl SET SyncFlg=0, UpdateDate='".date('Y-m-d H:i:s')."' WHERE TableName='RemoveNewOrderApi'")->execute(null);
            // エラーログを出力
            if ( isset($this->logger) ) {
                $this->logger->err($e->getMessage());
            }
            $this->logger->info('removeNewOrderApiRegistData.php error end');
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

        $targets = $this->dbAdapterNewOrderApi->query("SELECT * FROM T_Order WHERE SyncFlg = 2 AND RegistDate <= '".date('Y-m-d H:i:s', strtotime("-10 minute"))."' ORDER BY OrderSeq")->execute(null);
        if ($targets->count() == 0) {
            $this->dbAdapterNewOrderApi->query("UPDATE T_SyncControl SET SyncFlg=0, UpdateDate='".date('Y-m-d H:i:s')."' WHERE TableName='RemoveNewOrderApi'")->execute(null);
            $this->logger->info('target data is not found.');
            return;
        }

        $delete_stm = $this->dbAdapterNewOrderApi->query('DELETE FROM T_Order WHERE OrderSeq = :OrderSeq');

        foreach($targets as $target) {
            $this->logger->info('remove start. oseq:'.$target['OrderSeq']);

            $this->dbAdapterNewOrderApi->getDriver()->getConnection()->beginTransaction();

            try {
                // 注文データの削除
                $delete_stm->execute(array(':OrderSeq' => $target['OrderSeq']));

                // 注文商品
                $this->_exec_order_child($target['OrderSeq']);

                // 顧客情報
                $this->_exec_order_customer($target['OrderSeq']);

                $this->_exec_pass($target['OrderSeq']);

                // 注文子データの同期
                $this->_remove_orderseq('T_CjMailHistory', $target['OrderSeq']);
                $this->_remove_orderseq('T_OrderHistory', $target['OrderSeq']);
                $this->_remove_orderseq('T_CjOrderIdControl', $target['OrderSeq']);
                $this->_remove_orderseq('AT_Order', $target['OrderSeq']);
                $this->_remove_orderseq('T_OrderAddInfo', $target['OrderSeq']);
                $this->_remove_orderseq('T_OemSettlementFee', $target['OrderSeq']);
                $this->_remove_orderseq('T_OrderNotClose', $target['OrderSeq']);
                $this->_remove_orderseq('T_CreditTransferAlert', $target['OrderSeq']);

                // 外部連携ログの同期
                $this->_exec_coop($target['OrderSeq']);

                // 個別の同期
                $this->_exec_CreditOkTicket($target['OrderSeq']);

                $this->dbAdapterNewOrderApi->getDriver()->getConnection()->commit();
            } catch(\Exception $e) {
                $this->dbAdapterNewOrderApi->getDriver()->getConnection()->rollback();
                $this->logger->err('remove error for T_Order OrderSeq='.$target['OrderSeq'].' OrderId='.$target['OrderId']);
                $this->logger->err($e->getMessage());
                $this->logger->err($e->getTraceAsString());
                $is_error = true;
            }
        }

        if ($is_error) {
            throw new Exception('Remove Error Exception');
        }

        $this->dbAdapterNewOrderApi->query("UPDATE T_SyncControl SET SyncFlg=0, UpdateDate='".date('Y-m-d H:i:s')."' WHERE TableName='RemoveNewOrderApi'")->execute(null);
    }

    private function _exec_order_child($api_oseq) {
        $orderItemId = null;
        $targets = $this->dbAdapterNewOrderApi->query('SELECT * FROM T_OrderItems WHERE OrderSeq = '.$api_oseq)->execute(null);
        foreach($targets as $target) {
            if ($target['DeliDestId'] != -1) {
                $this->dbAdapterNewOrderApi->query('DELETE FROM T_DeliveryDestination WHERE DeliDestId = '.$target['DeliDestId'])->execute(null);
            }
        }
        $this->_remove_orderseq('T_OrderItems', $api_oseq);
        $this->_remove_orderseq('T_OrderSummary', $api_oseq);
    }

    private function _remove_orderseq($table_name, $api_oseq) {
        $this->dbAdapterNewOrderApi->query('DELETE FROM '.$table_name.' WHERE OrderSeq = '.$api_oseq)->execute(null);
    }

    private function _exec_pass($api_oseq) {
        $targets = $this->dbAdapterNewOrderApi->query('SELECT * FROM T_PayingAndSales WHERE OrderSeq = '.$api_oseq)->execute(null);
        foreach($targets as $target) {
            $this->dbAdapterNewOrderApi->query('DELETE FROM AT_PayingAndSales WHERE Seq = '.$target['Seq'])->execute(null);
        }
        $this->dbAdapterNewOrderApi->query('DELETE FROM T_PayingAndSales WHERE OrderSeq = '.$api_oseq)->execute(null);
    }

    private function _exec_order_customer($api_oseq) {
        $targets = $this->dbAdapterNewOrderApi->query('SELECT * FROM T_Customer WHERE OrderSeq = '.$api_oseq)->execute(null);
        foreach($targets as $target) {
            $e_targets = $this->dbAdapterNewOrderApi->query('SELECT * FROM T_EnterpriseCustomer WHERE EntCustSeq = '.$target['EntCustSeq'])->execute(null);
            foreach($e_targets as $e_target) {
                $this->dbAdapterNewOrderApi->query('DELETE FROM T_ManagementCustomer WHERE ManCustId >= 1000000000 AND ManCustId = '.$e_target['ManCustId'])->execute(null);
                $this->dbAdapterNewOrderApi->query('DELETE FROM T_CombinedList WHERE ManCustId >= 1000000000 AND ManCustId = '.$e_target['ManCustId'])->execute(null);
            }
            $this->dbAdapterNewOrderApi->query('DELETE FROM T_EnterpriseCustomer WHERE EntCustSeq >= 1000000000 AND EntCustSeq = '.$target['EntCustSeq'])->execute(null);
        }
        $this->dbAdapterNewOrderApi->query('DELETE FROM T_Customer WHERE OrderSeq = '.$api_oseq)->execute(null);
    }

    private function _exec_CreditOkTicket($api_oseq) {
        $this->dbAdapterNewOrderApi->query('DELETE FROM T_CreditOkTicket WHERE UseOrderSeq = '.$api_oseq)->execute(null);
    }

    private function _exec_coop($api_oseq) {
        $cjr_seq = $this->_remove_orderseq('T_CjResult', $api_oseq);
        $jtc_seq = $this->_remove_orderseq('T_JtcResult', $api_oseq);

        $this->_remove_orderseq('T_CjResult_Detail', $api_oseq);
        $this->_remove_orderseq('T_CjResult_Error', $api_oseq);
        $this->_remove_orderseq('T_JtcResult_Detail', $api_oseq);
        $this->_remove_orderseq('T_CreditLog', $api_oseq);
    }
}

Application::getInstance()->run();
