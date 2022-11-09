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
use Coral\Base\IO\BaseIOUtility;
use Coral\Coral\History\CoralHistoryOrder;
use models\Table\ATableReceiptControl;
use models\Table\TableClaimControl;
use models\Table\TableClaimHistory;
use models\Table\TableOrder;
use models\Table\TablePayingAndSales;
use models\Table\TableStagnationAlert;
use models\Table\TableStampFee;
use models\View\ViewDelivery;
use models\View\ViewOrderCustomer;
use Zend\Db\Adapter\Adapter;
use Zend\Config\Reader\Ini;
use models\Table\TableUser;
use models\Table\TableCode;
use models\Logic\LogicCreditTransfer;
use models\Table\TableSystemProperty;
use Zend\Http\Client;
use models\Table\ATablePayingAndSales;

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

$this->logger->info('importMufjReceiptData.php start');

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

            $this->execMain();

$this->logger->info('importMufjReceiptData.php end');
            $exitCode = 0;

        } catch( \Exception $e ) {
            // エラーログを出力
            if ( isset($this->logger) ) {
$this->logger->err($e->getMessage());
            }
        }

        // 終了コードを指定して処理終了
        exit($exitCode);

    }

    private function execMain()
    {
        $mdlo = new TableOrder($this->dbAdapter);
        $mdlch = new TableClaimHistory($this->dbAdapter);
        $mdlps = new TablePayingAndSales($this->dbAdapter);
        $mdlstmp = new TableStampFee($this->dbAdapter);
        $history = new CoralHistoryOrder($this->dbAdapter);
        $mdlapas = new ATablePayingAndSales($this->dbAdapter);
        $mdlatrc = new ATableReceiptControl($this->dbAdapter);

        // ユーザーID取得
        $mdlu = new TableUser($this->dbAdapter);
        $userId = $mdlu->getUserId(TableUser::USERCLASS_SYSTEM, TableUser::SEQ_BATCH_USER);
        $importDate = date('Y-m-d H:i:s');

        // トランザクション開始
        $this->dbAdapter->getDriver()->getConnection()->beginTransaction();

        // 処理対象データ取得
        $sql = " SELECT * FROM T_MufjReceipt WHERE ProcessClass=0 AND ValidFlg=1 ORDER BY Seq";
        $targets = $this->dbAdapter->query($sql)->execute();

        $stm = $this->dbAdapter->query($this->getBaseP_ReceiptControl());

        // SQL実行結果取得用のSQL
        $getretvalsql = " SELECT @po_ret_sts AS po_ret_sts, @po_ret_errcd AS po_ret_errcd, @po_ret_sqlcd AS po_ret_sqlcd, @po_ret_msg AS po_ret_msg ";

        $i=0;
        foreach ($targets as $target) {
            $i++;
$this->logger->info('importMufjReceiptData '.$i);
            if (trim($target['ResponseData']) == 0) {
                $this->saveReceipt(1, '空行の為スキップ', null, null, null, 0, $importDate, $userId, 0, $target['Seq']);
                continue;
            }

            // レコード区分
            $recordType = (int)substr($target['ResponseData'], 0, 1);
            if ($recordType != 2) {
                $this->saveReceipt(1, 'データレコードではない為スキップ', null, null, null, 0, $importDate, $userId, 0, $target['Seq']);
                continue;
            }

            $dataKind = (int)substr($target['ResponseData'], 1, 2);
            if (($dataKind != 1) && ($dataKind != 2) && ($dataKind != 3)) {
                $this->saveReceipt(1, '処理対象ではない為スキップ', null, null, null, 0, $importDate, $userId, 0, $target['Seq']);
                continue;
            }

            // 入金金額
            $paymentAmount = (int)substr($target['ResponseData'], 52, 6);

            // キー情報から得たOrderSeqで注文の存在を確認
            $oseq = (int)substr($target['ResponseData'], 34, 10);
            $orderRow = $mdlo->find($oseq)->current();
            $orderId = null;
            if($orderRow) {
                $orderId = $orderRow['OrderId'];
            } else {
                $this->saveReceipt(1, sprintf('未存在：指定された注文SEQ = %s', $oseq), $oseq, null, $paymentAmount, 1, $importDate, $userId, 1, $target['Seq']);
                continue;
            }

            if ($dataKind == 1) {
                // 処理しようとしている注文データが入金待ち、一部入金、入金済みクローズであるかのチェック
                $sql = "SELECT COUNT(*) AS cnt FROM T_Order WHERE (DataStatus IN (51, 61) OR (DataStatus = 91 AND CloseReason = 1)) AND Cnl_Status = 0 AND OrderId = :OrderId";
                $checkCount = $this->dbAdapter->query($sql)->execute(array(':OrderId' => $orderId))->current()['cnt'];
                //クレジットカードで支払った注文のチェック
                $sqlCredit = "SELECT COUNT(*) AS cnt FROM AT_Order WHERE ExtraPayType = 1 AND ExtraPayKey IS NOT NULL AND OrderSeq = :OrderSeq";
                $checkCountCredit = $this->dbAdapter->query($sqlCredit)->execute(array(':OrderSeq' => $oseq))->current()['cnt'];

                // 支払方法判定
                $cvs = substr($target['ResponseData'], 101, 4);
                $payWayType = 1;
                switch ($cvs) {
                    case '0180':
                        $payWayType = 4;
                        break;
                    case '0190':
                        $payWayType = 7;
                        break;
                    case '9900':
                        $payWayType = 2;
                        break;
                }

                if ($checkCount <= 0 || $checkCountCredit >= 1) {
                    // エラーメッセージを入れておく。
                    $mdlv = new ViewOrderCustomer($this->dbAdapter);
                    $orderData = $mdlv->findOrderCustomer(array("OrderId" => $orderId))->current();

                    if (!$orderData) {
                        $this->saveReceipt(1, sprintf('該当注文無し：指定された注文ID = %s', $orderId), $oseq, $orderRow['EnterpriseId'], $paymentAmount, 1, $importDate, $userId, 1, $target['Seq']);
                    } else {
                        if ($checkCountCredit <= 0)
                        {
                            $this->saveReceipt(1, sprintf('入金待ちではない：注文ID = %s', $orderId), $oseq, $orderRow['EnterpriseId'], $paymentAmount, 1, $importDate, $userId, 1, $target['Seq']);
                        } else {
                            $this->saveReceipt(1, sprintf('クレジットカードで支払った注文：注文ID = %s', $orderId), $oseq, $orderRow['EnterpriseId'], $paymentAmount, 1, $importDate, $userId, 1, $target['Seq']);
                        }

                        $this->updateIncreNote($orderData['OrderSeq'], $userId);
                    }
                    continue;
                } else if ($checkCount > 1) {
                    $this->saveReceipt(1, sprintf('複数該当：指定された注文ID = %s', $orderId), $oseq, $orderRow['EnterpriseId'], $paymentAmount, 1, $importDate, $userId, 1, $target['Seq']);
                    continue;
                } else {
                    // OrderDataを求める。
                    $sql = "SELECT * FROM T_Order WHERE (DataStatus IN (51, 61) OR (DataStatus = 91 AND CloseReason = 1)) AND OrderId = :OrderId";
                    $orderData = $this->dbAdapter->query($sql)->execute(array(':OrderId' => $orderId))->current();

                    $oemId = (is_null($orderData['OemId'])) ? 0 : $orderData['OemId'];

                    // 入金前データステータスの取得
                    $datastatusBeforeReceipt = (int)$orderData['DataStatus'];

                    $paymentAmount = (int)substr($target['ResponseData'], 52, 6);
                    $custPaymentDate = substr($target['ResponseData'], 3, 4).'-'.substr($target['ResponseData'], 7, 2).'-'.substr($target['ResponseData'], 9, 2);
                    $prm = array(
                        ':pi_receipt_amount'   => $paymentAmount,                             // 入金額（バーコード上の請求額）
                        ':pi_order_seq'        => $orderData['OrderSeq'],                     // 注文Seq
                        ':pi_receipt_date'     => $custPaymentDate,                           // 入金日
                        ':pi_receipt_class'    => $payWayType,                                // 入金方法
                        ':pi_branch_bank_id'   => null,                                       // 銀行支店ID       // 2015/10/19 Y.Suzuki 会計対応 Mod
                        ':pi_receipt_agent_id' => 6,                                          // 収納代行ID(2:@ﾍﾟｲﾒﾝﾄ)
                        ':pi_deposit_date'     => null,                                       // 口座入金日       // 2015/12/09 Y.Suzuki 会計対応 Mod
                        ':pi_user_id'          => $userId,
                        ':pi_receipt_note'     => null
                    );
                    try {
                        $ri = $stm->execute($prm);

                        // SQL実行例外なしもエラー戻り値の時は例外をｽﾛｰ
                        $retval = $this->dbAdapter->query($getretvalsql)->execute(null)->current();
                        if ($retval['po_ret_sts'] != 0) {
                            throw new \Exception($retval['po_ret_msg']);
                        }
                    }
                    catch(\Exception $e) { throw $e; }

                    // 未印刷の請求書印刷予約データを削除
                    $mdlch->deleteReserved($orderData['OrderSeq'], $userId);

                    // 立替・売上管理データ更新
                    $orderData = $mdlo->find($oseq)->current();

                    // 入金済み正常ｸﾛｰｽﾞの場合、立替対象とする。
                    if ($orderData['DataStatus'] == 91 && $orderData['CloseReason'] == 1) {
                        // 既に[立替条件クリアフラグ]が[1：条件をクリアしている]か？の取得
                        $isAlreadyClearCondition = $mdlps->IsAlreadyClearCondition($orderData['OrderSeq']);

                        $mdlps->clearConditionForCharge($orderData['OrderSeq'], 1, $userId);

                        if (!$isAlreadyClearCondition) {
                            $row_pas = $this->dbAdapter->query(" SELECT Seq FROM T_PayingAndSales WHERE OrderSeq = :OrderSeq "
                            )->execute(array(':OrderSeq' => $orderData['OrderSeq']))->current();

                            // 入金により立替条件クリアフラグが１化されるとき => '2:入金'として更新(顧客入金日をセットする)
                            $mdlapas->saveUpdate(array('ATUriType' => 2, 'ATUriDay' => date('Ymd', strtotime($custPaymentDate))), $row_pas['Seq']);
                        }
                    }

                    // 印紙代発生の有無
                    // 2014.2.21　印紙税発生の条件を金額依存では無くバーコードに含まれるフラッグに依存するように変更
                    //if ($atPaymentConfig->enable_stamp_fee && $data->PaymentAmount >= 31500 && $data->PayWayType == 1)
                    if (substr($target['ResponseData'], 51, 1) == 1) {
						// 支払方法区分２取得
						$sql = "SELECT cd.Class2 as Class2
								FROM T_ReceiptControl as rc
								LEFT JOIN M_Code as cd ON cd.CodeId = 198 AND cd.KeyCode = rc.ReceiptClass
								WHERE rc.OrderSeq = :OrderSeq
								ORDER BY rc.ReceiptSeq DESC LIMIT 1;";
						$Class2 = $this->dbAdapter->query($sql)->execute(array(':OrderSeq' => $orderData['OrderSeq']))->current()['Class2'];
						//支払方法区分2が0:印紙代対象
						if($Class2==0){
                            $stampFee['OrderSeq']       = $orderData['OrderSeq'];
                            $stampFee['DecisionDate']   = date('Y-m-d');
                            $stampFee['StampFee']       = 200;
                            $stampFee['ClearFlg']       = 0;
                            $stampFee['CancelFlg']      = 0;
                            $mdlstmp->saveNew($stampFee);
                        }
                    }

                    // 作成した入金管理Seqを取得する。（1注文に対する入金は複数存在するので、注文に紐づく入金のMAX値を取得）
                    $sql = "SELECT MAX(ReceiptSeq) AS ReceiptSeq FROM T_ReceiptControl WHERE OrderSeq = :OrderSeq GROUP BY OrderSeq";
                    $rcptSeq = $this->dbAdapter->query($sql)->execute(array(':OrderSeq' => $orderData['OrderSeq']))->current()['ReceiptSeq'];

                    if ($datastatusBeforeReceipt != 91) {// [91：クローズ]からの入金はメール対象から除外
                        // NOTE : 本箇所でのﾒｰﾙ送信は行わない(ﾊﾞｯﾁによるｽｹｼﾞｭｰﾘﾝｸﾞ送信)
                        // T_ReceiptControl.MailFlgの[0：未送信(送信対象)]化
                        $this->dbAdapter->query(" UPDATE T_ReceiptControl SET MailFlg = 0 WHERE ReceiptSeq = :ReceiptSeq ")->execute(array(':ReceiptSeq' => $rcptSeq));
                    }
                    // 注文履歴へ登録
                    $history->InsOrderHistory($orderData['OrderSeq'], 61, $userId);

                    // 2016/01/05 Y.Suzuki Add 更新後のデータを取得 Stt
                    // 入金取消前立替クリアフラグ、入金取消前立替クリア日
                    $sql = "SELECT ClearConditionForCharge, ClearConditionDate FROM T_PayingAndSales WHERE OrderSeq = :OrderSeq";
                    $ri = $this->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq));
                    $clearConditionForCharge = $ri->current()['ClearConditionForCharge'];
                    $clearConditionDate = $ri->current()['ClearConditionDate'];
                    // 入金取消前立替処理－ステータス、入金取消前配送－着荷確認
                    $sql = "SELECT Cnl_Status, Deli_ConfirmArrivalFlg FROM T_Order WHERE OrderSeq = :OrderSeq";
                    $ri = $this->dbAdapter->query($sql)->execute(array(':OrderSeq' => $orderData['OrderSeq']));
                    $cnlStatus = $ri->current()['Cnl_Status'];
                    $deliConfirmArrivalFlg = $ri->current()['Deli_ConfirmArrivalFlg'];
                    // 2016/01/05 Y.Suzuki Add 更新後のデータを取得 End

                    $atdata = array(
                        'ReceiptSeq' => $rcptSeq,
                        'AccountNumber' => null,
                        'ClassDetails' => null,
                        'BankFlg' => 1,     // 銀行入金区分：1（入金取り込みなので 1 固定）
                        // 2016/01/05 Y.Suzuki Add 会計対応_入金取消関連（INSERT項目追加） Stt
                        'Before_ClearConditionForCharge' => $clearConditionForCharge,   // 入金取消前立替クリアフラグ
                        'Before_ClearConditionDate' => $clearConditionDate,             // 入金取消前立替クリア日
                        'Before_Cnl_Status' => $cnlStatus,                              // 入金取消前立替処理－ステータス
                        'Before_Deli_ConfirmArrivalFlg' => $deliConfirmArrivalFlg,      // 入金取消前配送－着荷確認
                        // 2016/01/05 Y.Suzuki Add 会計対応_入金取消関連（INSERT項目追加） End
                        'KeyInfo' => substr($target['ResponseData'], 15, 44),            // 確報取込対応(キー情報)
                        'SokuhoRegistDate' => date('Y-m-d'),    // 確報取込対応(速報データ取込日)
                        'KakuhoRegistDate' => null,             // 確報取込対応(確報データ取込日)
                    );
                    $mdlatrc->saveNew($atdata);
                }
                $this->saveReceipt(1, null, $oseq, $orderRow['EnterpriseId'], $paymentAmount, 0, $importDate, $userId, 1, $target['Seq']);
            } elseif ($dataKind == 2) {
                // 確報データで入金予定日を更新する
                $transderCommitDate = substr($target['ResponseData'], 77, 4).'-'.substr($target['ResponseData'], 81, 2).'-'.substr($target['ResponseData'], 83, 2);
                $sql = "UPDATE T_ReceiptControl SET DepositDate=:DepositDate WHERE OrderSeq = :OrderSeq AND DepositDate IS NULL ";
                $ri = $this->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq,':DepositDate' => $transderCommitDate));
                $this->saveReceipt(1, null, $oseq, $orderRow['EnterpriseId'], $paymentAmount, 0, $importDate, $userId, 1, $target['Seq']);
            } else {
                // アラート作成
                $mdlsa = new TableStagnationAlert($this->dbAdapter);
                $udata = array(
                    'AlertClass' => 3,                                      // 停滞アラート区分(3：入金取消)※ 仮の区分
                    'AlertSign' => 1,                                       // アラートサイン(1：アラート)
                    'OrderSeq' => $orderData['OrderSeq'],                   // 注文SEQ
                    'StagnationDays' => NULL,                               // 停滞期間日数
                    'EnterpriseId' => $orderData['EnterpriseId'],           // 加盟店ID
                    'AlertJudgDate' => date('Y-m-d H:i:s'),                 // アラート抽出日時
                    'RegistId' => $userId,                                  // 登録者
                    'UpdateId' => $userId,                                  // 更新者
                    'ValidFlg' => 1,                                        // 有効フラグ
                );
                // 新規登録
                $mdlsa->saveNew($udata);
                $this->saveReceipt(1, sprintf('速報取消：指定された注文ID = %s', $orderId), $oseq, $orderRow['EnterpriseId'], $paymentAmount, 1, $importDate, $userId, 1, $target['Seq']);
            }
        }
        $this->dbAdapter->getDriver()->getConnection()->commit();
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

    private function saveReceipt($ProcessClass, $Note, $OrderSeq, $EnterpriseId, $PaymentAmount, $ErrorFlg, $ImportDate, $UpdateId, $ValidFlg, $Seq)
    {
        $sql = " UPDATE T_MufjReceipt SET ProcessClass=:ProcessClass,Note=:Note,OrderSeq=:OrderSeq,EnterpriseId=:EnterpriseId,PaymentAmount=:PaymentAmount,ErrorFlg=:ErrorFlg,ImportDate=:ImportDate,UpdateDate=:UpdateDate,UpdateId=:UpdateId,ValidFlg=:ValidFlg WHERE Seq=:Seq";
        $updData = array();
        $updData[':ProcessClass'] = $ProcessClass;
        $updData[':Note'] = $Note;
        $updData[':OrderSeq'] = $OrderSeq;
        $updData[':EnterpriseId'] = $EnterpriseId;
        $updData[':PaymentAmount'] = $PaymentAmount;
        $updData[':ErrorFlg'] = $ErrorFlg;
        $updData[':ImportDate'] = $ImportDate;
        $updData[':UpdateDate'] = date('Y-m-d H:i:s');
        $updData[':UpdateId'] = $UpdateId;
        $updData[':ValidFlg'] = $ValidFlg;
        $updData[':Seq'] = $Seq;

        return $this->dbAdapter->query($sql)->execute($updData);
    }

    /**
     * 注文情報 備考を更新する
     *
     * @param string $odrSeq 注文Seq
     * @param string $usrId ユーザーID
     */
    private function updateIncreNote($odrSeq, $usrId)
    {
        $mdlo = new TableOrder($this->dbAdapter);
        $obj = new \models\Table\TableUser($this->dbAdapter);
        $userName = $obj->getUserName($usrId);
        $odrData = $mdlo->find($odrSeq)->current();

        $uOrder['Incre_Note'] = $odrData['Incre_Note'] . "\n" . date('Y-m-d') . "@入金有/" . $userName;

        $mdlo->saveUpdateParts($uOrder, $odrSeq);
        return;
    }
}

Application::getInstance()->run();
