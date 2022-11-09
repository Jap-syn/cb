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

            $this->logger->info('syncNewOrderApi.php start');

            $globalConfig = include __DIR__ . '/../config/autoload/global.php';
            // 接続時間を設定する
            $rds_session_timezone = $globalConfig['RDS_SESSION_TIMEZONE'];
            if (isset($rds_session_timezone)) {
                $this->dbAdapter->query('SET SESSION time_zone = :time_zone')->execute(array(':time_zone'=>$rds_session_timezone));
                $this->dbAdapterNewOrderApi->query('SET SESSION time_zone = :time_zone')->execute(array(':time_zone'=>$rds_session_timezone));
            }

            // プログラム引数の件数チェック
            if ($_SERVER['argc'] != 2) {
                $this->logger->warn('It does not match the number of arguments. argc=' . $_SERVER['argc']);
                exit(0);
            }

            // 主処理実行
            $this->logger->info('sync '.$_SERVER['argv'][1].' start');
            $this->_exec($_SERVER['argv'][1]);

            $this->logger->info('syncNewOrderApi.php end');
            $exitCode = 0; // 正常終了

        } catch(\Exception $e) {
            $this->dbAdapterNewOrderApi->getDriver()->getConnection()->beginTransaction();
            $this->dbAdapterNewOrderApi->query("UPDATE T_SyncControl SET SyncFlg=0, UpdateDate='".date('Y-m-d H:i:s')."' WHERE TableName='".$_SERVER['argv'][1]."'")->execute(null);
            $this->dbAdapterNewOrderApi->getDriver()->getConnection()->commit();
            // エラーログを出力
            if ( isset($this->logger) ) {
                $this->logger->err($e->getMessage());
            }
            $this->logger->info('syncNewOrderApi.php error end');
        }

        // 終了コードを指定して処理終了
        exit($exitCode);

    }

    private function _exec($table_name) {
        $is_error = false;
        $is_order = false;

        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->beginTransaction();
        $ctl = $this->dbAdapterNewOrderApi->query("UPDATE T_SyncControl SET SyncFlg=1, UpdateDate='".date('Y-m-d H:i:s')."' WHERE SyncFlg=0 AND TableName='".$table_name."'")->execute(null);
        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->commit();
        if ($ctl->getAffectedRows() == 0) {
            $this->logger->info('多重起動のため処理終了.');
            return;
        }

        $que_table_name = $table_name.'_Que';
//        $this->logger->debug('SELECT * FROM '.$que_table_name.' WHERE ValidFlg = 1 ORDER BY QueSeq');
        $targets = $this->dbAdapter->query('SELECT * FROM '.$que_table_name.' WHERE ValidFlg = 1 ORDER BY QueSeq ')->execute(null);
        if ($targets->count() == 0) {
            $this->dbAdapterNewOrderApi->query("UPDATE T_SyncControl SET SyncFlg=0, UpdateDate='".date('Y-m-d H:i:s')."' WHERE TableName='".$table_name."'")->execute(null);
            $this->logger->info('target data is not found.');
            return;
        }

        if ($table_name == 'T_Order') {
            $is_order = true;
        }

        $columns = $this->dbAdapter->query('SHOW FULL COLUMNS FROM '.$table_name)->execute(null);

        // P-Key＆カラム情報取得
        $pkeys = array();
        $wheres = array();
        $upds = array();
        $cols = array();
        foreach ($columns as $column) {
            if ($column['Key'] == 'PRI') {
//                if ($is_order) {
//                    $pkeys[] = 'OrderId';
//                    $wheres[] = 'OrderId=:OrderId';
//                } else {
                    $pkeys[] = $column['Field'];
                    $wheres[] = $column['Field'].'=:'.$column['Field'];
//                }
            }
            $cols[] = $column['Field'];
//            if (($is_order) && ($column['Field'] == 'OrderSeq')) {
//                ;
//            } else {
                $upds[] = $column['Field'].'=:'.$column['Field'];
//            }
        }
        if ($is_order) {
            $cols[] = 'SyncFlg';
            $upds[] = 'SyncFlg=:SyncFlg';
        }

        $upd_valid_flg_stm = $this->dbAdapter->query('UPDATE '.$que_table_name.' SET ValidFlg = 0 WHERE QueSeq = :QueSeq');
        $insert_stm = $this->dbAdapterNewOrderApi->query('INSERT INTO '.$table_name.'('.implode(', ',$cols).') VALUES (:'.implode(', :',$cols).')');

        if ($table_name == 'M_CreditSystemInfo') {
            $get_stm = $this->dbAdapter->query('SELECT * FROM '.$table_name);
            $found_valid_flg_stm = $this->dbAdapterNewOrderApi->query('SELECT count(*) cnt FROM '.$table_name);
            $update_stm = $this->dbAdapterNewOrderApi->query('UPDATE '.$table_name.' set '.implode(', ',$upds));
            $delete_stm = $this->dbAdapterNewOrderApi->query('DELETE FROM '.$table_name);
        } else {
            $get_stm = $this->dbAdapter->query('SELECT * FROM '.$table_name.' WHERE '.implode(' AND ', $wheres));
            $found_valid_flg_stm = $this->dbAdapterNewOrderApi->query('SELECT count(*) cnt FROM '.$table_name.' WHERE '.implode(' AND ', $wheres));
            $update_stm = $this->dbAdapterNewOrderApi->query('UPDATE '.$table_name.' set '.implode(', ',$upds).' WHERE '.implode(' AND ', $wheres));
            $delete_stm = $this->dbAdapterNewOrderApi->query('DELETE FROM '.$table_name.' WHERE '.implode(' AND ', $wheres));
        }

//        $this->logger->debug('UPDATE '.$que_table_name.' SET ValidFlg = 0 WHERE QueSeq = :QueSeq');
//        $this->logger->debug('SELECT * FROM '.$table_name.' WHERE '.implode(' AND ', $wheres));
//        $this->logger->debug('SELECT count(*) cnt FROM '.$table_name.' WHERE '.implode(' AND ', $wheres));
//        $this->logger->debug('DELETE FROM '.$table_name.' WHERE '.implode(' AND ', $wheres));
//        $this->logger->debug('INSERT INTO '.$table_name.'('.implode(', ',$cols).') VALUES (:'.implode(', :',$cols).')');
//        $this->logger->debug('UPDATE '.$table_name.' set '.implode(', ',$upds).' WHERE '.implode(' AND ', $wheres));

        foreach($targets as $target) {
            $this->dbAdapter->getDriver()->getConnection()->beginTransaction();
            $this->dbAdapterNewOrderApi->getDriver()->getConnection()->beginTransaction();

            $key_param = array();
            foreach ($pkeys as $pkey) {
                $key_param[':'.$pkey] = $target[$pkey];
            }

            try {
                if ($target['AccessType'] == 'D') {
                    $delete_stm->execute($key_param);
                } else {
                    $res = $get_stm->execute($key_param);
                    if ($res->count() != 0) {
                        $back = $res->current();

                        $val_param = array();
                        foreach ($cols as $col) {
                            if (($is_order) && ($col == 'SyncFlg')) {
                                $val_param[':SyncFlg'] = 1;
                            } else {
                                $val_param[':'.$col] = $back[$col];
                            }
                        }

                        $cnt = $found_valid_flg_stm->execute($key_param)->current()['cnt'];
                        if ($cnt == 0) {
                            // insert
                            $insert_stm->execute($val_param);
                        } else {
                            // update
                            $update_stm->execute($val_param);
                        }
                    }
                }

                $upd_valid_flg_stm->execute(array(':QueSeq'=>$target['QueSeq']));
                $this->dbAdapter->getDriver()->getConnection()->commit();
                $this->dbAdapterNewOrderApi->getDriver()->getConnection()->commit();
                $this->logger->debug('sync data. p-key info:'.implode(', ', $key_param));
            } catch(\Exception $e) {
                $this->dbAdapter->getDriver()->getConnection()->rollback();
                $this->dbAdapterNewOrderApi->getDriver()->getConnection()->rollback();
                $this->logger->err('sync error for '.$que_table_name.' QueSeq='.$target['QueSeq']);
                $this->logger->err($e->getMessage());
                $this->logger->err($e->getTraceAsString());
                $is_error = true;
            }
        }

        if ($is_error) {
            throw new Exception('Sync Error Exception');
        }

        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->beginTransaction();
        $this->dbAdapterNewOrderApi->query("UPDATE T_SyncControl SET SyncFlg=0, UpdateDate='".date('Y-m-d H:i:s')."' WHERE TableName='".$table_name."'")->execute(null);
        $this->dbAdapterNewOrderApi->getDriver()->getConnection()->commit();
    }
}

Application::getInstance()->run();
