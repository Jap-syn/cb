<?php
namespace models\Logic;

use Zend\Db\Adapter\Adapter;
use Coral\Coral\Mail\CoralMail;
use Coral\Coral\History\CoralHistoryOrder;

/**
 * 入金処理ロジック
 */
class LogicReceiptOfMoney {
	/** 入金方法：コンビニ @var int */
	const RCTPMETHOD_CONVENI = 1;

	/** 入金方法：郵便局 @var int */
	const RCPTMETHOD_POSTOFFICE = 2;

	/** 入金方法：銀行 @var int */
	const RCPTMETHOD_BANK = 3;

	/**
	 * アダプタ
	 *
	 * @var Adapter
	 */
	protected $_adapter = null;

	/**
	 * 入金処理メソッド内でトランザクションを使用するかのフラグ
	 *
	 * @access protected
	 * @var boolean
	 */
	protected $_useTransaction;

	/**
	 * SMTPサーバ情報
	 *
	 * @access protected
	 * @var string
	 */
	protected $_smtp;

	/**
	 * メール送信で使用するcharset
	 *
	 * @access protected
	 * @var string
	 */
	protected $_mail_charset = 'ISO-2022-JP';

	/**
	 * コンストラクタ
	 *
	 * @param Adapter $adapter アダプタ
	 * @param null | string $smtp SMTPサーバ
	 * @param null | string $charset メール文字コード
	 */
	function __construct(Adapter $adapter, $smtp = null, $charset = null) {
        $this->_adapter         = $adapter;
        $this->_smtp            = $smtp;
        $this->_mail_charset    = $charset;
        $this->_useTransaction  = true; // デフォルトでは内部トランザクションを使用
	}

	/**
	 * 入金処理内部でトランザクションを使用するかを設定する
	 *
	 * @param boolean $useTransaction 処理内部でトランザクションを使用するかのフラグ
	 * @return LogicReceiptOfMoney このインスタンス
	 */
	public function setUseTransaction($useTransaction) {
	    $this->_useTransaction = $useTransaction ? true : false;
	    return $this;
	}

	/**
	 * メール送信を実行するためのCoralMailを生成する
	 *
	 * @return CoralMail
	 */
	public function createCoralMail() {
	    return new CoralMail($this->_adapter, $this->_smtp, $this->_mail_charset);
	}

	/**
	 * 入金処理
	 * @param int $orderSeq 注文SEQ
	 * @param int $rcptMethod 入金方法（1：コンビニ、2：郵便局、3：銀行）
	 * @param string $rcptDate 入金日
	 * @param int $rcptAmount 入金額
	 * @param int $claimAmount 請求金額
	 * @param int $userId ユーザーID
	 * @param null | string $accountPaymentDate CB口座入金日。省略時はnull
	 * @see 呼び出し元でトランザクションの開始／終了（コミット・ロールバック）を行っているので、この処理の中では行わない！！！
	 */
	public function payment($orderSeq, $rcptMethod, $rcptDate, $rcptAmount, $claimAmount, $userId, $accountPaymentDate = null ) {
        $mdlo = new \models\Table\TableOrder($this->_adapter);
        unset($udata);

        $stm = $this->_adapter->query($this->getBaseP_ReceiptControl());

        // SQL実行結果取得用のSQL
        $getretvalsql = " SELECT @po_ret_sts AS po_ret_sts, @po_ret_errcd AS po_ret_errcd, @po_ret_sqlcd AS po_ret_sqlcd, @po_ret_msg AS po_ret_msg ";

        // 現時点で入金確認待ちなら処理する
        if ($mdlo->isReceiptReady($orderSeq)) {
            try {
                if ($rcptAmount > 0) {
                    // 2016/01/28 Y.Suzuki Mod JNB自動入金の場合は、入金管理通知ﾃｰﾌﾞﾙから情報を取得する Stt
                    // 注文Seqから銀行ｺｰﾄﾞと支店ｺｰﾄﾞをCONCATして取得
                    $sql = "";
                    $sql .= " SELECT CONCAT(jnb.BankCode, ja.BranchCode) AS BranchBankId ";
                    $sql .= " FROM   T_JnbPaymentNotification nf ";
                    $sql .= "        INNER JOIN T_JnbAccount ja ON (ja.AccountSeq = nf.AccountSeq) ";
                    $sql .= "        INNER JOIN T_JnbAccountGroup jag ON (jag.AccountGroupId = ja.AccountGroupId) ";
                    $sql .= "        INNER JOIN T_Jnb jnb ON (jnb.JnbId = jag.JnbId) ";
                    $sql .= " WHERE  nf.OrderSeq = :OrderSeq ";
                    $branchBankId = $this->_adapter->query($sql)->execute(array(':OrderSeq' => $orderSeq))->current()['BranchBankId'];

//                     // 2015/12/09 Y.Suzuki Add 会計対応 Stt
//                     // 注文SeqからOEMIDを取得し、コードマスタ（識別ID：153）と紐付けて銀行支店IDを取得する。
//                     // OEMID抜き出し
//                     $sql = "SELECT IFNULL(OemId, 0) AS OemId FROM T_Order WHERE OrderSeq = :OrderSeq";
//                     $oemId = $this->_adapter->query($sql)->execute(array(':OrderSeq' => $orderSeq))->current()['OemId'];
//                     $sql = "SELECT Class1 FROM M_Code WHERE CodeId = 153 AND KeyCode = :KeyCode";
//                     $branchBankId = $this->_adapter->query($sql)->execute(array(':KeyCode' => $oemId))->current()['Class1'];
//                     // 2015/12/09 Y.Suzuki Add 会計対応 End
                    // 2016/01/28 Y.Suzuki Mod JNB自動入金の場合は、入金管理通知ﾃｰﾌﾞﾙから情報を取得する End

                    // 入金差額の算出
                    $sagaku = $claimAmount - $rcptAmount;

                    // 入金前データステータスの取得
                    $datastatusBeforeReceipt = (int)$this->_adapter->query(" SELECT DataStatus FROM T_Order WHERE OrderSeq = :OrderSeq "
                        )->execute(array(':OrderSeq' => (int)$orderSeq))->current()['DataStatus'];

                    // ここで入金処理のプロシージャをコール
                    $prm = array(
                            ':pi_receipt_amount'    => $rcptAmount,                 // 入金額
                            ':pi_order_seq'         => $orderSeq,                   // 注文Seq
                            ':pi_receipt_date'      => $rcptDate,                   // 入金日
                            ':pi_receipt_class'     => $rcptMethod,                 // 入金形態
                            ':pi_branch_bank_id'    => $branchBankId,               // 銀行支店ID       // 2015/12/09 Y.Suzuki 会計対応 Mod
                            ':pi_receipt_agent_id'  => null,                        // 収納代行ID（ジャパネは収納代行ではないのでココはNULLでよし）
                            ':pi_deposit_date'      => $accountPaymentDate,         // 口座入金日
                            ':pi_user_id'           => $userId,
                            ':pi_receipt_note'     => null,
                    );

                    try {
                        $ri = $stm->execute($prm);

                        // SQL実行例外なしもエラー戻り値の時は例外をｽﾛｰ
                        $retval = $this->_adapter->query($getretvalsql)->execute(null)->current();
                        if ($retval['po_ret_sts'] != 0) {
                            throw new \Exception($retval['po_ret_msg']);
                        }

                    }
                    catch(\Exception $e) { throw $e; }

                    // 未印刷の請求書印刷予約データを削除
                    $mdlch = new \models\Table\TableClaimHistory($this->_adapter);
                    $mdlch->deleteReserved($orderSeq, $userId);

                    // 立替・売上管理データ更新
                    $mdlps = new \models\Table\TablePayingAndSales($this->_adapter);
                    $mdld = new \models\View\ViewDelivery($this->_adapter);
                    $deli = $mdld->findByOrderSeq($orderSeq)->current();

                    // 補償外案件を立替案件にするために、DB上の注文情報を取得する
                    $targetOrder = $mdlo->find($orderSeq)->current();

                    // 正常入金ｸﾛｰｽﾞの場合、立替対象とする
                    if ($targetOrder['DataStatus'] == 91 && $targetOrder['CloseReason'] == 1) {

                        $mdlapas = new \models\Table\ATablePayingAndSales($this->_adapter);
                        // 既に[立替条件クリアフラグ]が[1：条件をクリアしている]か？の取得
                        $isAlreadyClearCondition = $mdlps->IsAlreadyClearCondition($orderSeq);

                        $mdlps->clearConditionForCharge($orderSeq, 1, $userId);

                        if (!$isAlreadyClearCondition) {
                            $row_pas = $this->_adapter->query(" SELECT Seq FROM T_PayingAndSales WHERE OrderSeq = :OrderSeq "
                                )->execute(array(':OrderSeq' => $orderSeq))->current();

                            // 入金により立替条件クリアフラグが１化されるとき => '2:入金'として更新(顧客入金日をセットする)
                            $mdlapas->saveUpdate(array('ATUriType' => 2, 'ATUriDay' => date('Ymd', strtotime($rcptDate))), $row_pas['Seq']);
                        }
                    }

                    // 作成した入金管理Seqを取得する。（1注文に対する入金は複数のため、注文に対する入金情報のMAX値を取得する）
                    $sql = "SELECT MAX(ReceiptSeq) AS ReceiptSeq FROM T_ReceiptControl WHERE OrderSeq = :OrderSeq GROUP BY OrderSeq";
                    $rcptSeq = $this->_adapter->query($sql)->execute(array(':OrderSeq' => $orderSeq))->current()['ReceiptSeq'];

                    // AT_ReceiptControl登録
                    $mdl_atrc = new \models\Table\ATableReceiptControl($this->_adapter);
                    $mdl_atrc->saveNew(array('ReceiptSeq' => $rcptSeq, 'BankFlg' => 1));

                    // 2015/12/21 Y.Suzuki Add JNB入金時に注文履歴登録処理が抜けていたので付加 Stt
                    // 注文履歴へ登録
                    $history = new CoralHistoryOrder($this->_adapter);
                    $history->InsOrderHistory($orderSeq, 61, $userId);
                    // 2015/12/21 Y.Suzuki Add JNB入金時に注文履歴登録処理が抜けていたので付加 End

                    if ($datastatusBeforeReceipt != 91 && $targetOrder['DataStatus'] == 91) {// [91：クローズ]からの入金はメール対象から除外
                    try {
                        // 入金確認メール送信
                        $this->createCoralMail()->SendRcptConfirmMail($rcptSeq, $userId);
                    } catch(\Exception $e) {
                    }
                    }
                } else	{
                    // 入金額が不正なので、処理しない。
                    throw new \Exception("入金額不正");
                }

            }
            catch(\Exception $e) {
                // 発生した例外はそのままスロー
                throw $e;
            }
        }
        else {
            throw new \Exception("入金待ちではない");
        }
    }

    /**
     * 入金処理(SMBCバーチャル口座専用)
     * @param int $orderSeq 注文SEQ
     * @param int $rcptMethod 入金方法（1：コンビニ、2：郵便局、3：銀行）
     * @param string $rcptDate 入金日
     * @param int $rcptAmount 入金額
     * @param int $claimAmount 請求金額
     * @param int $userId ユーザーID
     * @param null | string $accountPaymentDate CB口座入金日。省略時はnull
     * @see 呼び出し元でトランザクションの開始／終了（コミット・ロールバック）を行っているので、この処理の中では行わない！！！
     */
    public function paymentSmbcpa($orderSeq, $rcptMethod, $rcptDate, $rcptAmount, $claimAmount, $userId, $accountPaymentDate = null ) {
        $mdlo = new \models\Table\TableOrder($this->_adapter);
        unset($udata);

        $stm = $this->_adapter->query($this->getBaseP_ReceiptControl());

        // SQL実行結果取得用のSQL
        $getretvalsql = " SELECT @po_ret_sts AS po_ret_sts, @po_ret_errcd AS po_ret_errcd, @po_ret_sqlcd AS po_ret_sqlcd, @po_ret_msg AS po_ret_msg ";

        // 現時点で入金確認待ちなら処理する
        if ($mdlo->isReceiptReady($orderSeq)) {
            try {
                if ($rcptAmount > 0) {
                    // 注文Seqから銀行ｺｰﾄﾞと支店ｺｰﾄﾞをCONCATして取得
                    $sql = "";
                    $sql .= " SELECT CONCAT(smbcpa.BankCode, sa.BranchCode) AS BranchBankId ";
                    $sql .= " FROM   T_SmbcpaPaymentNotification nf ";
                    $sql .= "        INNER JOIN T_SmbcpaAccount sa ON (sa.AccountSeq = nf.AccountSeq) ";
                    $sql .= "        INNER JOIN T_SmbcpaAccountGroup sag ON (sag.AccountGroupId = sa.AccountGroupId) ";
                    $sql .= "        INNER JOIN T_Smbcpa smbcpa ON (smbcpa.SmbcpaId = sag.SmbcpaId) ";
                    $sql .= " WHERE  nf.OrderSeq = :OrderSeq ";
                    $branchBankId = $this->_adapter->query($sql)->execute(array(':OrderSeq' => $orderSeq))->current()['BranchBankId'];

                    // 入金差額の算出
                    $sagaku = $claimAmount - $rcptAmount;

                    // 入金前データステータスの取得
                    $datastatusBeforeReceipt = (int)$this->_adapter->query(" SELECT DataStatus FROM T_Order WHERE OrderSeq = :OrderSeq "
                        )->execute(array(':OrderSeq' => (int)$orderSeq))->current()['DataStatus'];

                    // ここで入金処理のプロシージャをコール
                    $prm = array(
                            ':pi_receipt_amount'    => $rcptAmount,                 // 入金額
                            ':pi_order_seq'         => $orderSeq,                   // 注文Seq
                            ':pi_receipt_date'      => $rcptDate,                   // 入金日
                            ':pi_receipt_class'     => $rcptMethod,                 // 入金形態
                            ':pi_branch_bank_id'    => $branchBankId,               // 銀行支店ID
                            ':pi_receipt_agent_id'  => null,                        // 収納代行ID
                            ':pi_deposit_date'      => $accountPaymentDate,         // 口座入金日
                            ':pi_user_id'           => $userId,
                            ':pi_receipt_note'     => null,
                    );

                    try {
                        $ri = $stm->execute($prm);

                        // SQL実行例外なしもエラー戻り値の時は例外をｽﾛｰ
                        $retval = $this->_adapter->query($getretvalsql)->execute(null)->current();
                        if ($retval['po_ret_sts'] != 0) {
                            throw new \Exception($retval['po_ret_msg']);
                        }

                    }
                    catch(\Exception $e) { throw $e; }

                    // 未印刷の請求書印刷予約データを削除
                    $mdlch = new \models\Table\TableClaimHistory($this->_adapter);
                    $mdlch->deleteReserved($orderSeq, $userId);

                    // 立替・売上管理データ更新
                    $mdlps = new \models\Table\TablePayingAndSales($this->_adapter);
                    $mdld = new \models\View\ViewDelivery($this->_adapter);
                    $deli = $mdld->findByOrderSeq($orderSeq)->current();

                    // 補償外案件を立替案件にするために、DB上の注文情報を取得する
                    $targetOrder = $mdlo->find($orderSeq)->current();

                    // 正常入金ｸﾛｰｽﾞの場合、立替対象とする
                    if ($targetOrder['DataStatus'] == 91 && $targetOrder['CloseReason'] == 1) {

                        $mdlapas = new \models\Table\ATablePayingAndSales($this->_adapter);
                        // 既に[立替条件クリアフラグ]が[1：条件をクリアしている]か？の取得
                        $isAlreadyClearCondition = $mdlps->IsAlreadyClearCondition($orderSeq);

                        $mdlps->clearConditionForCharge($orderSeq, 1, $userId);

                        if (!$isAlreadyClearCondition) {
                            $row_pas = $this->_adapter->query(" SELECT Seq FROM T_PayingAndSales WHERE OrderSeq = :OrderSeq "
                                )->execute(array(':OrderSeq' => $orderSeq))->current();

                            // 入金により立替条件クリアフラグが１化されるとき => '2:入金'として更新(顧客入金日をセットする)
                            $mdlapas->saveUpdate(array('ATUriType' => 2, 'ATUriDay' => date('Ymd', strtotime($rcptDate))), $row_pas['Seq']);
                        }
                    }

                    // 作成した入金管理Seqを取得する。（1注文に対する入金は複数のため、注文に対する入金情報のMAX値を取得する）
                    $sql = "SELECT MAX(ReceiptSeq) AS ReceiptSeq FROM T_ReceiptControl WHERE OrderSeq = :OrderSeq GROUP BY OrderSeq";
                    $rcptSeq = $this->_adapter->query($sql)->execute(array(':OrderSeq' => $orderSeq))->current()['ReceiptSeq'];

                    // AT_ReceiptControl登録
                    $mdl_atrc = new \models\Table\ATableReceiptControl($this->_adapter);
                    $mdl_atrc->saveNew(array('ReceiptSeq' => $rcptSeq, 'BankFlg' => 1));

                    // 注文履歴へ登録
                    $history = new CoralHistoryOrder($this->_adapter);
                    $history->InsOrderHistory($orderSeq, 61, $userId);

                    if ($datastatusBeforeReceipt != 91 && $targetOrder['DataStatus'] == 91) {// [91：クローズ]からの入金はメール対象から除外
                    try {
                        // 入金確認メール送信
                        $this->createCoralMail()->SendRcptConfirmMail($rcptSeq, $userId);
                    } catch(\Exception $e) {
                    }
                    }
                } else {
                    // 入金額が不正なので、処理しない。
                    throw new \Exception("入金額不正");
                }

            }
            catch(\Exception $e) {
                // 発生した例外はそのままスロー
                throw $e;
            }
        }
        else {
            throw new \Exception("入金待ちではない");
        }
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
,   :pi_receipt_note
,   @po_ret_sts
,   @po_ret_errcd
,   @po_ret_sqlcd
,   @po_ret_msg
    )
EOQ;
    }
}

