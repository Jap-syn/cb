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
use models\Table\TableUser;
use Coral\Coral\History\CoralHistoryOrder;
use models\Table\TableOrder;
use models\Table\TableClaimBatchControl;
use models\Table\ATableOrder;
use models\Logic\LogicCreditTransfer;

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
     * @var メール環境
     */
    public $mail;

    /**
     * アプリケーションを実行します。
     *
     * @access public
     */
    public function run() {
        $exitCode = 1;
        $isBeginTran = false;

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
$this->logger->info('claim03t_compdata.php start');

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

            // メールに絡む属性
            $this->mail = $data['mail'];

            // ユーザーID取得
            $mdlu = new TableUser($this->dbAdapter);
            $userId = $mdlu->getUserId(TableUser::USERCLASS_SYSTEM, TableUser::SEQ_BATCH_USER);

            // 主処理
            $this->dbAdapter->getDriver()->getConnection()->beginTransaction();
            $isBeginTran = true;
            // (請求確定処理)
            $this->execClaimControl($userId);
            // (請求バッチ管理(T_ClaimBatchControl)更新)
            $this->updClaimBatchControl($this->getClaimBatchControlSeq());
            $this->dbAdapter->getDriver()->getConnection()->commit();
            $isBeginTran = false;

$this->logger->info('claim03t_compdata.php end');
            $exitCode = 0;

        } catch( \Exception $e ) {
            // ロールバック
            if ($isBeginTran) {
                $this->dbAdapter->getDriver()->getConnection()->rollback();
            }

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
     * 請求バッチ管理の最新シーケンス取得
     *
     * @return int 請求バッチ管理の最新シーケンス
     * @see [請求バッチ管理.請求データ送信バッチ実行フラグ(SendFlg)]が、[1:完了]であることは、本バッチ起動時には確定している
     */
    protected function getClaimBatchControlSeq() {
        return $this->dbAdapter->query(" SELECT Seq FROM T_ClaimBatchControl ORDER BY 1 DESC LIMIT 1 ")->execute(null)->current()['Seq'];
    }

    /**
     * 請求確定処理を行う
     *
     * @param int $prmUserId ユーザーID
     * @throws Exception
     */
    protected function execClaimControl($prmUserId) {

        // 変数の初期化
        $history = new CoralHistoryOrder($this->dbAdapter);
        $mdlo = new TableOrder($this->dbAdapter);
        $mdlao = new ATableOrder($this->dbAdapter);
        $lgc = new LogicCreditTransfer($this->dbAdapter);

        // 対象データの取得
        $sql = <<<EOQ
SELECT ch.Seq
       ,o.OrderSeq
       ,IFNULL(o.OutOfAmends, 0) AS OutOfAmends
       ,ch.ClaimPattern
       ,e.CreditTransferFlg
       ,e.OemId
       ,e.AppFormIssueCond
       ,ch.LimitDate
FROM   T_ClaimHistory ch
       INNER JOIN T_Order      o ON (o.OrderSeq = ch.OrderSeq)
       INNER JOIN T_Enterprise e ON (e.EnterpriseId = o.EnterpriseId)
       INNER JOIN T_Site       s ON (s.SiteId = o.SiteId)
WHERE  1 = 1
AND   ( ch.PrintedFlg = 0 OR ch.PrintedFlg = 2 )
AND    ch.ValidFlg = 1
AND    o.Cnl_Status = 0
AND    IFNULL(o.LetterClaimStopFlg, 0) = 0
AND    ch.EnterpriseBillingCode IS NULL
AND    o.DataStatus < 91;
EOQ;

        $ri = $this->dbAdapter->query($sql)->execute(null);

        // 請求プロシージャの準備
        $stm = $this->dbAdapter->query($this->getBaseP_ClaimControl());
        $getretvalsql = " SELECT @po_ret_sts AS po_ret_sts, @po_ret_errcd AS po_ret_errcd, @po_ret_sqlcd AS po_ret_sqlcd, @po_ret_msg AS po_ret_msg ";
        $stm2 = $this->dbAdapter->query($this->getBaseP_ReceiptControl());
        $getretvalsql2 = " SELECT @po_ret_sts AS po_ret_sts, @po_ret_errcd AS po_ret_errcd, @po_ret_sqlcd AS po_ret_sqlcd, @po_ret_msg AS po_ret_msg ";

        // 対象データを取得分ループ
        foreach ($ri as $row) {

            // 請求管理データの更新(P_ClaimControl CALL)
            $stm->execute(array(
                    ':pi_history_seq'   => $row['Seq'],
                    ':pi_button_flg'    => 1,
                    ':pi_user_id'       => $prmUserId,
            ));

            // 督促の場合の処理
            if ($row['ClaimPattern'] > 1) {

                // 紙請求ストップフラグの判定
                $letterClaimStopFlg = 0;
                if (($row['OutOfAmends'] == 0 && $row['ClaimPattern'] >= 9) ||
                    ($row['OutOfAmends'] == 1 && ($row['ClaimPattern'] >= 3 && $row['ClaimPattern'] < 9))) {
                    $letterClaimStopFlg = 1;
                }

                // 注文データの更新
                $sql  = " UPDATE T_Order ";
                $sql .= " SET    LetterClaimStopFlg = :LetterClaimStopFlg ";
                $sql .= " ,      MailClaimStopFlg = 0 ";
                $sql .= " ,      UpdateId = :UpdateId ";
                $sql .= " ,      UpdateDate = :UpdateDate ";
                $sql .= " WHERE  P_OrderSeq = :OrderSeq ";

                $prm = array(
                        ':LetterClaimStopFlg'   => $letterClaimStopFlg,
                        ':UpdateId'             => $prmUserId,
                        ':UpdateDate'           => date('Y-m-d H:i:s'),
                        ':OrderSeq'             => $row['OrderSeq'],
                );
                $this->dbAdapter->query($sql)->execute($prm);

            }

            // 履歴登録用理由コードを設定
            $reasonCode = 42; // 請求書発行(初回)
            if ($row['ClaimPattern'] == 2) {
                $reasonCode = 43;       // 再請求書発行（再１）
            } else if ($row['ClaimPattern'] == 4) {
                $reasonCode = 44;       // 再請求書発行（再３）
            } else if ($row['ClaimPattern'] == 6) {
                $reasonCode = 45;       // 再請求書発行（再４）
            } else if ($row['ClaimPattern'] == 7) {
                $reasonCode = 46;       // 再請求書発行（再５）
            } else if ($row['ClaimPattern'] == 8) {
                $reasonCode = 47;       // 再請求書発行（再６）
            } else if ($row['ClaimPattern'] == 9) {
                $reasonCode = 48;       // 再請求書発行（再７）
            }

            // 注文履歴登録用に親注文Seqから子注文Seqを再取得する。
            $sql = "SELECT OrderSeq FROM T_Order WHERE P_OrderSeq = :P_OrderSeq AND Cnl_Status = 0";
            $ri2 = $this->dbAdapter->query($sql)->execute(array(':P_OrderSeq' => $row['OrderSeq']));

            // 注文履歴へ登録(取得できた件数分ループする)
            foreach ($ri2 as $row2) {
                // 備考に保存
                $mdlo->appendPrintedInfoToOemNote($row2["OrderSeq"]);
                // 注文履歴登録
                $history->InsOrderHistory($row2["OrderSeq"], $reasonCode, $prmUserId);
            }

            $creditTransferMethod = $lgc->getCreditTransferMethod($row['OrderSeq']);
            // 出力した請求履歴データに対する更新処理
            $sql  = " UPDATE T_ClaimHistory ";
            $sql .= " SET    PrintedStatus = 9 ";
            $sql .= " ,      CreditTransferMethod = :CreditTransferMethod ";
            $sql .= " ,      UpdateId = :UpdateId ";
            $sql .= " ,      UpdateDate = :UpdateDate ";
            $sql .= " WHERE  Seq = :Seq ";

            $this->dbAdapter->query($sql)->execute(array(
                    ':CreditTransferMethod' => $creditTransferMethod,
                    ':UpdateId' => $prmUserId,
                    ':UpdateDate' => date('Y-m-d H:i:s'),
                    ':Seq' => $row['Seq']
            ));

            // 加盟店顧客の申込ステータスの更新（口座振替顧客のみ）
            // 注文SEQから加盟店顧客を特定する。
            $sql  = " SELECT c.EntCustSeq ";
            $sql .= " FROM   AT_Order ao ";
            $sql .= " INNER JOIN T_Customer c ON (ao.OrderSeq = c.OrderSeq) ";
            $sql .= " INNER JOIN T_EnterpriseCustomer ec ON (c.EntCustSeq = ec.EntCustSeq) ";
            $sql .= " WHERE  1 = 1 ";
            $sql .= " AND    ao.OrderSeq = :OrderSeq ";
            $sql .= " AND    ao.CreditTransferRequestFlg > 0 ";
            $sql .= " AND    ec.RequestStatus IS NULL ";

            $entCustSeq = $this->dbAdapter->query($sql)->execute(array(':OrderSeq' => $row['OrderSeq']))->current()['EntCustSeq'];

            //EntCustSeq対象が抽出できた場合、加盟店顧客．申込みステータスを更新する
            if(!empty($entCustSeq)){
                $sql  = " UPDATE T_EnterpriseCustomer ";
                $sql .= " SET    RequestStatus = 1 ";
                $sql .= " ,      RequestSubStatus = 1 ";
                $sql .= " ,      UpdateId = 1 ";
                $sql .= " ,      UpdateDate = :UpdateDate ";
                $sql .= " WHERE  EntCustSeq = :EntCustSeq ";

                $this->dbAdapter->query($sql)->execute(array(':UpdateDate' => date('Y-m-d H:i:s'),':EntCustSeq' => $entCustSeq));
//            }else{
//                // 対象が存在しなかった場合
//                continue;
            }

            // -----------------------------------------------------------------
            // 請求管理.口座振替利用を更新
            // -----------------------------------------------------------------
            if ($row['ClaimPattern'] == 1) {
                $sql  = " SELECT e.CreditTransferFlg ";
                $sql .= " FROM   AT_Order ao ";
                $sql .= " INNER JOIN T_Customer c ON (ao.OrderSeq = c.OrderSeq) ";
                $sql .= " INNER JOIN T_EnterpriseCustomer ec ON (c.EntCustSeq = ec.EntCustSeq) ";
                $sql .= " INNER JOIN T_Enterprise e ON (ec.EnterpriseId = e.EnterpriseId) ";
                $sql .= " WHERE  1 = 1 ";
                $sql .= " AND    ao.OrderSeq = :OrderSeq ";
                $sql .= " AND    ao.CreditTransferRequestFlg > 0 ";
                $sql .= " AND    ec.RequestStatus = 2 ";
                $creditTransferFlg = $this->dbAdapter->query($sql)->execute(array(':OrderSeq' => $row['OrderSeq']))->current()['CreditTransferFlg'];

                $creditTransferDate = null;
                if (($creditTransferMethod == 2) || ($creditTransferMethod == 3)) {
                    $creditTransferDate = $lgc->getTransderCommitDate4LimitDate($row['LimitDate'], $creditTransferFlg);
                }

                if(!empty($creditTransferFlg)){
                    $sql  = " UPDATE T_ClaimControl ";
                    $sql .= " SET    CreditTransferFlg = :CreditTransferFlg ";
                    $sql .= " ,      F_CreditTransferDate = :CreditTransferDate ";
                    $sql .= " ,      UpdateId = 1 ";
                    $sql .= " ,      UpdateDate = :UpdateDate ";
                    $sql .= " WHERE  OrderSeq = :OrderSeq ";

                    $this->dbAdapter->query($sql)->execute(array(':CreditTransferFlg' => $creditTransferFlg, ':CreditTransferDate' => $creditTransferDate, ':UpdateDate' => date('Y-m-d H:i:s'),':OrderSeq' => $row['OrderSeq']));
                }
            }

            // -----------------------------------------------------------------
            // 強制解約日印字による紙ストップ設定
            // -----------------------------------------------------------------
            $sql  = " SELECT c.CancelNoticePrintStopStatus,o.Ent_Note FROM T_ClaimControl c INNER JOIN T_Order o ON c.OrderSeq=o.OrderSeq WHERE c.OrderSeq = :OrderSeq ";
            $stopData = $this->dbAdapter->query($sql)->execute(array(':OrderSeq' => $row['OrderSeq']))->current();
            if ($stopData['CancelNoticePrintStopStatus'] == 0) {
                if (preg_match("/^強制解約日=[0-9]{4}\/[0-9]{2}\/[0-9]{2}$/u", $stopData['Ent_Note'])) {
                    $claimStopReleaseDate = str_replace("強制解約日=", "", $stopData['Ent_Note']);
                }
                // 注文データの更新
                $sql  = " UPDATE T_Order ";
                $sql .= " SET    LetterClaimStopFlg = 1 ";
                $sql .= " ,      ClaimStopReleaseDate = :ClaimStopReleaseDate ";
                $sql .= " ,      UpdateId = :UpdateId ";
                $sql .= " ,      UpdateDate = :UpdateDate ";
                $sql .= " WHERE  P_OrderSeq = :OrderSeq ";
                $prm = array(
                    ':ClaimStopReleaseDate' => $claimStopReleaseDate,
                    ':UpdateId'             => $prmUserId,
                    ':UpdateDate'           => date('Y-m-d H:i:s'),
                    ':OrderSeq'             => $row['OrderSeq'],
                );
                $this->dbAdapter->query($sql)->execute($prm);
            }


            // -----------------------------------------------------------------
            // ０円請求の入金クローズ
            // 　口振選択加盟店、且つ、注文.口座振替利用（1：Web、2：紙面）、且つ、請求金額が0円の場合は、入金クローズとする
            // -----------------------------------------------------------------
            $sql  = " SELECT SUM(UseAmount) AS UseAmount FROM T_Order WHERE Cnl_Status = 0 AND P_OrderSeq = :OrderSeq ";
            $useAmount = $this->dbAdapter->query($sql)->execute(array(':OrderSeq' => $row['OrderSeq']))->current()['UseAmount'];

            if ($useAmount != 0) {
//                // CB_B2C_DEV-750対策
//                $aoInfo = $mdlao->find($row['OrderSeq'])->current();
//                if (($row['AppFormIssueCond'] == 1) && ($aoInfo['CreditTransferRequestFlg'] == 1)) {
//                    $sql  = " SELECT ec.* FROM T_Customer c INNER JOIN T_EnterpriseCustomer ec ON c.EntCustSeq = ec.EntCustSeq WHERE c.OrderSeq = :OrderSeq ";
//                    $requestStatus = $this->dbAdapter->query($sql)->execute(array(':OrderSeq' => $row['OrderSeq']))->current()['RequestStatus'];
//                    if (is_null($requestStatus) || ($requestStatus = 9)) {
//                        $sql  = " UPDATE T_ClaimHistory ";
//                        $sql .= " SET    ZeroAmountClaimMailFlg = 0 ";
//                        $sql .= " ,      UpdateId = 1 ";
//                        $sql .= " ,      UpdateDate = :UpdateDate ";
//                        $sql .= " WHERE  OrderSeq = :OrderSeq ";
//                        $this->dbAdapter->query($sql)->execute(array(':UpdateDate' => date('Y-m-d H:i:s'),':OrderSeq' => $row['OrderSeq']));
//                    }
//                }
                continue;
            }
            if (($row['CreditTransferFlg'] != 1) && ($row['CreditTransferFlg'] != 2) && ($row['CreditTransferFlg'] != 3)) {
                continue;
            }

            // 注文取りまとめの際の親注文の状況チェック
            $aoInfo = $mdlao->find($row['OrderSeq'])->current();
            if (($aoInfo['CreditTransferRequestFlg'] != 1) && ($aoInfo['CreditTransferRequestFlg'] != 2)) {
                continue;
            }

            // ①入金プロシージャー(P_ReceiptControl)呼び出し
            $prm = array(
                ':pi_receipt_amount'   => 0,
                ':pi_order_seq'        => $row['OrderSeq'],
                ':pi_receipt_date'     => date('Y-m-d'),
                ':pi_receipt_class'    => 13, // 13:口座振替
                ':pi_branch_bank_id'   => null,
                ':pi_receipt_agent_id' => null,
                ':pi_deposit_date'     => date('Y-m-d'),
                ':pi_user_id'          => $prmUserId,
                ':pi_receipt_note'     => null,
            );

            try {
                $stm2->execute($prm);

                // SQL実行例外なしもエラー戻り値の時は例外をｽﾛｰ
                $retval = $this->dbAdapter->query($getretvalsql2)->execute(null)->current();
                if ($retval['po_ret_sts'] != 0) {
                    throw new \Exception($retval['po_ret_msg']);
                }
            }
            catch(\Exception $e) { throw $e; }

            // ②未印刷の請求書印刷予約データを削除
            $mdlch = new models\Table\TableClaimHistory($this->dbAdapter);
            $mdlch->deleteReserved($row['OrderSeq'], $prmUserId);

            // ③立替・売上管理データ更新
            $mdlpas = new \models\Table\TablePayingAndSales($this->dbAdapter);
            $mdlapas = new \models\Table\ATablePayingAndSales($this->dbAdapter);
            // 既に[立替条件クリアフラグ]が[1：条件をクリアしている]か？の取得
            $isAlreadyClearCondition = $mdlpas->IsAlreadyClearCondition($row['OrderSeq']);
            $mdlpas->clearConditionForCharge($row['OrderSeq'], 1, $prmUserId);

            if (!$isAlreadyClearCondition) {
                $row_pas = $this->dbAdapter->query(" SELECT Seq FROM T_PayingAndSales WHERE OrderSeq = :OrderSeq "
                )->execute(array(':OrderSeq' => $row['OrderSeq']))->current();

                // 入金により立替条件クリアフラグが１化されるとき => '2:入金'として更新(顧客入金日をセットする)
                $mdlapas->saveUpdate(array('ATUriType' => 2, 'ATUriDay' => str_replace('-', '', date('Y-m-d'))), $row_pas['Seq']);
            }

            $sql = " SELECT MAX(ReceiptSeq) AS ReceiptSeq FROM T_ReceiptControl WHERE OrderSeq = :OrderSeq ";
            $receiptSeq = $this->dbAdapter->query($sql)->execute(array(':OrderSeq' => $row['OrderSeq']))->current()['ReceiptSeq'];

            // 注文履歴登録
            $history->InsOrderHistory($row["OrderSeq"], 61, $prmUserId);

            // AT_ReceiptControl登録
            $mdl_atrc = new \models\Table\ATableReceiptControl($this->dbAdapter);
            $rowATReceiptControl = $this->dbAdapter->query($sql)->execute(array(':OrderSeq' => $row['OrderSeq']))->current();
            $clearConditionForCharge = $rowATReceiptControl['ClearConditionForCharge'];
            $clearConditionDate = $rowATReceiptControl['ClearConditionDate'];

            // 入金取消前立替処理－ステータス、入金取消前配送－着荷確認
            $sql = "SELECT Chg_Status, Deli_ConfirmArrivalFlg FROM T_Order WHERE OrderSeq = :OrderSeq";
            $work2 = $this->dbAdapter->query($sql)->execute(array(':OrderSeq' => $row['OrderSeq']));
            $chgStatus = $work2->current()['Chg_Status'];
            $deliConfirmArrivalFlg = $work2->current()['Deli_ConfirmArrivalFlg'];
            $atdata = array(
                'ReceiptSeq' => $receiptSeq,
                'AccountNumber' => null,
                'BankFlg' => null, // 2：直接振込
                'Before_ClearConditionForCharge' => $clearConditionForCharge,
                'Before_ClearConditionDate' => $clearConditionDate,
                'Before_Chg_Status' => $chgStatus,
                'Before_Deli_ConfirmArrivalFlg' => $deliConfirmArrivalFlg
            );
            $mdl_atrc->saveNew($atdata);

            // 0円請求口振WEB申し込み案内メールフラグを未送信にする
            if ($aoInfo['CreditTransferRequestFlg'] == 1) {
                $sql  = " UPDATE T_ClaimHistory ";
                $sql .= " SET    ZeroAmountClaimMailFlg = 0 ";
                $sql .= " ,      UpdateId = 1 ";
                $sql .= " ,      UpdateDate = :UpdateDate ";
                $sql .= " WHERE  OrderSeq = :OrderSeq ";
                $this->dbAdapter->query($sql)->execute(array(':UpdateDate' => date('Y-m-d H:i:s'),':OrderSeq' => $row['OrderSeq']));
            }
        }
    }

    /**
     * 請求バッチ管理(T_ClaimBatchControl)のCompFlgを、[1:完了]に更新する
     *
     * @param int $prmCbSeq T_ClaimBatchControlのSEQ
     */
    protected function updClaimBatchControl($prmCbSeq) {

        $mdlcb = new TableClaimBatchControl($this->dbAdapter);

        // データ更新
        $data = array(
            'CompFlg' => 1,
        );
        $mdlcb->saveUpdate($data, $prmCbSeq);
    }

    /**
     * 請求関連処理ファンクションの基礎SQL取得。
     *
     * @return 請求関連処理ファンクションの基礎SQL
     */
    protected function getBaseP_ClaimControl() {
        return <<<EOQ
CALL P_ClaimControl(
    :pi_history_seq
,   :pi_button_flg
,   :pi_user_id
,   @po_ret_sts
,   @po_ret_errcd
,   @po_ret_sqlcd
,   @po_ret_msg
    )
EOQ;
    }

    /**
     * 入金関連処理ファンクションの基礎SQL取得。
     *
     * @return 入金関連処理ファンクションの基礎SQL
     */
    private function getBaseP_ReceiptControl() {
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
,   :pi_receipt_note
,   @po_ret_sts
,   @po_ret_errcd
,   @po_ret_sqlcd
,   @po_ret_msg
    )
EOQ;
    }
}

Application::getInstance()->run();
