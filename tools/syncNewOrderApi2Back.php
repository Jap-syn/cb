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

            $this->logger->info('syncNewOrderApi2Back.php start');

            $globalConfig = include __DIR__ . '/../config/autoload/global.php';
            // 接続時間を設定する
            $rds_session_timezone = $globalConfig['RDS_SESSION_TIMEZONE'];
            if (isset($rds_session_timezone)) {
                $this->dbAdapter->query('SET SESSION time_zone = :time_zone')->execute(array(':time_zone'=>$rds_session_timezone));
                $this->dbAdapterNewOrderApi->query('SET SESSION time_zone = :time_zone')->execute(array(':time_zone'=>$rds_session_timezone));
            }

            // 主処理実行
            $this->_exec();

            $this->logger->info('syncNewOrderApi2Back.php end');
            $exitCode = 0; // 正常終了

        } catch(\Exception $e) {
            $this->dbAdapterNewOrderApi->query("UPDATE T_SyncControl SET SyncFlg=0, UpdateDate='".date('Y-m-d H:i:s')."' WHERE TableName='NewOrderApi2Back'")->execute(null);
            // エラーログを出力
            if ( isset($this->logger) ) {
                $this->logger->err($e->getMessage());
            }
            $this->logger->info('syncNewOrderApi2Back.php error end');
        }

        // 終了コードを指定して処理終了
        exit($exitCode);

    }

    private function _exec() {
        $is_error = false;

        $ctl = $this->dbAdapterNewOrderApi->query("UPDATE T_SyncControl SET SyncFlg=1, UpdateDate='".date('Y-m-d H:i:s')."' WHERE SyncFlg=0 AND TableName='NewOrderApi2Back'")->execute(null);
        if ($ctl->getAffectedRows() == 0) {
            $this->logger->info('多重起動のため処理終了.');
            return;
        }

        $targets = $this->dbAdapterNewOrderApi->query("SELECT * FROM T_Order WHERE SyncFlg = 0 AND RegistDate <= '".date('Y-m-d H:i:s', strtotime("-1 minute"))."' ORDER BY OrderSeq ")->execute(null);
        if ($targets->count() == 0) {
            $this->dbAdapterNewOrderApi->query("UPDATE T_SyncControl SET SyncFlg=0, UpdateDate='".date('Y-m-d H:i:s')."' WHERE TableName='NewOrderApi2Back'")->execute(null);
            $this->logger->info('target data is not found.');
            return;
        }

        $columns = $this->dbAdapter->query('SHOW FULL COLUMNS FROM T_Order')->execute(null);

        // カラム情報取得
        $upds = array();
        $cols = array();
        foreach ($columns as $column) {
            if ($column['Key'] == 'PRI') {
                continue;
            }
            $cols[] = $column['Field'];
            $upds[] = $column['Field'].'=:'.$column['Field'];
        }

        $upd_flg_stm = $this->dbAdapterNewOrderApi->query('UPDATE T_Order SET SyncFlg = 2 WHERE OrderSeq = :OrderSeq');
        $insert_stm = $this->dbAdapter->query('INSERT INTO T_Order('.implode(', ',$cols).') VALUES (:'.implode(', :',$cols).')');
        $update_stm = $this->dbAdapter->query('UPDATE T_Order SET P_OrderSeq=:OrderSeq WHERE OrderSeq=:OrderSeq');

        foreach($targets as $target) {
            $this->dbAdapter->getDriver()->getConnection()->beginTransaction();
            $this->dbAdapterNewOrderApi->getDriver()->getConnection()->beginTransaction();

            try {
                // 注文データの同期
                $val_param = array();
                foreach ($cols as $col) {
                    $val_param[':'.$col] = $target[$col];
                }
                $ri = $insert_stm->execute($val_param);
                $orderSeq = $ri->getGeneratedValue();// 新規登録したPK値を戻す
                $update_stm->execute(array(':OrderSeq' => $orderSeq));

                // 注文商品
                $this->_exec_order_child($target['OrderSeq'], $orderSeq);

                // 顧客情報
                $this->_exec_order_customer($target['OrderSeq'], $orderSeq);

                $this->_exec_pass($target['OrderSeq'], $orderSeq);

                // 注文子データの同期
                $this->_exec_sub('T_CjMailHistory', $target['OrderSeq'], $orderSeq);
                $this->_exec_sub('T_OrderHistory', $target['OrderSeq'], $orderSeq);
                $this->_exec_sub('T_CjOrderIdControl', $target['OrderSeq'], $orderSeq);
                $this->_exec_sub('AT_Order', $target['OrderSeq'], $orderSeq);
                $this->_exec_sub('T_OrderAddInfo', $target['OrderSeq'], $orderSeq);
                $this->_exec_sub('T_OemSettlementFee', $target['OrderSeq'], $orderSeq);
                $this->_exec_sub('T_OrderNotClose', $target['OrderSeq'], $orderSeq);
                $this->_exec_sub('T_CreditTransferAlert', $target['OrderSeq'], $orderSeq);

                // 外部連携ログの同期
                $this->_exec_coop($target['OrderSeq'], $orderSeq);

                // 個別の同期
                $this->_exec_CreditOkTicket($target['OrderSeq'], $orderSeq);

                $upd_flg_stm->execute(array(':OrderSeq'=>$target['OrderSeq']));
                $this->dbAdapter->getDriver()->getConnection()->commit();
                $this->dbAdapterNewOrderApi->getDriver()->getConnection()->commit();
            } catch(\Exception $e) {
                $this->dbAdapter->getDriver()->getConnection()->rollback();
                $this->dbAdapterNewOrderApi->getDriver()->getConnection()->rollback();
                $this->logger->err('sync error for T_Order OrderSeq='.$target['OrderSeq'].' OrderId='.$target['OrderId']);
                $this->logger->err($e->getMessage());
                $this->logger->err($e->getTraceAsString());
                $is_error = true;
            }
        }

        if ($is_error) {
            throw new Exception('Sync Error Exception');
        }

        $this->dbAdapterNewOrderApi->query("UPDATE T_SyncControl SET SyncFlg=0, UpdateDate='".date('Y-m-d H:i:s')."' WHERE TableName='NewOrderApi2Back'")->execute(null);
    }

    private function _exec_order_child($api_oseq, $back_oseq) {
        $orderItemId = null;
        $targets = $this->dbAdapterNewOrderApi->query('SELECT * FROM T_OrderItems WHERE OrderSeq = '.$api_oseq)->execute(null);
        foreach($targets as $target) {
            if ($target['DeliDestId'] != -1) {
                $deli_map = array();
                $deli_targets = $this->dbAdapterNewOrderApi->query('SELECT * FROM T_DeliveryDestination WHERE DeliDestId = '.$target['DeliDestId'])->execute(null);
                $deli_cols = $this->_get_column_info('T_DeliveryDestination');
                $deli_insert_stm = $this->dbAdapter->query('INSERT INTO T_DeliveryDestination('.implode(', ',$deli_cols).') VALUES (:'.implode(', :',$deli_cols).')');
                foreach ($deli_targets as $deli_target) {
                    $val_param = $this->_get_bind($deli_cols, $deli_target, null);
                    $ri = $deli_insert_stm->execute($val_param);
                    $deli_map[$deli_target['DeliDestId']] = $ri->getGeneratedValue();// 新規登録したPK値を戻す
                }
            }
            $deli_map[-1] = -1;

            $cols = $this->_get_column_info('T_OrderItems');
            $insert_stm = $this->dbAdapter->query('INSERT INTO T_OrderItems('.implode(', ',$cols).') VALUES (:'.implode(', :',$cols).')');
            $val_param = $this->_get_bind($cols, $target, $back_oseq, null, null, $deli_map[$target['DeliDestId']]);
            $ri = $insert_stm->execute($val_param);
            if (is_null($orderItemId)) {
                $orderItemId = $ri->getGeneratedValue();// 新規登録したPK値を戻す
            }
        }
        $this->_exec_sub('T_OrderSummary', $api_oseq, $back_oseq, $orderItemId);
    }

    private function _exec_pass($api_oseq, $back_oseq) {
        $targets = $this->dbAdapterNewOrderApi->query('SELECT * FROM T_PayingAndSales WHERE OrderSeq = '.$api_oseq)->execute(null);
        foreach($targets as $target) {
            $cols = $this->_get_column_info('T_PayingAndSales');
            $insert_stm = $this->dbAdapter->query('INSERT INTO T_PayingAndSales('.implode(', ',$cols).') VALUES (:'.implode(', :',$cols).')');
            $val_param = $this->_get_bind($cols, $target, $back_oseq);
            $ri = $insert_stm->execute($val_param);
            $pass_seq = $ri->getGeneratedValue();// 新規登録したPK値を戻す

            $a_targets = $this->dbAdapterNewOrderApi->query('SELECT * FROM AT_PayingAndSales WHERE Seq = '.$target['Seq'])->execute(null);
            foreach($a_targets as $a_target) {
                $cols = $this->_get_column_info('AT_PayingAndSales');
                $cols[] = 'Seq';
                $insert_stm = $this->dbAdapter->query('INSERT INTO AT_PayingAndSales('.implode(', ',$cols).') VALUES (:'.implode(', :',$cols).')');
                $val_param = $this->_get_bind($cols, $a_target, null);
                $val_param[':Seq'] = $pass_seq;
                $insert_stm->execute($val_param);
            }
        }
    }

    private function _exec_order_customer($api_oseq, $back_oseq) {
        $targets = $this->dbAdapterNewOrderApi->query('SELECT * FROM T_Customer WHERE OrderSeq = '.$api_oseq)->execute(null);
        foreach($targets as $target) {
            $entCustSeq = $target['EntCustSeq'];
            $oldEntCustSeq = $target['EntCustSeq'];
            $e_targets = $this->dbAdapterNewOrderApi->query('SELECT * FROM T_EnterpriseCustomer WHERE EntCustSeq >= 100000000 AND EntCustSeq = '.$target['EntCustSeq'])->execute(null);
            foreach($e_targets as $e_target) {
                $manCustId = $e_target['ManCustId'];
                $m_targets = $this->dbAdapterNewOrderApi->query('SELECT * FROM T_ManagementCustomer WHERE ManCustId >= 100000000 AND ManCustId = '.$e_target['ManCustId'])->execute(null);
                foreach($m_targets as $m_target) {
                    $cols = $this->_get_column_info('T_ManagementCustomer');
                    $insert_stm = $this->dbAdapter->query('INSERT INTO T_ManagementCustomer('.implode(', ',$cols).') VALUES (:'.implode(', :',$cols).')');
                    $val_param = $this->_get_bind($cols, $m_target, null);
                    $ri = $insert_stm->execute($val_param);
                    $manCustId = $ri->getGeneratedValue();// 新規登録したPK値を戻す
                }

                $c_targets = $this->dbAdapterNewOrderApi->query('SELECT * FROM T_CombinedList WHERE ManCustId >= 100000000 AND ManCustId = '.$e_target['ManCustId'])->execute(null);
                foreach($c_targets as $c_target) {
                    $cols = $this->_get_column_info2('T_CombinedList');
//                    $cols[] = 'ManCustId';
                    $insert_stm = $this->dbAdapter->query('INSERT INTO T_CombinedList('.implode(', ',$cols).') VALUES (:'.implode(', :',$cols).')');
//var_dump('INSERT INTO T_CombinedList('.implode(', ',$cols).') VALUES (:'.implode(', :',$cols).')');
                    $val_param = $this->_get_bind($cols, $c_target, null);
                    $val_param[':ManCustId'] = $manCustId;
//var_dump($val_param);
                    $insert_stm->execute($val_param);
                }

                $cols = $this->_get_column_info('T_EnterpriseCustomer');
                $insert_stm = $this->dbAdapter->query('INSERT INTO T_EnterpriseCustomer('.implode(', ',$cols).') VALUES (:'.implode(', :',$cols).')');
                $val_param = $this->_get_bind($cols, $e_target, null);
                $val_param[':ManCustId'] = $manCustId;
                $ri = $insert_stm->execute($val_param);
                $entCustSeq = $ri->getGeneratedValue();// 新規登録したPK値を戻す

                $update_stm = $this->dbAdapterNewOrderApi->query('UPDATE T_EnterpriseCustomer SET EntCustSeq = :EntCustSeq WHERE EntCustSeq = '.$e_target['EntCustSeq']);
                $val_param = array();
                $val_param[':EntCustSeq'] = $entCustSeq;
                $update_stm->execute($val_param);
                $oldEntCustSeq = $e_target['EntCustSeq'];
            }
            $cols = $this->_get_column_info('T_Customer');
            $insert_stm = $this->dbAdapter->query('INSERT INTO T_Customer('.implode(', ',$cols).') VALUES (:'.implode(', :',$cols).')');
            $val_param = $this->_get_bind($cols, $target, $back_oseq);
            $val_param[':EntCustSeq'] = $entCustSeq;
            $insert_stm->execute($val_param);

            $update_stm = $this->dbAdapterNewOrderApi->query('UPDATE T_Customer SET EntCustSeq = :EntCustSeq WHERE EntCustSeq = '.$oldEntCustSeq);
            $val_param = array();
            $val_param[':EntCustSeq'] = $entCustSeq;
            $update_stm->execute($val_param);

            $update_stm = $this->dbAdapterNewOrderApi->query('UPDATE T_CreditTransferAlert SET EntCustSeq = :EntCustSeq WHERE EntCustSeq = '.$oldEntCustSeq);
            $val_param = array();
            $val_param[':EntCustSeq'] = $entCustSeq;
            $update_stm->execute($val_param);
        }
    }

    private function _exec_sub($table_name, $api_oseq, $back_oseq, $o_item_id = null) {
        $result = 0;
        $targets = $this->dbAdapterNewOrderApi->query('SELECT * FROM '.$table_name.' WHERE OrderSeq = '.$api_oseq)->execute(null);

        // カラム情報取得
        switch ($table_name) {
            case 'T_CjOrderIdControl':
            case 'AT_Order':
            case 'T_OrderAddInfo':
            case 'T_OrderNotClose':
                $cols = $this->_get_column_info2($table_name);
                break;
            default:
                $cols = $this->_get_column_info($table_name);
                break;
        }

        $insert_stm = $this->dbAdapter->query('INSERT INTO '.$table_name.'('.implode(', ',$cols).') VALUES (:'.implode(', :',$cols).')');
        foreach($targets as $target) {
            $val_param = $this->_get_bind($cols, $target, $back_oseq, null, null, null, $o_item_id);
//            $val_param = array();
//            foreach ($cols as $col) {
//                if ($col == 'OrderSeq') {
//                    $val_param[':'.$col] = $back_oseq;
//                } else {
//                    $val_param[':'.$col] = $target[$col];
//                }
//            }
            $ri = $insert_stm->execute($val_param);
            $result = $ri->getGeneratedValue();// 新規登録したPK値を戻す
        }
        return $result;
    }

    private function _exec_CreditOkTicket($api_oseq, $back_oseq) {
        $targets = $this->dbAdapterNewOrderApi->query('SELECT * FROM T_CreditOkTicket WHERE UseOrderSeq = '.$api_oseq)->execute(null);
        if ($targets->count() == 0) {
            return;
        }

        // カラム情報取得
        $columns = $this->dbAdapter->query('SHOW FULL COLUMNS FROM T_CreditOkTicket')->execute(null);

        $wheres = array();
        $upds = array();
        foreach ($columns as $column) {
            if ($column['Key'] == 'PRI') {
                $wheres[] = $column['Field'] . '=:' . $column['Field'];
            }
            $upds[] = $column['Field'] . '=:' . $column['Field'];
        }

        $cols = $this->_get_column_info2('T_CreditOkTicket');

        $update_stm = $this->dbAdapter->query('UPDATE T_CreditOkTicket set '.implode(', ',$upds).' WHERE '.implode(' AND ', $wheres));
        foreach($targets as $target) {
            $val_param = array();
            foreach ($cols as $col) {
                if ($col == 'UseOrderSeq') {
                    $val_param[':'.$col] = $back_oseq;
                } else {
                    $val_param[':'.$col] = $target[$col];
                }
            }
            $update_stm->execute($val_param);
        }
    }

    // 主キーがauto seqenceのパターン
    private function _get_column_info($table_name) {
        $columns = $this->dbAdapter->query('SHOW FULL COLUMNS FROM '.$table_name)->execute(null);

//        $upds = array();
        $cols = array();
        foreach ($columns as $column) {
            if ($column['Key'] == 'PRI') {
                continue;
            }
            $cols[] = $column['Field'];
//            $upds[] = $column['Field'].'=:'.$column['Field'];
        }

        return $cols;
    }

    // 主キーがOrderSeqのパターン
    private function _get_column_info2($table_name) {
        $columns = $this->dbAdapter->query('SHOW FULL COLUMNS FROM '.$table_name)->execute(null);

//        $upds = array();
        $cols = array();
        foreach ($columns as $column) {
            $cols[] = $column['Field'];
//            $upds[] = $column['Field'].'=:'.$column['Field'];
        }

        return $cols;
    }

    private function _exec_coop($api_oseq, $back_oseq) {
        $cjr_seq = $this->_exec_sub('T_CjResult', $api_oseq, $back_oseq);
        $jtc_seq = $this->_exec_sub('T_JtcResult', $api_oseq, $back_oseq);

        $this->_exec_coop_sub('T_CjResult_Detail', $api_oseq, $back_oseq, $cjr_seq, $jtc_seq);
        $this->_exec_coop_sub('T_CjResult_Error', $api_oseq, $back_oseq, $cjr_seq, $jtc_seq);
        $this->_exec_coop_sub('T_JtcResult_Detail', $api_oseq, $back_oseq, $cjr_seq, $jtc_seq);
        $this->_exec_coop_sub('T_CreditLog', $api_oseq, $back_oseq, $cjr_seq, $jtc_seq);
    }

    private function _exec_coop_sub($table_name, $api_oseq, $back_oseq, $cjr_seq, $jtc_seq) {
        $targets = $this->dbAdapterNewOrderApi->query('SELECT * FROM '.$table_name.' WHERE OrderSeq = '.$api_oseq)->execute(null);
        $cols = $this->_get_column_info($table_name);
        $insert_stm = $this->dbAdapter->query('INSERT INTO '.$table_name.'('.implode(', ',$cols).') VALUES (:'.implode(', :',$cols).')');
        foreach($targets as $target) {
            $val_param = $this->_get_bind($cols, $target, $back_oseq, $cjr_seq, $jtc_seq);
            $ri = $insert_stm->execute($val_param);
            $ri->getGeneratedValue();// 新規登録したPK値を戻す
        }
    }

    private function _get_bind($cols, $data, $back_oseq, $cjr_seq=null, $jtc_seq=null, $deliDestId=null, $o_item_id=null){
        $result = array();
        foreach ($cols as $col) {
            if ($col == 'OrderSeq') {
                $result[':'.$col] = $back_oseq;
            } else if ($col == 'CjrSeq') {
                $result[':'.$col] = $cjr_seq;
            } else if ($col == 'JtcSeq') {
                $result[':'.$col] = $jtc_seq;
            } else if ($col == 'DeliDestId') {
                $result[':'.$col] = $deliDestId;
            } else if ($col == 'OrderItemId') {
                $result[':'.$col] = $o_item_id;
            } else {
                $result[':'.$col] = $data[$col];
            }
        }
        return $result;
    }
}

Application::getInstance()->run();
