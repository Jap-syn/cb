<?php
chdir(dirname(__DIR__));

// Setup autoloading
require 'init_autoloader.php';

use Coral\Base\Application\BaseApplicationAbstract;
use Zend\Db\Adapter\Adapter;
use Zend\Config\Reader\Ini;
use models\Logic\LogicTreasurer;
use Coral\Base\BaseLog;
use models\Table\TableSystemProperty;
use models\Table\TableBusinessCalendar;

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

            // ログ設定の読み込み
            $logConfig = $data['log'];
            // 標準ログクラス初期化
            $this->logger = BaseLog::createFromArray( $logConfig );

            $this->logger->info('treas_account_data.php start');

            $globalConfig = include __DIR__ . '/../config/autoload/global.php';
            // 接続時間を設定する
            $rds_session_timezone = $globalConfig['RDS_SESSION_TIMEZONE'];
            if (isset($rds_session_timezone)) {
                $this->dbAdapter->query('SET SESSION time_zone = :time_zone')->execute(array(':time_zone'=>$rds_session_timezone));
            }

            // 本処理
            $this->_exec();

            $this->logger->info('treas_account_data.php end');
            $exitCode = 0; // 正常終了

        } catch(\Exception $e) {
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
     * 本処理
     */
    private function _exec() {
        $this->logger->info('treas_account_data.php [_exec] start');
        // -------------------------
        // 業務日付を取得する。
        // -------------------------
        $sys = new TableSystemProperty($this->dbAdapter);
        $module = '[DEFAULT]';
        $category = 'systeminfo';
        $name = 'BusinessDate';
        $businessDate = $sys->getValue($module, $category, $name);

        // -------------------------
        // 会計締め日を取得する。
        // -------------------------
        // 会計締め日（会計締めをする営業日）を取得
        $name = 'AccountingDay';
        $accountingDay = $sys->getValue($module, $category, $name);

        // 上で取得した営業日から日にちを取得
        $mdlbc = new TableBusinessCalendar($this->dbAdapter);
        // 今月の営業カレンダーを取得
        $ri = $mdlbc->getMonthCalendar(date('Y'), date('m'));
        // 営業カレンダーをループして会計締めの営業日を取得
        $i = 0;
        foreach ($ri as $key => $value) {
            // 営業日の場合
            if ($value['BusinessFlg'] == 1) {
                $i++;
                if ($i == $accountingDay) {
                    $accountingDate = $value['BusinessDate'];
                    break;
                }
            }
        }

        // -------------------------
        // 業務日付の月末日を取得する。
        // -------------------------
        $lastDate = date("Y-m-t", strtotime($businessDate));

        // -------------------------
        // 処理開始
        // -------------------------
        $logic = new LogicTreasurer($this->dbAdapter);

        // 業務日付が月末日の場合
        if ($businessDate == $lastDate) {
            // 加盟店月締め処理
            $this->logger->info('treas_enterprisemonthlyclosinginfo.php start');
            $logic->treas_enterprise_monthlyclosinginfo();
            $this->logger->info('treas_enterprisemonthlyclosinginfo.php end');
        }

        // 日次処理
        // 会計日次用ワーク入金データ作成
        $this->logger->info('treas_make_wk_receipt_day.php start');
        $logic->treas_make_wk_receipt_day();
        $this->logger->info('treas_make_wk_receipt_day.php end');

        // 調整金一覧
        $this->logger->info('treas_adjustgoldlist.php start');
        $logic->treas_adjustgoldlist();
        $this->logger->info('treas_adjustgoldlist.php end');

        // 貸倒債権一覧
        $this->logger->info('treas_baddebt_list.php start');
        $logic->treas_baddebt_list();
        $this->logger->info('treas_baddebt_list.php end');

        // OEM移管明細
        $this->logger->info('treas_oem_transferspecification.php start');
        $logic->treas_oem_transferspecification();
        $this->logger->info('treas_oem_transferspecification.php end');

        // 再発行手数料明細
        $this->logger->info('treas_reissuefeespecificationt.php start');
        $logic->treas_reissuefeespecificationt();
        $this->logger->info('treas_reissuefeespecificationt.php end');

        // 過剰金一覧（日次）
        $this->logger->info('treas_excess_list_day.php start');
        $logic->treas_excess_list_day();
        $this->logger->info('treas_excess_list_day.php end');

        // 精算日計
        $this->logger->info('treas_payoff_dailyaccount.php start');
        $logic->treas_payoff_dailyaccount();
        $this->logger->info('treas_payoff_dailyaccount.php end');

        // 仮払日計
        $this->logger->info('treas_suspensepayments_dailyaccount.php start');
        $logic->treas_suspensepayments_dailyaccount();
        $this->logger->info('treas_suspensepayments_dailyaccount.php end');

        // 未収金日計
        $this->logger->info('treas_accountsdue_dailyaccount.php start');
        $logic->treas_accountsdue_dailyaccount();
        $this->logger->info('treas_accountsdue_dailyaccount.php end');

        // 収納代行委託先未収金明細
        $this->logger->info('treas_receiptagen_accountsdue.php start');
        $logic->treas_receiptagen_accountsdue();
        $this->logger->info('treas_receiptagen_accountsdue.php end');

        // 日次売上明細
        $this->logger->info('treas_daily_salesdetails.php start');
        $logic->treas_daily_salesdetails();
        $this->logger->info('treas_daily_salesdetails.php end');

        // 入金先トータル表
        $this->logger->info('treas_paymentplacetotal.php start');
        $logic->treas_paymentplacetotal();
        $this->logger->info('treas_paymentplacetotal.php end');

        // 直営日次統計表（日次）
        $this->logger->info('treas_dailystatisticstable_day.php start');
        $logic->treas_dailystatisticstable_day();
        $this->logger->info('treas_dailystatisticstable_day.php end');

        // OEM日次統計表（日次）
        $this->logger->info('treas_oem_dailystatisticstable_day.php start');
        $logic->treas_oem_dailystatisticstable_day();
        $this->logger->info('treas_oem_dailystatisticstable_day.php end');

        // 日次用ファイル作成処理
        $this->logger->info('account_report_day.php start');
        $logic->account_report_day();
        $this->logger->info('account_report_day.php end');

        // 業務日付が会計締め日の場合
        if ($businessDate == $accountingDate) {
            // 月次処理
            // 会計月次用ワーク請求データ作成
            // 2016/02/08 Y.Suzuki Mod AW_Receiptが作成されないようなので、プロシージャの直接コールをロジックのコールへ変更。 Stt
//             $this->dbAdapter->query(' CALL P_Treas_Make_Wk_Receipt() ');
            $this->logger->info('treas_make_wk_receipt.php start');
            $logic->treas_make_wk_receipt();
            $this->logger->info('treas_make_wk_receipt.php end');
            // 2016/02/08 Y.Suzuki Mod AW_Receiptが作成されないようなので、プロシージャの直接コールをロジックのコールへ変更。 End

            // 消費者未収金明細
            $this->logger->info('treas_consumer_accountsdue.php start');
            $logic->treas_consumer_accountsdue();
            $this->logger->info('treas_consumer_accountsdue.php end');

            // OEM仮払金明細
            $this->logger->info('treas_oem_suspensepayments.php start');
            $logic->treas_oem_suspensepayments();
            $this->logger->info('treas_oem_suspensepayments.php end');

            // 直営未払金兼売掛金明細
            $this->logger->info('treas_accounts_payablereceivable_month.php start');
            $logic->treas_accounts_payablereceivable_month();
            $this->logger->info('treas_accounts_payablereceivable_month.php end');

            // OEM未払金兼売掛金明細
            $this->logger->info('treas_oem_accounts_payablereceivable.php start');
            $logic->treas_oem_accounts_payablereceivable();
            $this->logger->info('treas_oem_accounts_payablereceivable.php end');

            // 過剰金一覧（月次）
            $this->logger->info('treas_excess_list_month.php start');
            $logic->treas_excess_list_month();
            $this->logger->info('treas_excess_list_month.php end');

            // 直営日次統計表（月次）
            $this->logger->info('treas_dailystatisticstable_month.php start');
            $logic->treas_dailystatisticstable_month();
            $this->logger->info('treas_dailystatisticstable_month.php end');

            // OEM日次統計表（月次）
            $this->logger->info('treas_oem_dailystatisticstable_month.php start');
            $logic->treas_oem_dailystatisticstable_month();
            $this->logger->info('treas_oem_dailystatisticstable_month.php end');

            // 直営未払金・売掛金・加盟店未収金明細 (SP呼出し廃止⇒executemonthlyAction内でSQL実行 20171016)
            // $this->logger->info('treas_accounts_payablestatisticstable.php start');
            // $logic->treas_accounts_payablestatisticstable();
            // $this->logger->info('treas_accounts_payablestatisticstable.php end');

            // OEM未払金・売掛金・OEM未収金明細 (SP呼出し廃止⇒executemonthlyAction内でSQL実行 20171016)
            // $this->logger->info('treas_oemaccounts_payablestatisticstable.php start');
            // $logic->treas_oemaccounts_payablestatisticstable();
            // $this->logger->info('treas_oemaccounts_payablestatisticstable.php end');

            // 月次用ファイル作成処理
            $this->logger->info('account_report_month.php start');
            $logic->account_report_month();
            $this->logger->info('account_report_month.php end');

            // 会計月変更処理
            $this->logger->info('treas_updateaccountingmonth.php start');
            $logic->treas_updateaccountingmonth();
            $this->logger->info('treas_updateaccountingmonth.php end');

        }

        // 会計後処理
        $this->logger->info('treas_postprocessing.php start');
        $logic->treas_postprocessing();
        $this->logger->info('treas_postprocessing.php end');

        $this->logger->info('treas_account_data.php [_exec] end');
        return;
    }
}

Application::getInstance()->run();