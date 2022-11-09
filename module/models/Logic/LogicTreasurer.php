<?php
namespace models\Logic;

use Zend\Db\Adapter\Adapter;
use models\Table\TableUser;
use models\Table\TableSystemProperty;
use Coral\Base\BaseLog;
use Zend\Config\Reader\Ini;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Json\Json;
use models\Table\TableTemplateHeader;
use models\Table\TableTemplateField;

setlocale( LC_ALL, 'ja_JP.UTF-8' );
ini_set( 'default_charset', 'UTF-8' );
mb_internal_encoding( 'UTF-8' );
mb_http_output('UTF-8');
mb_regex_encoding( 'UTF-8' );

ini_set( 'max_execution_time', 0 );

/**
 * 会計クラス
 */
class LogicTreasurer
{
    /**
     * アダプタ
     *
     * @var Adapter
     */
    protected $_adapter = null;

    /**
     * コンストラクタ
     *
     * @param Adapter $adapter アダプタ
     */
    public function __construct(Adapter $adapter)
    {
        $this->_adapter = $adapter;
    }

    /**
     * 直営日次統計表（日次）
     */
    public function treas_dailystatisticstable_day(){
        $mdlu = new TableUser($this->_adapter);

        try {
            $this->_adapter->getDriver()->getConnection()->beginTransaction();

            // ﾕｰｻﾞｰID
            $userId = $mdlu->getUserId(99, 1);

            $stm = $this->_adapter->query("CALL P_Treas_DailyStatisticsTable_Day( :pi_user_id )");
            $prm = array( ':pi_user_id' => $userId );
            $ri = $stm->execute($prm);

            $this->_adapter->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            $this->_adapter->getDriver()->getConnection()->rollBack();
            throw $e;
        }
	}

    /**
     * 直営日次統計表（月次）
     */
    public function treas_dailystatisticstable_month(){
        $mdlu = new TableUser($this->_adapter);

        try {
            $this->_adapter->getDriver()->getConnection()->beginTransaction();

            // ﾕｰｻﾞｰID
            $userId = $mdlu->getUserId(99, 1);

            $stm = $this->_adapter->query("CALL P_Treas_DailyStatisticsTable_Month( :pi_user_id )");
            $prm = array( ':pi_user_id' => $userId );
            $ri = $stm->execute($prm);

            $this->_adapter->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            $this->_adapter->getDriver()->getConnection()->rollBack();
            throw $e;
        }
    }

    /**
	 * OEM日次統計表（日次）
     */
	public function treas_oem_dailystatisticstable_day(){
        $mdlu = new TableUser($this->_adapter);

        try {
            $this->_adapter->getDriver()->getConnection()->beginTransaction();

            // ﾕｰｻﾞｰID
            $userId = $mdlu->getUserId(99, 1);

            $stm = $this->_adapter->query("CALL P_Treas_Oem_DailyStatisticsTable_Day( :pi_user_id )");
            $prm = array( ':pi_user_id' => $userId );
            $ri = $stm->execute($prm);

            $this->_adapter->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            $this->_adapter->getDriver()->getConnection()->rollBack();
            throw $e;
        }
	}

    /**
	 * OEM日次統計表（月次）
	 */
	public function treas_oem_dailystatisticstable_month(){
	    $mdlu = new TableUser($this->_adapter);

	    try {
	        $this->_adapter->getDriver()->getConnection()->beginTransaction();

	        // ﾕｰｻﾞｰID
	        $userId = $mdlu->getUserId(99, 1);

	        $stm = $this->_adapter->query("CALL P_Treas_Oem_DailyStatisticsTable_Month( :pi_user_id )");
	        $prm = array( ':pi_user_id' => $userId );
	        $ri = $stm->execute($prm);

	        $this->_adapter->getDriver()->getConnection()->commit();
	    } catch (\Exception $e) {
	        $this->_adapter->getDriver()->getConnection()->rollBack();
	        throw $e;
	    }
	}


	/**
     * 直営未払金統計表
     */
    public function treas_accounts_payablestatisticstable(){
        $mdlu = new TableUser($this->_adapter);

        try {
            $this->_adapter->getDriver()->getConnection()->beginTransaction();

            // ﾕｰｻﾞｰID
            $userId = $mdlu->getUserId(99, 1);

            $stm = $this->_adapter->query("CALL P_Treas_Accounts_PayableStatisticsTable( :pi_user_id )");
            $prm = array( ':pi_user_id' => $userId );
            $ri = $stm->execute($prm);

            $this->_adapter->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            $this->_adapter->getDriver()->getConnection()->rollBack();
            throw $e;
        }
	}

    /**
     * OEM未払金統計表
     */
    public function treas_oemaccounts_payablestatisticstable(){
        $mdlu = new TableUser($this->_adapter);

        try {
            $this->_adapter->getDriver()->getConnection()->beginTransaction();

            // ﾕｰｻﾞｰID
            $userId = $mdlu->getUserId(99, 1);

            $stm = $this->_adapter->query("CALL P_Treas_OemAccounts_PayableStatisticsTable( :pi_user_id )");
            $prm = array( ':pi_user_id' => $userId );
            $ri = $stm->execute($prm);

            $this->_adapter->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            $this->_adapter->getDriver()->getConnection()->rollBack();
            throw $e;
        }
	}

    /**
     * 日次売上明細
     */
    public function treas_daily_salesdetails(){
        $mdlu = new TableUser($this->_adapter);

        try {
            $this->_adapter->getDriver()->getConnection()->beginTransaction();

            // ﾕｰｻﾞｰID
            $userId = $mdlu->getUserId(99, 1);

            $stm = $this->_adapter->query("CALL P_Treas_Daily_SalesDetails( :pi_user_id )");
            $prm = array( ':pi_user_id' => $userId );
            $ri = $stm->execute($prm);

            $this->_adapter->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            $this->_adapter->getDriver()->getConnection()->rollBack();
            throw $e;
        }
	}

    /**
     * 消費者未収金明細(月次)
     */
    public function treas_consumer_accountsdue(){
        $mdlu = new TableUser($this->_adapter);

        try {
            $this->_adapter->getDriver()->getConnection()->beginTransaction();

            // ﾕｰｻﾞｰID
            $userId = $mdlu->getUserId(99, 1);

            $stm = $this->_adapter->query("CALL P_Treas_Consumer_AccountsDue( :pi_user_id )");
            $prm = array( ':pi_user_id' => $userId );
            $ri = $stm->execute($prm);

            $this->_adapter->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            $this->_adapter->getDriver()->getConnection()->rollBack();
            throw $e;
        }
	}

    /**
     * 収納代行委託先未収金明細
     */
    public function treas_receiptagen_accountsdue(){
        $mdlu = new TableUser($this->_adapter);

        try {
            $this->_adapter->getDriver()->getConnection()->beginTransaction();

            // ﾕｰｻﾞｰID
            $userId = $mdlu->getUserId(99, 1);

            $stm = $this->_adapter->query("CALL P_Treas_ReceiptAgen_AccountsDue( :pi_user_id )");
            $prm = array( ':pi_user_id' => $userId );
            $ri = $stm->execute($prm);

            $this->_adapter->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            $this->_adapter->getDriver()->getConnection()->rollBack();
            throw $e;
        }
	}

    /**
     * OEM仮払金明細
     */
    public function treas_oem_suspensepayments(){
        $mdlu = new TableUser($this->_adapter);

        try {
            $this->_adapter->getDriver()->getConnection()->beginTransaction();

            // ﾕｰｻﾞｰID
            $userId = $mdlu->getUserId(99, 1);

            $stm = $this->_adapter->query("CALL P_Treas_Oem_SuspensePayments( :pi_user_id )");
            $prm = array( ':pi_user_id' => $userId );
            $ri = $stm->execute($prm);

            $this->_adapter->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            $this->_adapter->getDriver()->getConnection()->rollBack();
            throw $e;
        }
	}

    /**
     * 直営未払金兼売掛金明細(日次)
     */
    public function treas_accounts_payablereceivable_day(){
        $mdlu = new TableUser($this->_adapter);

        try {
            $this->_adapter->getDriver()->getConnection()->beginTransaction();

            // ﾕｰｻﾞｰID
            $userId = $mdlu->getUserId(99, 1);

            $stm = $this->_adapter->query("CALL P_Treas_Accounts_PayableReceivable_Day( :pi_user_id )");
            $prm = array( ':pi_user_id' => $userId );
            $ri = $stm->execute($prm);

            $this->_adapter->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            $this->_adapter->getDriver()->getConnection()->rollBack();
            throw $e;
        }
	}

    /**
     * 直営未払金兼売掛金明細（月次）
     */
    public function treas_accounts_payablereceivable_month(){
        $mdlu = new TableUser($this->_adapter);

        try {
            $this->_adapter->getDriver()->getConnection()->beginTransaction();

            // ﾕｰｻﾞｰID
            $userId = $mdlu->getUserId(99, 1);

            $stm = $this->_adapter->query("CALL P_Treas_Accounts_PayableReceivable_Month( :pi_user_id )");
            $prm = array( ':pi_user_id' => $userId );
            $ri = $stm->execute($prm);

            $this->_adapter->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            $this->_adapter->getDriver()->getConnection()->rollBack();
            throw $e;
        }
    }

    /**
     * OEM未払金兼売掛金明細
     */
    public function treas_oem_accounts_payablereceivable(){
        $mdlu = new TableUser($this->_adapter);

        try {
            $this->_adapter->getDriver()->getConnection()->beginTransaction();

            // ﾕｰｻﾞｰID
            $userId = $mdlu->getUserId(99, 1);

            $stm = $this->_adapter->query("CALL P_Treas_Oem_Accounts_PayableReceivable( :pi_user_id )");
            $prm = array( ':pi_user_id' => $userId );
            $ri = $stm->execute($prm);

            $this->_adapter->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            $this->_adapter->getDriver()->getConnection()->rollBack();
            throw $e;
        }
	}

    /**
     * 未収金日計
     */
    public function treas_accountsdue_dailyaccount(){
        $mdlu = new TableUser($this->_adapter);

        try {
            $this->_adapter->getDriver()->getConnection()->beginTransaction();

            // ﾕｰｻﾞｰID
            $userId = $mdlu->getUserId(99, 1);

            $stm = $this->_adapter->query("CALL P_Treas_AccountsDue_DailyAccount( :pi_user_id )");
            $prm = array( ':pi_user_id' => $userId );
            $ri = $stm->execute($prm);

            $this->_adapter->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            $this->_adapter->getDriver()->getConnection()->rollBack();
            throw $e;
        }
	}

    /**
     * 仮払金日計
     */
    public function treas_suspensepayments_dailyaccount(){
        $mdlu = new TableUser($this->_adapter);

        try {
            $this->_adapter->getDriver()->getConnection()->beginTransaction();

            // ﾕｰｻﾞｰID
            $userId = $mdlu->getUserId(99, 1);

            $stm = $this->_adapter->query("CALL P_Treas_SuspensePayments_DailyAccount( :pi_user_id )");
            $prm = array( ':pi_user_id' => $userId );
            $ri = $stm->execute($prm);

            $this->_adapter->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            $this->_adapter->getDriver()->getConnection()->rollBack();
            throw $e;
        }
	}

    /**
     * 精算日計
     */
    public function treas_payoff_dailyaccount(){
        $mdlu = new TableUser($this->_adapter);

        try {
            $this->_adapter->getDriver()->getConnection()->beginTransaction();

            // ﾕｰｻﾞｰID
            $userId = $mdlu->getUserId(99, 1);

            $stm = $this->_adapter->query("CALL P_Treas_PayOff_DailyAccount( :pi_user_id )");
            $prm = array( ':pi_user_id' => $userId );
            $ri = $stm->execute($prm);

            $this->_adapter->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            $this->_adapter->getDriver()->getConnection()->rollBack();
            throw $e;
        }
	}

    /**
     * 貸倒債権一覧
     */
    public function treas_baddebt_list(){
        $mdlu = new TableUser($this->_adapter);

        try {
            $this->_adapter->getDriver()->getConnection()->beginTransaction();

            // ﾕｰｻﾞｰID
            $userId = $mdlu->getUserId(99, 1);

            $stm = $this->_adapter->query("CALL P_Treas_BadDebt_List( :pi_user_id )");
            $prm = array( ':pi_user_id' => $userId );
            $ri = $stm->execute($prm);

            $this->_adapter->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            $this->_adapter->getDriver()->getConnection()->rollBack();
            throw $e;
        }
	}

    /**
     * 過剰金一覧（日次）
     */
    public function treas_excess_list_day(){
        $mdlu = new TableUser($this->_adapter);

        try {
            $this->_adapter->getDriver()->getConnection()->beginTransaction();

            // ﾕｰｻﾞｰID
            $userId = $mdlu->getUserId(99, 1);

            $stm = $this->_adapter->query("CALL P_Treas_Excess_List_Day( :pi_user_id )");
            $prm = array( ':pi_user_id' => $userId );
            $ri = $stm->execute($prm);

            $this->_adapter->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            $this->_adapter->getDriver()->getConnection()->rollBack();
            throw $e;
        }
	}

    /**
     * 過剰金一覧（月次）
     */
    public function treas_excess_list_month(){
        $mdlu = new TableUser($this->_adapter);

        try {
            $this->_adapter->getDriver()->getConnection()->beginTransaction();

            // ﾕｰｻﾞｰID
            $userId = $mdlu->getUserId(99, 1);

            $stm = $this->_adapter->query("CALL P_Treas_Excess_List_Month( :pi_user_id )");
            $prm = array( ':pi_user_id' => $userId );
            $ri = $stm->execute($prm);

            $this->_adapter->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            $this->_adapter->getDriver()->getConnection()->rollBack();
            throw $e;
        }
    }

    /**
     * 再発行手数料明細
     */
    public function treas_reissuefeespecificationt(){
        $mdlu = new TableUser($this->_adapter);

        try {
            $this->_adapter->getDriver()->getConnection()->beginTransaction();

            // ﾕｰｻﾞｰID
            $userId = $mdlu->getUserId(99, 1);

            $stm = $this->_adapter->query("CALL P_Treas_ReissueFeeSpecificationt( :pi_user_id )");
            $prm = array( ':pi_user_id' => $userId );
            $ri = $stm->execute($prm);

            $this->_adapter->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            $this->_adapter->getDriver()->getConnection()->rollBack();
            throw $e;
        }
	}

    /**
     * 無補償立替金戻し明細
     */
    public function treas_advancessettlementback(){
        $mdlu = new TableUser($this->_adapter);

        try {
            $this->_adapter->getDriver()->getConnection()->beginTransaction();

            // ﾕｰｻﾞｰID
            $userId = $mdlu->getUserId(99, 1);

            $stm = $this->_adapter->query("CALL P_Treas_AdvancesSettlementBack( :pi_user_id )");
            $prm = array( ':pi_user_id' => $userId );
            $ri = $stm->execute($prm);

            $this->_adapter->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            $this->_adapter->getDriver()->getConnection()->rollBack();
            throw $e;
        }
	}

    /**
     * OEM移管明細
     */
    public function treas_oem_transferspecification(){
        $mdlu = new TableUser($this->_adapter);

        try {
            $this->_adapter->getDriver()->getConnection()->beginTransaction();

            // ﾕｰｻﾞｰID
            $userId = $mdlu->getUserId(99, 1);

            $stm = $this->_adapter->query("CALL P_Treas_Oem_TransferSpecification( :pi_user_id )");
            $prm = array( ':pi_user_id' => $userId );
            $ri = $stm->execute($prm);

            $this->_adapter->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            $this->_adapter->getDriver()->getConnection()->rollBack();
            throw $e;
        }
	}

    /**
     * 調整金一覧
     */
    public function treas_adjustgoldlist(){
        $mdlu = new TableUser($this->_adapter);

        try {
            $this->_adapter->getDriver()->getConnection()->beginTransaction();

            // ﾕｰｻﾞｰID
            $userId = $mdlu->getUserId(99, 1);

            $stm = $this->_adapter->query("CALL P_Treas_AdjustGoldList( :pi_user_id )");
            $prm = array( ':pi_user_id' => $userId );
            $ri = $stm->execute($prm);

            $this->_adapter->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            $this->_adapter->getDriver()->getConnection()->rollBack();
            throw $e;
        }
	}

    /**
     * 入金先トータル表
     */
    public function treas_paymentplacetotal(){
        $mdlu = new TableUser($this->_adapter);

        try {
            $this->_adapter->getDriver()->getConnection()->beginTransaction();

            // ﾕｰｻﾞｰID
            $userId = $mdlu->getUserId(99, 1);

            $stm = $this->_adapter->query("CALL P_Treas_PaymentPlaceTotal( :pi_user_id )");
            $prm = array( ':pi_user_id' => $userId );
            $ri = $stm->execute($prm);

            $this->_adapter->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            $this->_adapter->getDriver()->getConnection()->rollBack();
            throw $e;
        }
	}

    /**
     * 会計後処理
     */
    public function treas_postprocessing(){
        $mdlu = new TableUser($this->_adapter);

        try {
            $this->_adapter->getDriver()->getConnection()->beginTransaction();

            // ﾕｰｻﾞｰID
            $userId = $mdlu->getUserId(99, 1);

            $stm = $this->_adapter->query("CALL P_Treas_PostProcessing( :pi_user_id )");
            $prm = array( ':pi_user_id' => $userId );
            $ri = $stm->execute($prm);

            $this->_adapter->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            $this->_adapter->getDriver()->getConnection()->rollBack();
            throw $e;
        }
    }

    /**
     * 加盟店月締め情報
     */
    public function treas_enterprise_monthlyclosinginfo(){
        $mdlu = new TableUser($this->_adapter);

        try {
            $this->_adapter->getDriver()->getConnection()->beginTransaction();

            // ﾕｰｻﾞｰID
            $userId = $mdlu->getUserId(99, 1);

            $stm = $this->_adapter->query("CALL P_Treas_Enterprise_MonthlyClosingInfo( :pi_user_id )");
            $prm = array( ':pi_user_id' => $userId );
            $ri = $stm->execute($prm);

            $this->_adapter->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            $this->_adapter->getDriver()->getConnection()->rollBack();
            throw $e;
        }
    }

    /**
     * 会計月更新処理
     */
    public function treas_updateaccountingmonth() {

        try {
            $this->_adapter->getDriver()->getConnection()->beginTransaction();

            // 会計月を+1月更新する
            $sql = <<<EOQ
UPDATE  T_SystemProperty
SET     PropValue = CAST(DATE_ADD(STR_TO_DATE(PropValue, '%Y-%m-%d'), interval 1 month) AS CHAR)
WHERE   Module    = :Module
AND     Category  = :Category
AND     Name      = :Name
EOQ;

            $prm = array(
                    ':Module'   => TableSystemProperty::DEFAULT_MODULE,
                    ':Category' => 'systeminfo',
                    ':Name'     => 'AccountingMonth',
            );
            $this->_adapter->query($sql)->execute($prm);

            $this->_adapter->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            $this->_adapter->getDriver()->getConnection()->rollBack();
            throw $e;
        }
    }

    /**
     * 日次用ファイル作成処理
     */
    public function account_report_day() {

        try {
            // 会計帳票/CSV作成処理
            // (業務日付（バッチの実行時に利用する想定）)
            $today = $this->_adapter->query(" SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'systeminfo' AND Name = 'BusinessDate' "
            )->execute(null)->current()['PropValue'];
            // $today = '2020-04-25';
            $url = 'http://localhost/cbadmin/AccountReport/executedairy/day/' . $today;

            // 一時ファイルの先指定
            $mdlsp = new \models\Table\TableSystemProperty($this->_adapter);
            $tempDir = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'TempFileDir');

            $savename = $tempDir . '/kaikei_dairy_' . date('Ymd', strtotime($today)) . '.zip';
            $client = new \Zend\Http\Client();
            $client->setStream();
            $client->setUri($url);
            $client->setOptions(array('timeout' => 21600, 'keepalive' => true, 'maxredirects' => 1, 'outputstream' => true));  // 20150717 試行回数(maxredirects) を 1 に設定
            $response = $client->send();
            copy($response->getStreamName(), $savename);

            // 会計帳票ファイル日次登録
            $this->_saveATReportFileDaily($today, $savename);

            // ダウンロードファイル削除
            unlink($savename);
        } catch (\Exception $e) {
            throw  $e;
        }
    }

    /**
     * 会計帳票ファイル日次登録
     *
     * @param string $today YYYY-MM-DD形式
     * @param string $filename ファイル名(ZIP形式)
     */
    protected function _saveATReportFileDaily($today, $filename)
    {
        // (ZIPファイル)
        $obj_file = null;
        if (!is_null($filename)) {
            $fp = fopen($filename, "rb");
            $obj_file = fread($fp, filesize($filename));
            fclose($fp);
        }

        // 既に登録がある場合はDELETE⇒INSERT
        $cnt = (int)$this->_adapter->query(" SELECT COUNT(1) AS cnt FROM AT_ReportFileDaily WHERE CreateDate = :CreateDate "
        )->execute(array(':CreateDate' => $today))->current()['cnt'];
        if ($cnt != 0) {
            $this->_adapter->query(" DELETE FROM AT_ReportFileDaily WHERE CreateDate = :CreateDate ")->execute(array(':CreateDate' => $today));
        }
        // 登録
        // ユーザーID(バッチユーザー)の取得
        $obj = new \models\Table\TableUser($this->_adapter);
        $userId = $obj->getUserId( 99, 1 );

        // 会計月（YYYY-MM-DD)※DDは01固定
        $sql = " SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'systeminfo' AND Name = 'AccountingMonth' ";
        $presentMonth = $this->_adapter->query($sql)->execute(null)->current()['PropValue'];

        $sql  = " INSERT INTO AT_ReportFileDaily (CreateDate, PresentMonth, ReportFile, Reserve, RegistDate, RegistId, ValidFlg) VALUES ( ";
        $sql .= "     :CreateDate ";
        $sql .= " ,   :PresentMonth ";
        $sql .= " ,   :ReportFile ";
        $sql .= " ,   :Reserve ";
        $sql .= " ,   :RegistDate ";
        $sql .= " ,   :RegistId ";
        $sql .= " ,   :ValidFlg ";
        $sql .= " ) ";

        $prm_save = array (
                'CreateDate'   => $today,
                'PresentMonth' => $presentMonth,
                'ReportFile'   => $obj_file,
                'Reserve'      => null,
                'RegistDate'   => date('Y-m-d H:i:s'),
                'RegistId'     => $userId,
                'ValidFlg'     => 1,
        );

        $this->_adapter->query($sql)->execute($prm_save);

        return;
    }

    /**
     * 月次用ファイル作成
     */
    public function account_report_month() {
        // 会計帳票/CSV作成処理
        // (業務日付（バッチの実行時に利用する想定）)
        $accountingMonth = $this->_adapter->query(" SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'systeminfo' AND Name = 'AccountingMonth' "
        )->execute(null)->current()['PropValue'];
        // $accountingMonth = '2020-08-01';
        $url = 'http://localhost/cbadmin/AccountReport/executemonthly/month/' . $accountingMonth;

        $today = date('Y-m-d');

        // 一時ファイルの先指定
        $mdlsp = new \models\Table\TableSystemProperty($this->_adapter);
        $tempDir = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'TempFileDir');

        $savename = $tempDir . '/kaikei_monthly_' . date('Ym', strtotime($accountingMonth)) . '.zip';
        $client = new \Zend\Http\Client();
        $client->setStream();
        $client->setUri($url);
        $client->setOptions(array('timeout' => 21600, 'keepalive' => true, 'maxredirects' => 1, 'outputstream' => true));  // 20150717 試行回数(maxredirects) を 1 に設定
        $response = $client->send();
        copy($response->getStreamName(), $savename);

        // 会計帳票ファイル月次登録
        $this->_saveATReportFileMonthly($today, $savename);

        // ダウンロードファイル削除
        unlink($savename);
    }

    /**
     * 会計帳票ファイル月次登録
     *
     * @param string $today YYYY-MM-DD形式
     * @param string $filename ファイル名(ZIP形式)
     */
    protected function _saveATReportFileMonthly($today, $filename)
    {
        // (ZIPファイル)
        $obj_file = null;
        if (!is_null($filename)) {
            $fp = fopen($filename, "rb");
            $obj_file = fread($fp, filesize($filename));
            fclose($fp);
        }

        // 既に登録がある場合はDELETE⇒INSERT
        $cnt = (int)$this->_adapter->query(" SELECT COUNT(1) AS cnt FROM AT_ReportFileMonthly WHERE CreateDate = :CreateDate "
        )->execute(array(':CreateDate' => $today))->current()['cnt'];
        if ($cnt != 0) {
            $this->_adapter->query(" DELETE FROM AT_ReportFileMonthly WHERE CreateDate = :CreateDate ")->execute(array(':CreateDate' => $today));
        }
        // 登録
        // ユーザーID(バッチユーザー)の取得
        $obj = new \models\Table\TableUser($this->_adapter);
        $userId = $obj->getUserId( 99, 1 );

        // 会計月（YYYY-MM-DD)※DDは01固定
        $sql = " SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'systeminfo' AND Name = 'AccountingMonth' ";
        $presentMonth = $this->_adapter->query($sql)->execute(null)->current()['PropValue'];

        $sql  = " INSERT INTO AT_ReportFileMonthly (CreateDate, PresentMonth, ReportFile, Reserve, RegistDate, RegistId, ValidFlg) VALUES ( ";
        $sql .= "     :CreateDate ";
        $sql .= " ,   :PresentMonth ";
        $sql .= " ,   :ReportFile ";
        $sql .= " ,   :Reserve ";
        $sql .= " ,   :RegistDate ";
        $sql .= " ,   :RegistId ";
        $sql .= " ,   :ValidFlg ";
        $sql .= " ) ";

        $prm_save = array (
                'CreateDate'   => $today,
                'PresentMonth' => $presentMonth,
                'ReportFile'   => $obj_file,
                'Reserve'      => null,
                'RegistDate'   => date('Y-m-d H:i:s'),
                'RegistId'     => $userId,
                'ValidFlg'     => 1,
        );

        $this->_adapter->query($sql)->execute($prm_save);

        return;
    }

    // 2016/02/08 Y.Suzuki Add AW_Receiptが作成されないようなので、プロシージャの直接コールをロジックのコールへ変更。 Stt
    /**
     * 月次用ワークテーブル作成
     */
    public function treas_make_wk_receipt(){
        try {
            $this->_adapter->getDriver()->getConnection()->beginTransaction();

            $stm = $this->_adapter->query("CALL P_Treas_Make_Wk_Receipt()");
            $ri = $stm->execute(null);

            $this->_adapter->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            $this->_adapter->getDriver()->getConnection()->rollBack();
            throw $e;
        }
    }
    // 2016/02/08 Y.Suzuki Add AW_Receiptが作成されないようなので、プロシージャの直接コールをロジックのコールへ変更。 End

    // 2016/02/08 Y.Suzuki Add 入金取消のマイナス売上対応_ワークテーブル作成用プロシージャをコール End
    /**
     * 日次用ワークテーブル作成
     */
    public function treas_make_wk_receipt_day(){
        try {
            $this->_adapter->getDriver()->getConnection()->beginTransaction();

            $stm = $this->_adapter->query("CALL P_Treas_Make_Wk_Receipt_Day()");
            $ri = $stm->execute(null);

            $this->_adapter->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            $this->_adapter->getDriver()->getConnection()->rollBack();
            throw $e;
        }
    }
    // 2016/02/08 Y.Suzuki Add 入金取消のマイナス売上対応_ワークテーブル作成用プロシージャをコール End

}
