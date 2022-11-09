<?php
namespace models\Logic;

use Zend\Db\Adapter\Adapter;
use Zend\Json\Json;
use Coral\Base\BaseGeneralUtils;
use Coral\Coral\Mail\CoralMail;
use models\Table\TableClaimHistory;
use models\Table\TableClaimControl;
use models\Table\TableManagementCustomer;
use models\Table\TableMypageCustomer;
use models\Table\TableMypageOrder;
use models\Table\TableMypageToBackIF;
use models\Table\TableToBackMypageIF;
use models\Table\TableSbpsReceiptControl;
use models\Table\TableReceiptControl;
use models\Table\TableReceiptIssueHistory;
use models\Table\TableCode;
use models\Table\TableCreditPayment;
use models\Table\TableUser;
use models\View\MypageViewCustomer;
use models\View\MypageViewOrder;
use models\Table\TableSystemProperty;
use models\Table\ATableOrder;
use models\Table\ATablePayingAndSales;
use models\Table\TablePayingAndSales;
use models\Table\TableSite;
use models\View\ViewOrderCustomer;


/**
 * マイページクラス
 */
class LogicMypage
{
	/**
	 * アダプタ
	 *
	 * @var Adapter
	 */
	protected $_adapter = null;

	/**
	 * アダプタ
	 *
	 * @var Adapter
	 */
	protected $_adapterMypage = null;

	/**
	 * SMTP
	 *
	 * @var Adapter
	 */
	protected $_smtp = null;

	/**
	 * コンストラクタ
	 *
	 * @param Adapter $adapter アダプタ
	 */
	public function __construct($adapter, $adapterMypage, $smtp = null)
	{
	    $this->_adapter = $adapter;
	    $this->_adapterMypage = $adapterMypage;
	    $this->_smtp = $smtp;
	}

	/**
	 * マイページ連携バッチ
	 */
	public function mypagetoback(){
        $mdltbi = new TableMypageToBackIF($this->_adapterMypage);
        $mdlo = new MypageViewOrder($this->_adapterMypage);
        $mdlc = new TableMypageCustomer($this->_adapterMypage);
        $mdlch = new TableClaimHistory($this->_adapter);
        $mdlcc = new TableClaimControl($this->_adapter);
        $mdlmc = new TableManagementCustomer($this->_adapter);
        $mdlu = new TableUser($this->_adapter);

        try {
            $this->_adapter->getDriver()->getConnection()->beginTransaction();
            $this->_adapterMypage->getDriver()->getConnection()->beginTransaction();

            $userId = $mdlu->getUserId(99, 1);

            // マイページから基幹への反映

            // ＩＦテーブルからデータを取得
            $datas = $mdltbi->findMypageToBackIF( array('Status' => 0));
            foreach ($datas as $data){
                if ($data['IFClass'] == 1) {
                    // 連携区分＝1（請求書発行指示）の場合

                    // チェック処理
                    $dataOrder = $mdlo->find($data['OrderSeq'])->current();

                    if ($dataOrder['Cnl_Status'] != 0)
                    {
                        $mdltbi->saveUpdate(array('Status' => 9, 'Reason' => '注文キャンセル済み'), $data['Seq']);
                        continue;
                    }

                    if ($dataOrder['DataStatus'] != 51 && $dataOrder['DataStatus'] != 61)
                    {
                        $mdltbi->saveUpdate(array('Status' => 9, 'Reason' => '更新可能なデータステータスではない'), $data['Seq']);
                        continue;
                    }

                    // 基幹のデータ反映
                    // 請求履歴
                    $mdlch->deleteReserved($data['OrderSeq'], $userId);
                    // 請求管理
                    $ifData = Json::decode($data['IFData'], Json::TYPE_ARRAY);
                    $mdlcc->updateMypageReissue(
                        array(
                                'MypageReissueClass' => $ifData['ReissueClass'],
                                'MypageReissueRequestDate' => $ifData['ReissueRequestDate'],
                                'MypageReissueReClaimFee' => $ifData['ReissueReClaimFee'],
                                'UpdateId' => $userId,
                        ),
                        $data['OrderSeq']
                    );
                } elseif ($data['IFClass'] == 2) {
                    // 連携区分＝2（管理顧客作成指示）の場合

                    $ifData = Json::decode($data['IFData'], Json::TYPE_ARRAY);
                    $customerId = $ifData['CustomerId'];

                    // 管理顧客番号取得
                    $manCustId = $this->getManCustId($customerId);

                    if ($manCustId != null) {
                        // 管理顧客番号が取得出来た場合
                        $mdlc->saveUpdate(array('ManCustId' => $manCustId), $customerId);
                    } else {
                        // 管理顧客番号が取得出来なかった場合
                        $dataMypageCustomer = $mdlc->find($customerId)->current();
                        $mdlmc->saveNew(
                            array(
                                    'GoodFlg'              => 0,
                                    'BlackFlg'             => 0,
                                    'ClaimerFlg'           => 0,
                                    'RemindStopFlg'        => 0,
                                    'IdentityDocumentFlg'  => $dataMypageCustomer['IdentityDocumentClass'],
                                    'NameKj'               => $dataMypageCustomer['NameSeiKj']. $dataMypageCustomer['NameMeiKj'],
                                    'NameKn'               => $dataMypageCustomer['NameSeiKn']. $dataMypageCustomer['NameMeiKn'],
                                    'PostalCode'           => $dataMypageCustomer['PostalCode'],
                                    'PrefectureCode'       => $dataMypageCustomer['PrefectureCode'],
                                    'PrefectureName'       => $dataMypageCustomer['PrefectureName'],
                                    'UnitingAddress'       => $dataMypageCustomer['UnitingAddress'],
                                    'Phone'                => $dataMypageCustomer['Phone'],
                                    'MailAddress'          => $dataMypageCustomer['MailAddress'],
                                    'Note'                 => "マイページからの登録\n電話番号(携帯)：". $dataMypageCustomer['MobilePhone'],
                                    'RegNameKj'            => $dataMypageCustomer['RegNameKj'],
                                    'RegUnitingAddress'    => $dataMypageCustomer['RegUnitingAddress'],
                                    'RegPhone'             => $dataMypageCustomer['RegPhone'],
                                    'SearchNameKj'         => mb_ereg_replace( '[ 　\r\n\t\v]', '', strlen($dataMypageCustomer['NameSeiKj']. $dataMypageCustomer['NameMeiKj']) ? $dataMypageCustomer['NameSeiKj']. $dataMypageCustomer['NameMeiKj'] : '' ),
                                    'SearchNameKn'         => mb_ereg_replace( '[ 　\r\n\t\v]', '', strlen($dataMypageCustomer['NameSeiKn']. $dataMypageCustomer['NameMeiKn']) ? $dataMypageCustomer['NameSeiKn']. $dataMypageCustomer['NameMeiKn'] : '' ),
                                    'SearchPhone'          => mb_ereg_replace( '[^0-9]', '', BaseGeneralUtils::convertWideToNarrow( strlen($dataMypageCustomer['Phone']) ? $dataMypageCustomer['Phone'] : '' ) ),
                                    'SearchUnitingAddress' => mb_ereg_replace( '[ 　\r\n\t\v]', '', strlen($dataMypageCustomer['UnitingAddress']) ? $dataMypageCustomer['UnitingAddress'] : '' ),
                                    'IluCustomerId'        => null,
                                    'IluCustomerListFlg'   => 0,
                                    'RegistId'             => $userId,
                                    'UpdateId'             => $userId,
                                    'ValidFlg'             => 1,
                            )
                        );
                    }
                } elseif ($data['IFClass'] == 3) {
                    // 連携区分＝3（身分証アップロードフラグ更新指示）の場合

                    $ifData = Json::decode($data['IFData'], Json::TYPE_ARRAY);
                    $customerId = $ifData['CustomerId'];

                    // 管理顧客番号をマイページ情報から取得
                    $mpc = $mdlc->find($customerId)->current();
                    if (!empty($mpc['ManCustId'])) {
                        // マイページ顧客と管理顧客が紐付けできる場合のみ更新
                        $mdlmc->saveUpdate(
                            array(
                                'IdentityDocumentFlg' => $mpc['IdentityDocumentClass']
                            )
                        , $mpc['ManCustId']);
                    }
                } elseif ($data['IFClass'] == 4) {
                    continue;

                }

                // ＩＦテーブル更新
                $mdltbi->saveUpdate( array('Status' => 1), $data['Seq']);
            }

            // 基幹からマイページへの反映
            // 必要になったら考慮する

            $this->_adapter->getDriver()->getConnection()->commit();
            $this->_adapterMypage->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            $this->_adapter->getDriver()->getConnection()->rollBack();
            $this->_adapterMypage->getDriver()->getConnection()->rollBack();
            throw $e;
        }
	}

    /**
     * マイページ連携バッチ（連携区分クレジット用）
     */
    public function mypageToBackCredit(){
        $mdltbi = new TableMypageToBackIF($this->_adapterMypage);
        $mdlao = new ATableOrder($this->_adapter);
        $mdlu = new TableUser($this->_adapter);
        $mdlpas = new TablePayingAndSales($this->_adapter);
        $mdlapas = new ATablePayingAndSales($this->_adapter);
        $mdls = new TableSite($this->_adapter);
        $coralMail = new CoralMail($this->_adapter, $this->_smtp);
        $mdlsp = new TableSystemProperty($this->_adapter);
        $mdlo = new MypageViewOrder($this->_adapterMypage);

        try {
            $this->_adapter->getDriver()->getConnection()->beginTransaction();
            $this->_adapterMypage->getDriver()->getConnection()->beginTransaction();

            //
            $userId = $mdlu->getUserId(99, 1);

            // マイページから基幹への反映

            // ＩＦテーブルからデータを取得
            $datas = $mdltbi->findMypageToBackIF( array('Status' => 0, 'IFClass' => '4'));
            
            // チェック処理
            $vdata = array();
            foreach ($datas as $data){
                // 処理しようとしている注文データが入金待ち、一部入金、入金済みクローズであるかのチェック
                $sql = "SELECT COUNT(*) AS cnt FROM T_Order WHERE (DataStatus IN (51, 61) OR (DataStatus = 91 AND CloseReason = 1)) AND Cnl_Status = 0 AND OrderSeq = :OrderSeq";
                $checkCount = $this->_adapter->query($sql)->execute(array(':OrderSeq' => $data['OrderSeq']))->current()['cnt'];
                //クレジットカードで支払った注文のチェック
                $sqlCredit = "SELECT COUNT(*) AS cnt FROM AT_Order WHERE ExtraPayType = 1 AND ExtraPayKey IS NOT NULL AND OrderSeq = :OrderSeq";
                $checkCountCredit = $this->_adapter->query($sqlCredit)->execute(array(':OrderSeq' => $data['OrderSeq']))->current()['cnt'];
                
                if ($checkCount <= 0 || $checkCountCredit >= 1) {
                    // エラーメッセージを入れておく。
                    $dataOrder = $mdlo->find($data['OrderSeq'])->current();
                    $vdata[] = $dataOrder['OrderId'];
                    if ($checkCountCredit <= 0)
                    {
                        $mdltbi->saveUpdate(array('Status' => 9, 'Reason' => '入金待ちではない'), $data['Seq']);
                    } else {
                        $mdltbi->saveUpdate(array('Status' => 9, 'Reason' => 'クレジットカードで支払った注文'), $data['Seq']);
                    }
                    continue;
                }
                
                // 基幹のデータ反映
                // 連携区分＝4（クレジット決済手続き）の場合
                $ifData = Json::decode($data['IFData'], Json::TYPE_ARRAY);

                // 注文Seq
                $orderSeq = $data['OrderSeq'];

                // 登録日
                $registDate = $data['RegistDate'];

                // 追加支払い区分
                
                if ($ifData['res_pay_method']) {
                    $extraPayType = '1'; // 届いてから払い
                    // 追加支払い鍵
                    $extraPayKey = $ifData['res_tracking_id'];
                    
                    // 追加支払い備考
                    $extraPayNote = array(
                        'res_pay_method'     => $ifData['res_pay_method'],
                        'res_result'         => $ifData['res_result'],
                        'res_tracking_id'    => $ifData['res_tracking_id'],
                        'res_sps_cust_no'    => $ifData['res_sps_cust_no'],
                        'res_sps_payment_no' => $ifData['res_sps_payment_no'],
                        'res_payinfo_key'    => $ifData['res_payinfo_key'],
                        'res_payment_date'   => $ifData['res_payment_date'],
                        'res_err_code'       => $ifData['res_err_code'],
                        'res_date'           => $ifData['res_date'],
                        'amount'           => $ifData['amount'],
                    );
                    $extraPayNote = Json::encode($extraPayNote);
                    $amount = $ifData['amount'];
                } else {
                    $extraPayType = '1'; // 届いてから払い
                    // 追加支払い鍵
                    $extraPayKey = $ifData['tracking_id'];
                    // 追加支払い備考
                    $extraPayNote = $data['IFData'];
                    // 決済した金額
                    $amount = $ifData['amount'];
                }
                
                // M_SbpsPaymentのSbpsPaymentIdを取得
                $payment_method=null;
                $sbps_sql = "";
                $sbps_sql .= "SELECT SbpsPaymentId";
                $sbps_sql .= " FROM M_SbpsPayment";
                if ($ifData['res_pay_method']) {
                    $payment_method = $ifData['res_pay_method'];
                    $sbps_sql .= " WHERE PaymentName = '". $payment_method ."'";
                    $sbpsPaymentId = $this->_adapter->query($sbps_sql)->execute()->current()['SbpsPaymentId'];
                } else {
                    if ($ifData['cardbrand_code'] == 'V' || $ifData['cardbrand_code']== 'M') {
                        $sbpsPaymentId = 1;
                    } elseif ($ifData['cardbrand_code'] == 'J' || $ifData['cardbrand_code'] == 'A') {
                        $sbpsPaymentId = 2;
                    } elseif ($ifData['cardbrand_code'] == 'D') {
                        $sbpsPaymentId = 3;
                    } else {
                        throw new \Exception('Not found PaymentId');
                    }
                }

                // サイト情報の取得＋α
                $sql = "";
                $sql .= "SELECT s.* ";
                $sql .= ", o.UseAmount ";
                $sql .= ", o.ClaimSendingClass ";
                $sql .= " FROM T_Order AS o";
                $sql .= " INNER JOIN T_SiteSbpsPayment AS s ON s.SiteId = o.SiteId";
                $sql .= " INNER JOIN M_SbpsPayment AS sbps ON sbps.SbpsPaymentId = s.PaymentId";
                $sql .= " WHERE o.OrderSeq = ". $orderSeq;
                $sql .= " AND sbps.FixedId = ". $sbpsPaymentId;
                $orderSite = $this->_adapter->query($sql)->execute()->current();

                // 注文_会計の更新
                $prm = array(
                        'ExtraPayType' => $extraPayType,
                        'ExtraPayKey'  => $extraPayKey,
                        'ExtraPayNote' => $extraPayNote,
                );
                $mdlao->saveUpdateExtraPay($prm, $orderSeq);

                // 立替・売上管理の取得
                $payingAndSales = $mdlpas->findPayingAndSales( array('OrderSeq' => $orderSeq) )->current();
                $payingAndSalesSeq = $payingAndSales['Seq'];
                $useAmount = 0;
                $appSettlementFeeRate = ( ( !isset($orderSite['SettlementFeeRate']) || empty($orderSite['SettlementFeeRate']) ) ? 0 : $orderSite['SettlementFeeRate'] );
                $settlementFee = floor( $orderSite['UseAmount'] * ($appSettlementFeeRate / 100) );
                $cSClaimFeeDK = ( ( !isset($orderSite['ClaimFeeDK']) || empty($orderSite['ClaimFeeDK']) ) ? 0 : $orderSite['ClaimFeeDK'] );
                $cSClaimFeeBS = ( ( !isset($orderSite['ClaimFeeBS']) || empty($orderSite['ClaimFeeBS']) ) ? 0 : $orderSite['ClaimFeeBS'] );
                $claimFee = ( $orderSite['ClaimSendingClass'] == '11' ? $cSClaimFeeDK : $cSClaimFeeBS );
                $claimFee = $mdlsp->getIncludeTaxAmount(date('Y-m-d'), $claimFee);
                $chargeAmount = ( -1 * ( $settlementFee + $claimFee ) );

                // 立替・売上管理の更新
                $prm = array(
                        'UseAmount'               => $useAmount,
                        'AppSettlementFeeRate'    => $appSettlementFeeRate,
                        'SettlementFee'           => $settlementFee,
                        'ClaimFee'                => $claimFee,
                        'ChargeAmount'            => $chargeAmount,
                        'ClearConditionForCharge' => 1,
                        'ClearConditionDate'      => date('Y-m-d'),
                        'UpdateDate'              => date('Y-m-d H:i:s'),
                        'UpdateId'                => $userId,
                );
                $mdlpas->saveUpdate($prm, $payingAndSalesSeq);

                // 立替・売上管理_会計の更新
                $prm = array(
                        'ATUriType' => 2,
                        'ATUriDay'  => date('Ymd', strtotime($registDate)),
                );
                $mdlapas->saveUpdate($prm, $payingAndSalesSeq);
                
                /****************************
                 *  Process payment by Todoitekara
                 ****************************/
                // A. Call P_ReceiptControl
                $this->_callP_ReceiptControl($orderSeq, $userId, $amount);
                
                // B. Delete bill data
                $this->_deleteBillData($orderSeq);
                
                // C. Regist Order History
                $history = new \Coral\Coral\History\CoralHistoryOrder( $this->_adapter );
                $history->InsOrderHistory( $orderSeq, 61, $userId );
                
                // D. Regist AT_ReceiptControl
                $this->_registAtReceiptControl($orderSeq);

                // メール送信処理
                try {
                    // クレジット決済完了メール送信
                    $coralMail->SendCreditBuyingCompleteMail($orderSeq, $userId, $registDate);

                    // メール送信に成功した場合のみ、送信フラグを更新する
                    $mdltbi->saveUpdate( array( 'MailFlg' => 1 ), $data['Seq'] );

                }
                catch(\Exception $e) {
                    // メール送信に成功した場合のみ、送信フラグを更新する
                    $mdltbi->saveUpdate( array( 'MailRetryCount' => ($data['MailRetryCount'] + 1) ), $data['Seq'] );

                }

                // ＩＦテーブル更新
                $mdltbi->saveUpdate( array('Status' => 1), $data['Seq']);

            }
            // アラートメール送信
            if (sizeof($vdata ) > 0) {
                $coralMail->MypagetobackextrapayBactchErrorMailToCb($vdata, $userId);
            }
            
            $this->_adapter->getDriver()->getConnection()->commit();
            $this->_adapterMypage->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            $this->_adapter->getDriver()->getConnection()->rollBack();
            $this->_adapterMypage->getDriver()->getConnection()->rollBack();
            throw $e;
        }
    }

/**
     * 入金関連処理ファンクションの基礎SQL取得。
     *
     * @return 入金関連処理ファンクションの基礎SQL
     */
    protected function _getBaseP_ReceiptControl() {
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

    /**
     * Call procedure P_ReceiptControl
     */
    protected function _callP_ReceiptControl($orderSeq, $userId, $amount) {
        // Get Receipt Amount        
//         $sql  = " SELECT ClaimedBalance FROM T_ClaimControl WHERE ClaimPattern = 1 AND OrderSeq = :OrderSeq ";
//         $rowCC = $this->_adapter->query($sql)->execute(array('OrderSeq' => $orderSeq))->current();

        // Get Payment Method        
        $sql  = " SELECT PaymentName FROM T_SbpsReceiptControl WHERE OrderSeq = :OrderSeq AND ValidFlg=1 ";
        $rowAO = $this->_adapterMypage->query($sql)->execute(array('OrderSeq' => $orderSeq))->current();
        $resPayMethod = $rowAO['PaymentName'];

        // Get Receipt Class 
        $sql  = " SELECT KeyCode FROM M_Code WHERE CodeId = 198 AND Class1 = :Class1 ";
        $rowCode = $this->_adapter->query($sql)->execute(array('Class1' => $resPayMethod))->current();

        // Call procedure P_ReceiptControl
        $prm = array(
            ':pi_receipt_amount'   => (int)$amount,
            ':pi_order_seq'        => $orderSeq,
            ':pi_receipt_date'     => date('Y-m-d'),
            ':pi_receipt_class'    => $rowCode['KeyCode'] ,
            ':pi_branch_bank_id'   => null,
            ':pi_receipt_agent_id' => null,
            ':pi_deposit_date'     => date('Y-m-d'),
            ':pi_user_id'          => $userId,
            ':pi_receipt_note'     => null,
        );
        $stm = $this->_adapter->query($this->_getBaseP_ReceiptControl());
        $stm->execute($prm);

        // Get output params
        $getretvalsql = " SELECT @po_ret_sts AS po_ret_sts, @po_ret_errcd AS po_ret_errcd, @po_ret_sqlcd AS po_ret_sqlcd, @po_ret_msg AS po_ret_msg ";
        $retval = $this->_adapter->query($getretvalsql)->execute(null)->current();
        if ($retval['po_ret_sts'] != 0) {
            throw new \Exception($retval['po_ret_msg']);
        }
    }

    /**
     * Delete Bill Data
     */
    protected function _deleteBillData($orderSeq) {
        // Update T_ClaimHistory        
        $sql  = " SELECT Seq FROM T_ClaimHistory WHERE PrintedFlg = 0 AND ValidFlg = 1 AND OrderSeq = ( SELECT MAX(o.P_OrderSeq) FROM T_Order o WHERE o.OrderSeq = :OrderSeq) ";
        $rowCH = $this->_adapter->query($sql)->execute(array('OrderSeq' => $orderSeq))->current();
        if ($rowCH) {
            $sql  = " UPDATE T_ClaimHistory SET ValidFlg = 0 WHERE Seq = :Seq ";
            $this->_adapter->query($sql)->execute(array('Seq' => $rowCH['Seq']));
        }

        // Update T_ReclaimIndicate        
        $sql  = " SELECT Seq FROM T_ReclaimIndicate WHERE IndicatedFlg = 0 AND ValidFlg = 1 AND OrderSeq = ( SELECT MAX(o.P_OrderSeq) FROM T_Order o WHERE o.OrderSeq = :OrderSeq) ";
        $rowRI = $this->_adapter->query($sql)->execute(array('OrderSeq' => $orderSeq))->current();
        if ($rowRI) {
            $sql  = " UPDATE T_ReclaimIndicate SET ValidFlg = 0 WHERE Seq = :Seq ";
            $this->_adapter->query($sql)->execute(array('Seq' => $rowRI['Seq']));
        }

    }
    
    /**
     * Regist AT_ReceiptControl
     */
    protected function _registAtReceiptControl($orderSeq) {
        $prm = array( ':OrderSeq' => $orderSeq );
        $sql = "SELECT MAX(ReceiptSeq) AS ReceiptSeq FROM T_ReceiptControl WHERE OrderSeq = :OrderSeq";
        $receiptSeq = $this->_adapter->query( $sql )->execute( $prm )->current()['ReceiptSeq'];

        $sql = "SELECT ClearConditionForCharge, ClearConditionDate FROM T_PayingAndSales WHERE OrderSeq = :OrderSeq";
        $rowATReceiptControl = $this->_adapter->query( $sql )->execute( $prm )->current();
        $clearConditionForCharge = $rowATReceiptControl['ClearConditionForCharge'];
        $clearConditionDate = $rowATReceiptControl['ClearConditionDate'];

        // 入金取消前立替処理－ステータス、入金取消前配送－着荷確認
        $sql = "SELECT Chg_Status, Deli_ConfirmArrivalFlg FROM T_Order WHERE OrderSeq = :OrderSeq";
        $ri = $this->_adapter->query( $sql )->execute( $prm );
        $chgStatus = $ri->current()['Chg_Status'];
        $deliConfirmArrivalFlg = $ri->current()['Deli_ConfirmArrivalFlg'];

        $atdata = array(
            'ReceiptSeq'                     => $receiptSeq,
            'AccountNumber'                  => null,
            'ClassDetails'                   => null,
            'BankFlg'                        => 2,
            'Before_ClearConditionForCharge' => $clearConditionForCharge,
            'Before_ClearConditionDate'      => $clearConditionDate,
            'Before_Chg_Status'              => $chgStatus,
            'Before_Deli_ConfirmArrivalFlg'  => $deliConfirmArrivalFlg
        );

        $mdl_atrc = new \models\Table\ATableReceiptControl( $this->_adapter );
        $mdl_atrc->saveNew($atdata);

    }

	/**
	 * 注文マイページ破棄バッチ
	 */
	public function mypageorderinvalid(){
        $mdlu = new TableUser($this->_adapter);                        // ユーザー
        $mdlo = new TableMypageOrder($this->_adapter);                 // 注文マイページ
        $mdlsys = new TableSystemProperty($this->_adapter);            // システムプロパティ

        try {
            $this->_adapter->getDriver()->getConnection()->beginTransaction();

            // ユーザーID
            $userId = $mdlu->getUserId(99, 1);

            // 対象期間
            $days = $mdlsys->getValue(TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'OrderMypageCloseValidDays');
            $days = $days > 0 ? $days : 30; // 念のため保護

            // 対象データの取得
            $sql = <<<EOQ
                    SELECT
                          T_MypageOrder.Seq
                    FROM  T_MypageOrder, T_Order
                    WHERE T_MypageOrder.OrderSeq     = T_Order.OrderSeq
                    AND   T_MypageOrder.ValidFlg     = 1
                    AND   T_Order.DataStatus         = 91
                    AND   DATE_ADD(T_Order.UpdateDate, INTERVAL $days DAY) <= current_timestamp()
EOQ;
            $stm = $this->_adapter->query($sql);
            $datas = $stm->execute();

            // 更新処理
            foreach ($datas as $data) {
                // 注文マイページ
                $mdlo->saveUpdate(
                    array(
                            'UpdateId' => $userId,
                            'ValidFlg' => 0,
                    ),
                    $data['Seq']
                );
            }

            $this->_adapter->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            $this->_adapter->getDriver()->getConnection()->rollBack();
            throw $e;
        }
	}

	/**
	 * 顧客関連付けバッチ
	 */
	public function mypagetomancust(){
        $mdlc = new TableMypageCustomer($this->_adapterMypage);
        $mdltb = new TableMypageToBackIF($this->_adapterMypage);

        try {
            $this->_adapterMypage->getDriver()->getConnection()->beginTransaction();

            // マイページ顧客読み込み
            $sql = <<<EOQ
                    SELECT
                          CustomerId
                    FROM  T_MypageCustomer
                    WHERE ManCustId IS NULL
                    AND   ValidFlg = 1
EOQ;
            $stm = $this->_adapterMypage->query($sql);
            $datas = $stm->execute();

            // 更新処理
            foreach ($datas as $data){
                // 管理顧客番号取得
                $manCustId = $this->getManCustId($data['CustomerId']);

                if ($manCustId != null) {
                    // 管理顧客番号が特定出来た場合
                    $mdlc->saveUpdate(
                        array(
                            'ManCustId' => $manCustId,
                        ),
                        $data['CustomerId']
                    );
                } else {
                    // 管理顧客番号が特定出来なかった場合

                    // 既に指示中のデータがいるか判定
                    $dataMypageToBackIF = $mdltb->findMypageToBackIF(
                        array(
                            'Status'     => 0,
                            'IFClass'    => 2,
                            'CustomerId' => $data['CustomerId'],
                        )
                    );

                    if (count($dataMypageToBackIF) == 0)
                    {
                        // 基幹反映指示インタフェース追加
                        $mdltb->saveNew(
                            array(
                                'Status' => 0,
                                'Reason' => null,
                                'IFClass' => 2,
                                'IFData' => Json::encode( array('CustomerId' => $data['CustomerId']) ),
                                'OrderSeq' => null,
                                'ManCustId' => null,
                                'CustomerId' => $data['CustomerId'],
                                'ValidFlg' => 1,
                            )
                        );
                    }
                }
            }

            $this->_adapterMypage->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            $this->_adapterMypage->getDriver()->getConnection()->rollBack();
            throw $e;
        }
	}

	/**
	 * 管理顧客番号取得関数
	 * @return int 管理顧客番号
	 */
	public function getManCustId($customerId){
        $mdltc = new TableMypageCustomer($this->_adapterMypage);
        $mdlvc = new MypageViewCustomer($this->_adapterMypage);
        $manCustId = null;

        // マイページ顧客のデータを取得
        $mypageCustomer = $mdltc->find($customerId)->current();

        // 加盟店顧客のデータを取得
        $sql = <<<EOQ
                SELECT
                      EntCustSeq
                     ,ManCustId
                FROM  MV_EnterpriseCustomer
                WHERE RegNameKj         = :RegNameKj
                AND   RegUnitingAddress = :RegUnitingAddress
                AND   RegPhone        IN (:RegPhone, :RegMobilePhone)
                AND   ValidFlg          = 1
                AND   EXISTS ( SELECT * FROM MV_Enterprise e WHERE e.EnterpriseId = MV_EnterpriseCustomer.EnterpriseId AND ifnull(e.OemId, 0) = :OemId )
                ORDER BY EntCustSeq DESC
EOQ;
        $stm = $this->_adapterMypage->query($sql);

        $prm = array(
                ':RegNameKj'  => $mypageCustomer['RegNameKj'],
                ':RegUnitingAddress'  => $mypageCustomer['RegUnitingAddress'],
                ':RegPhone'  => $mypageCustomer['RegPhone'],
                ':RegMobilePhone'  => $mypageCustomer['RegMobilePhone'],
                ':OemId'  => (int)$mypageCustomer['OemId'],
        );

        $datas = $stm->execute($prm);

        foreach ($datas as $data){
            // 購入者を検索する
            $manCustData = $mdlvc->findCustomer(
                array(
                        'EntCustSeq'  => $data['EntCustSeq'],
                        'MailAddress' => $mypageCustomer['MailAddress'],
                )
            );

            // １件以上取得出来た場合
            if ($manCustData->count() > 0)
            {
                $manCustId = $data['ManCustId'];
                break;
            }
        }

        return $manCustId;
	}

    /**
     * 基幹の詳細入金画面で登録した入金・入金取消データをマイページDBに反映
     * IFClass=1：入金
     * IFClass=2：入金取消
     */
    public function fromCbadminToMypage() {
        $mdltbi = new TableToBackMypageIF($this->_adapter);
        $mdlu = new TableUser($this->_adapter);

        try {
            $this->_adapter->getDriver()->getConnection()->beginTransaction();
            $this->_adapterMypage->getDriver()->getConnection()->beginTransaction();

            // userId
            $userId = $mdlu->getUserId(99, 1);

            // ＩＦテーブルからデータを取得
            $datas = $mdltbi->findToBackMypageIF( array('Status' => 0), true );
//             $order = ResultInterfaceToArray($datas);
//             print_r($order);

            foreach ($datas as $data){
                // 入金
                if ($data['IFClass'] == 1) {
                    $this->processDataForClassIs1($data, $userId);
                }

                // 入金取消
                if ($data['IFClass'] == 2) {
                    $this->processDataForClassIs2($data, $userId);
                }
            }

            $this->_adapter->getDriver()->getConnection()->commit();
            $this->_adapterMypage->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            $this->_adapter->getDriver()->getConnection()->rollBack();
            $this->_adapterMypage->getDriver()->getConnection()->rollBack();
            throw $e;
        }
    }

    /**
     * 入金データを反映
     * T_MypageToBackIF：対象注文のデータを作成または更新
     * T_SbpsReceiptControl：PayTypeは1に更新
     * T_CreditPayment：PaymentTypeは1に更新
     */
    protected function processDataForClassIs1($data, $userId) {
            // B1
            $parameters = [
                'Status'         => 1,
                'Reason'         => null,
                'IFClass'        => 4,
                'IFData'         => $data['IFData'],
                'OrderSeq'       => $data['OrderSeq'],
                'ManCustId'      => null,
                'CustomerId'     => null,
                'MailFlg'        => 1,
                'MailRetryCount' => 0,
                'RegistDate'     => date('Y-m-d H:i:s'),
                'UpdateDate'     => date('Y-m-d H:i:s'),
                'ValidFlg'       => 1,
            ];
            $mdltbi = new TableMypageToBackIF($this->_adapterMypage);
            $mdltbi->saveUpdateOrCreate($parameters, $data['OrderSeq']);
            // B2
            $ifData = Json::decode($data['IFData'], Json::TYPE_ARRAY);
            $parameters = [
                'OrderSeq'       => $data['OrderSeq'],
                'PayType'        => 1,
                'PaymentName'    => $ifData['payment_method'],
                'ReceiptDate'    => $data['RegistDate'],
                'RegistDate'     => date('Y-m-d H:i:s'),
                'UpdateDate'     => date('Y-m-d H:i:s'),
                'RegistId'       => $userId,
                'UpdateId'       => $userId,
                'ValidFlg'       => 1,
            ];
            $mdltbsrc = new TableSbpsReceiptControl($this->_adapterMypage);
            $mdltbsrc->saveUpdateOrCreate($parameters, $data['OrderSeq']);
            // B3
            $mdltbcp = new TableCreditPayment($this->_adapterMypage);
            $mdltbcp->saveUpdateOrCreate($data['OrderSeq'], ['PaymentType' => 1]);
            // B4
            $mdltbtbmi = new TableToBackMypageIF($this->_adapter);
            $mdltbtbmi->saveUpdate( array('Status' => 1), $data['Seq'] );
    }

    /**
     * getReceiptControl
     */
    protected function getReceiptControlByOrderSeq($orderSeq) {
        $mdltbi = new TableReceiptControl($this->_adapter);
        return $mdltbi->findByOrderSeq($orderSeq)->current();
    }

    /**
     * getMCode
     */
    protected function getMCode($keyCode) {
        $mdltbi = new TableCode($this->_adapter);
        return $mdltbi->getMasterAssCode(198, $keyCode);
    }

    /**
     * processDataForClassIs2
     */
    protected function processDataForClassIs2($data, $userId) {
        // B1
        $mdltbi = new TableMypageToBackIF($this->_adapterMypage);
        $mdltbi->saveUpdateWhere(['ValidFlg' => 0], ['IFClass' => 4, 'OrderSeq' => $data['OrderSeq']]);

        // B2
        $mdltbsrc = new TableSbpsReceiptControl($this->_adapterMypage);
        $mdltbsrc->saveUpdate(['ValidFlg' => 0, 'UpdateId' => $userId], $data['OrderSeq']);

        // B3
        $mdltbcp = new TableCreditPayment($this->_adapterMypage);
        $mdltbcp->saveUpdate($data['OrderSeq'], ['PaymentType' => 0]);

        // B4
        $mdltbrih = new TableReceiptIssueHistory($this->_adapterMypage);
        $mdltbrih->updateValidFlg($data['OrderSeq'], 0);

        // B5
        $mdltbtbmi = new TableToBackMypageIF($this->_adapter);
        $mdltbtbmi->saveUpdate( array('Status' => 1), $data['Seq'] );
    }

}
