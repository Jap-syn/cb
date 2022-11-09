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
use Zend\Db\Adapter\Adapter;
use Zend\Config\Reader\Ini;
use Coral\Base\BaseLog;
use Coral\Base\BaseGeneralUtils;
use models\Table\TableClaimHistory;
use models\Table\ATableReceiptControl;
use models\Table\TablePayingAndSales;
use models\Table\TableOrder;
use Coral\Coral\History\CoralHistoryOrder;

/**
 * アプリケーションクラスです。
 * 入金取消処理を行うパッチです。
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
     * Logger
     * @var unknown
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

$this->logger->info('_data_patch_20151225_2100.php start');

            $globalConfig = include __DIR__ . '/../config/autoload/global.php';
            // 接続時間を設定する
            $rds_session_timezone = $globalConfig['RDS_SESSION_TIMEZONE'];
            if (isset($rds_session_timezone)) {
                $this->dbAdapter->query('SET SESSION time_zone = :time_zone')->execute(array(':time_zone'=>$rds_session_timezone));
            }

$this->dbAdapter->getDriver()->getConnection()->beginTransaction();

            // 本処理
            $this->_exec();

$this->dbAdapter->getDriver()->getConnection()->commit();
// $this->dbAdapter->getDriver()->getConnection()->rollback();

$this->logger->info('_data_patch_20151225_2100.php end');
            $exitCode = 0; // 正常終了
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

    /**
     * 本処理
     */
    public function _exec()
    {

        try {
//             // （直営日次統計表）対象を抽出
//             $sql = "";
//             $sql .= " SELECT  o.EnterpriseId ";
//             $sql .= "     ,   SUM(cc.ClaimedBalance) AS ClaimedBalance ";
//             $sql .= " FROM    T_Order o ";
//             $sql .= "         INNER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.OrderSeq) ";
//             $sql .= "         INNER JOIN M_Code c ON (c.CodeId = 160 AND c.KeyCode = IFNULL(o.OemId, 0)) ";
//             $sql .= " WHERE   o.DataStatus IN (51, 61) ";
//             $sql .= " AND     o.Cnl_Status = 0 ";
//             $sql .= " AND     (o.Deli_ConfirmArrivalDate IS NOT NULL and o.Deli_ConfirmArrivalFlg = 1) ";
//             $sql .= " AND     c.Class1 = 0 ";
//             $sql .= " GROUP BY ";
//             $sql .= "         o.EnterpriseId ";

//             $ri = $this->dbAdapter->query($sql)->execute(null);
//             $cbdata = ResultInterfaceToArray($ri);

            $cbdata = array(
                    '1675' => 0,
                    '1949' => 0,
                    '2231' => 0,
                    '2253' => 0,
                    '2357' => 0,
                    '2392' => 0,
                    '2438' => 0,
                    '2524' => 0,
                    '2763' => 0,
                    '2846' => 0,
                    '2851' => 0,
                    '3002' => 0,
                    '3118' => 0,
                    '3131' => 0,
                    '3182' => 0,
                    '3230' => 0,
                    '3240' => 0,
                    '3304' => 0,
                    '3381' => 0,
                    '3528' => 0,
                    '3632' => 0,
                    '3700' => 0,
                    '3745' => 0,
                    '3756' => 0,
                    '3796' => 0,
                    '3831' => 0,
                    '3938' => 0,
                    '3947' => 0,
                    '4062' => 0,
                    '4096' => 0,
                    '4097' => 0,
                    '4121' => 0,
                    '4183' => 0,
                    '4345' => 0,
                    '4378' => 0,
                    '5227' => 0,
                    '6403' => 0,
                    '6488' => 0,
                    '6739' => 0,
                    '6893' => 0,
                    '7101' => 0,
                    '7122' => 0,
                    '7212' => 0,
                    '7440' => 0,
                    '7573' => 0,
                    '7660' => 0,
                    '7767' => 0,
                    '7789' => 0,
                    '7825' => 0,
                    '8047' => 0,
                    '8122' => 0,
                    '8280' => 0,
                    '8566' => 0,
                    '8672' => 0,
            );


            // 取得できたデータ数分ループする
            foreach ($cbdata as $key => $value) {

                $claimedBalance = $value;
                $enterpriseId = $key;

                // 直営日次統計表の当日時点未収金残高を更新
                $sql  = "";
                $sql .= " UPDATE  AT_DailyStatisticsTable ";
                $sql .= " SET     D_AccountsReceivableBalance = :D_AccountsReceivableBalance ";
                $sql .= " WHERE   DailyMonthlyFlg = 0 ";
                $sql .= " AND     ProcessingDate = '2015-12-24' ";
                $sql .= " AND     EnterpriseId = :EnterpriseId ";

                $this->dbAdapter->query($sql)->execute(array(':D_AccountsReceivableBalance' => $claimedBalance, ':EnterpriseId' => $enterpriseId));


                // 直営日次統計表の前日時点未収金残高を更新
                $sql  = "";
                $sql .= " UPDATE  AT_DailyStatisticsTable ";
                $sql .= " SET     DB__AccountsReceivableBalance = D_AccountsReceivableBalance - D_ChargeAmount - D_CancelAmount - ";
                $sql .= "                                         D_SettlementBackAmount - D_OemTransferAmount - D_ReClaimFeeAmount - D_DamageAmount - ";
                $sql .= "                                         D_ReceiptAmount - D_RepayAmount - D_BadDebtAmount - D_OtherPaymentAmount ";
                $sql .= " WHERE   DailyMonthlyFlg = 0 ";
                $sql .= " AND     ProcessingDate = '2015-12-24' ";
                $sql .= " AND     EnterpriseId = :EnterpriseId ";

                $this->dbAdapter->query($sql)->execute(array(':EnterpriseId' => $enterpriseId));
            }


//             // （OEM日次統計表）対象を抽出
//             $sql = "";
//             $sql .= " SELECT  o.EnterpriseId ";
//             $sql .= "     ,   SUM(cc.ClaimedBalance) AS ClaimedBalance ";
//             $sql .= " FROM    T_Order o ";
//             $sql .= "         INNER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.OrderSeq) ";
//             $sql .= "         INNER JOIN M_Code c ON (c.CodeId = 160 AND c.KeyCode = IFNULL(o.OemId, 0)) ";
//             $sql .= " WHERE   o.DataStatus IN (51, 61) ";
//             $sql .= " AND     o.Cnl_Status = 0 ";
//             $sql .= " AND     (o.Deli_ConfirmArrivalDate IS NOT NULL and o.Deli_ConfirmArrivalFlg = 1) ";
//             $sql .= " AND     c.Class1 <> 0 ";
//             $sql .= " GROUP BY ";
//             $sql .= "         o.EnterpriseId ";

//             $ri = $this->dbAdapter->query($sql)->execute(null);
//             $oemdata = ResultInterfaceToArray($ri);

            $oemdata = array(
                    '4392' => 0,
                    '4406' => 0,
                    '4425' => 0,
                    '4426' => 0,
                    '4525' => 0,
                    '4534' => 0,
                    '4613' => 0,
                    '4754' => 0,
                    '4848' => 0,
                    '5355' => 0,
                    '5542' => 0,
                    '5576' => 0,
                    '6345' => 0,
                    '6440' => 0,
                    '6500' => 0,
                    '6718' => 0,
                    '6768' => 0,
                    '6792' => 0,
                    '7075' => 0,
                    '7222' => 0,
                    '7296' => 0,
                    '7309' => 0,
                    '7334' => 0,
                    '7698' => 0,
                    '7721' => 0,
                    '7915' => 0,
                    '8019' => 0,
                    '8404' => 0,
                    '8475' => 0,
                    '8592' => 0,
                    '8893' => 0,
                    '8580' => 0,
                    '9575' => 0,
            );

            // 取得できたデータ数分ループする
            foreach ($oemdata as $key => $value) {

                $claimedBalance = $value;
                $enterpriseId = $key;

                // OEM日次統計表の当日時点未収金残高を更新
                $sql  = "";
                $sql .= " UPDATE  AT_Oem_DailyStatisticsTable ";
                $sql .= " SET     D_AccountsReceivableBalance = :D_AccountsReceivableBalance ";
                $sql .= " WHERE   DailyMonthlyFlg = 0 ";
                $sql .= " AND     ProcessingDate = '2015-12-24' ";
                $sql .= " AND     EnterpriseId = :EnterpriseId ";

                $this->dbAdapter->query($sql)->execute(array(':D_AccountsReceivableBalance' => $claimedBalance, ':EnterpriseId' => $enterpriseId));


                // OEM日次統計表の前日時点未収金残高を更新
                $sql  = "";
                $sql .= " UPDATE  AT_Oem_DailyStatisticsTable ";
                $sql .= " SET     DB__AccountsReceivableBalance = D_AccountsReceivableBalance - D_ChargeAmount - D_CancelAmount - ";
                $sql .= "                                         D_SettlementBackAmount - D_OemTransferAmount - D_ReClaimFeeAmount - D_DamageAmount - ";
                $sql .= "                                         D_ReceiptAmount - D_RepayAmount - D_BadDebtAmount - D_OtherPaymentAmount ";
                $sql .= " WHERE   DailyMonthlyFlg = 0 ";
                $sql .= " AND     ProcessingDate = '2015-12-24' ";
                $sql .= " AND     EnterpriseId = :EnterpriseId ";

                $this->dbAdapter->query($sql)->execute(array(':EnterpriseId' => $enterpriseId));
            }
        }
        catch(\Exception $err) {
            throw new \Exception('hoge_20151225');
        }

        return;
    }

    /**
     * 入金関連処理ファンクションの基礎SQL取得。
     *
     * @return 入金関連処理ファンクションの基礎SQL
     */
    protected function getBaseP_ReceiptControl() {
        return <<<EOQ
CALL P_ReceiptControl(
    :pi_receipt_amount
,   :pi_order_seq
,   :pi_receipt_date
,   :pi_receipt_class
,   :pi_branch_bank_id
,   :pi_receipt_agent_id
,   :pi_deposit_date
,   :pi_user_id
,   @po_ret_sts
,   @po_ret_errcd
,   @po_ret_sqlcd
,   @po_ret_msg
    )
EOQ;
    }
}

Application::getInstance()->run();
