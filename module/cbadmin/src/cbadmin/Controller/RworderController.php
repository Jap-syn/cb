<?php
namespace cbadmin\Controller;

use models\Logic\OrderCancelException;
use Zend\Json\Json;
use Coral\Base\BaseHtmlUtils;
use Coral\Base\BaseGeneralUtils;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\CoralCodeMaster;
use cbadmin\Application;
use cbadmin\classes\SearchfCache;
use models\Table\TableOrder;
use models\Table\TableOem;
use models\Table\TableOrderItems;
use models\Table\TableCancel;
use models\Table\TableClaimHistory;
use models\Table\TableReclaimIndicate;
use models\Table\TableStampFee;
use models\Table\TablePayingControl;
use models\Table\TableOperator;
use models\Table\TableCjResult;
use models\Table\TableCjResultDetail;
use models\Table\TablePayingAndSales;
use models\Table\TableCustomer;
use models\Table\TableOrderSummary;
use models\Table\TableEnterprise;
use models\Table\TableDeliMethod;
use models\Table\TableDeliveryDestination;
use models\Table\TableUser;
use models\Table\TableSite;
use models\Table\TableSystemProperty;
use models\View\ViewOrderCustomer;
use models\View\ViewDelivery;
use models\Logic\LogicCancel;
use models\Logic\MergeOrder\LogicMergeOrderHelper;
use Zend\Db\Adapter\Driver\ResultInterface;
use Coral\Coral\History\CoralHistoryOrder;
use Coral\Coral\Mail\CoralMail;
use models\Table\ATableOrder;
use models\Table\TableCode;
use models\View\MypageViewReceiptIssueHistory;
use models\Table\TableOrderAddInfo;
use models\Logic\LogicPayeasy;

class RworderController extends CoralControllerAction
{
	protected $_componentRoot = './application/views/components';

    const UNSELECT_LIST_ITEM = -99;

	/**
	 * アプリケーションオブジェクト
	 * @var Application
	 */
	private $app;

	/**
	 * Controllerを初期化する
	 */
	public function _init()
	{
        $this->app = Application::getInstance();
        $this->view->assign('userInfo', $this->app->authManagerAdmin->getUserInfo());

        $this->addStyleSheet('../css/default02.css')
        ->addStyleSheet('../css/cbadmin/rworder/detail/default.css')
        ->addJavaScript('../js/prototype.js');

        $this->setPageTitle("後払い.com - 注文情報");
	}

	/**
	 * 注文詳細画面
	 */
	public function detailAction()
	{
        $this->addJavaScript("../js/corelib.js");
        $this->addJavaScript("../js/base.ui.js");
        $this->addJavaScript("../js/base.ui.datepicker.js");
        $this->addStyleSheet("../css/base.ui.datepicker.css");
        $this->addJavaScript( '../js/json+.js' );
        $this->addJavaScript( '../js/json_format.js' );
        $this->addJavaScript( '../js/fixednote.js' );

        // 注文ステータスによる色分け用CSSのアサイン
        $this->addStyleSheet( '../css/cbadmin/orderstatus/detail_' .
            ( $this->app->tools['orderstatus']['style'] ? $this->app->tools['orderstatus']['style'] : 'default' ) .
            '.css' );

        // 詳細表示用の注文Seqを取得
        $oseq = $this->getOrderSeqForDetail();

        // 不払い検索でキャッシュ不一致なので処理終了
        if( $oseq === false ) return $this->view;

        if ($oseq == 0)
        {
            // 注文Seqが指定されていなければ、エラーページに飛ばす。
            return $this->_redirect('error/nop');
        }

        // 注文・購入者情報
        $orderCustomer = $this->getOrderCustomerRi($oseq)->current();
        $orderCustomer['CustStsStr'] = $this->makeCustStsStr($oseq);    // 顧客スタータス文字列の生成
        $taxClass = $orderCustomer['TaxClass'];

        //注文情報
        $mdlo = new TableOrder( $this->app->dbAdapter );
        $order = $mdlo->find( $oseq )->current();

        // OEM情報
        $oem = null;
        if(!is_null($order['OemId']) && $order['OemId'] != 0) {
            $oem['OemId'] = $order['OemId'];
            $mdlOem = new TableOem($this->app->dbAdapter);
            $oemInfo = $mdlOem->findOem($order['OemId'])->current();

            //OEM情報が取れている場合OEM名をセット
            if ($oemInfo) {
                $oem['OemNameKj'] = $oemInfo['OemNameKj'];
            }
        }

        // 配送情報
        $mdldeli = new ViewDelivery($this->app->dbAdapter);
        $deli = $mdldeli->findDelivery(array('OrderSeq' => $oseq, 'DataClass' => 1))->current();

        // 商品情報
        $mdloi = new TableOrderItems($this->app->dbAdapter);
        $deliveryFee = 0;
        $settlementFee = 0;
        $reclaimFee = 0;
        $totalSumMoney = 0;
        $totalClaimMoney = 0;
        $itemsNeta = $mdloi->findByOrderSeq($oseq);

        // キャンセル情報
        $mdlcl = new TableCancel($this->app->dbAdapter);
        $cancel = $mdlcl->findCancel(array('OrderSeq' => $oseq))->current();

        // 請求履歴情報
        $mdlhis = new TableClaimHistory($this->app->dbAdapter);
        $his = $mdlhis->getReturnMailTagetByOrderSeq($oseq);

        // 印紙代
        $mdlstmp = new TableStampFee($this->app->dbAdapter);
        $stampFee = $mdlstmp->getStampFee($oseq);
        $custom['StampFee'] = $stampFee;

        // 立替振込管理情報
        $mdlpc = new TablePayingControl($this->app->dbAdapter);
        $pcData = $mdlpc->find($orderCustomer['Chg_Seq'])->current();
        $custom['ExecScheduleDate'] = ($pcData) ? $pcData['ExecScheduleDate'] : '';

        // マスター関連クラス
        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);

        //現在日時の消費税率
        $propertyTable = new TableSystemProperty($this->app->dbAdapter);
        $taxRate = $propertyTable->getTaxRateAt(date('Y-m-d'));

        foreach ($itemsNeta as $item)
        {
            // 税率未設定商品の税率設定
            if ($item['TaxRate'] == NULL || $item['TaxRate'] == '' ) {
                $item['TaxRate'] = $taxRate;
            }

            // 値引き商品の税率設定
            if ($item['SumMoney'] < 0) {
                $item['TaxRate'] = 0;
            }

            switch((int)$item['DataClass'])
            {

                case 2:	// 送料
                    $deliveryFee = $item['SumMoney'];
                    break;
                case 3:	// 手数料
                    $settlementFee = $item['SumMoney'];
                    break;
                case 4:	// 外税額
                    $exTax = $item['SumMoney'];
                    break;
                default:
                    $items[] = $item;
                    $totalSumMoney += $item['SumMoney'];
                    break;
            }
        }

        // 商品の合計＋送料＋手数料＋外税額
        $totalSumMoney += $deliveryFee + $settlementFee + $exTax;

        // 再請求に伴う追加手数料
        $reclaimFee = $orderCustomer['Clm_L_DamageInterestAmount'] + $orderCustomer['Clm_L_ClaimFee'];

        // 請求金額
        $totalClaimMoney = $orderCustomer['ClaimAmount'];

        // 支払済み金額変更に必要なベーストータル
        $baseTotal = $totalSumMoney + $reclaimFee;

        // 加工
        // ステータス
        $custom["DataStatus"] = $codeMaster->getDataStatusCaption($orderCustomer['DataStatus']);

        // 電話履歴
        switch($orderCustomer['PhoneHistory'])
        {
            case 1:
                $custom['PhoneHistory'] = '○';
                break;
            case 3:
                $custom['PhoneHistory'] = '×';
                break;
            default:
                $custom['PhoneHistory'] = '--';
                break;
        }

        // リアル送信結果
        switch($orderCustomer['RealSendMailResult'])
        {
            case 1:
                $custom['RealSendMailResult'] = 'OK';
                break;
            case 2:
                $custom['RealSendMailResult'] = 'NG';
                break;
            default:
                $custom['RealSendMailResult'] = '--';
                break;
        }

        // ｅ電
        switch($orderCustomer['eDen'])
        {
            case 1:
                $custom['eDen'] = '○';
                break;
            case 2:
                $custom['eDen'] = '△';
                break;
            case 3:
                $custom['eDen'] = '×';
                break;
            default:
                $custom['eDen'] = '--';
                break;
        }

        // 与信確定担当者
        $mdlop = new TableOperator($this->app->dbAdapter);
        $row = $mdlop->findOperator($orderCustomer['Incre_DecisionOpId'])->current();
        $custom['Incre_DecisionOpName'] = ($row) ? $row['NameKj'] : '';

        // ******************* 不払い管理関連 *************************
        // TEL有効
        $custom["ValidTelTag"] = BaseHtmlUtils::SelectTag(
            "ValidTel",
            $codeMaster->getValidTelMaster(),
            $orderCustomer['ValidTel']
        );

        // キャリア
        $custom["CarrierTag"] = BaseHtmlUtils::SelectTag(
            "Carrier",
            $codeMaster->getCarrierMaster(),
            $orderCustomer['Carrier']
        );

        // メール有効
        $custom["ValidMailTag"] = BaseHtmlUtils::SelectTag(
            "ValidMail",
            $codeMaster->getValidMailMaster(),
            $orderCustomer['ValidMail']
        );

        // 住所有効
        $custom["ValidAddressTag"] = BaseHtmlUtils::SelectTag(
            "ValidAddress",
            $codeMaster->getValidAddressMaster(),
            $orderCustomer['ValidAddress'],
            'onChange="changeValidAddress();"'
        );

        // 経過日数
        if ($orderCustomer['Clm_F_LimitDate'] != null)
        {
            $pastDays = BaseGeneralUtils::CalcSpanDays2(
                date('Y-m-d H:i:s', strtotime($orderCustomer['Clm_F_LimitDate'])), date('Y-m-d H:i:s'));
            $custom["PastDays"] = sprintf("%d 日", $pastDays);
        }
        else
        {
            $custom["PastDays"] = "*** 日";
        }

        // 督促分類
        $custom["RemindClassTag"] = BaseHtmlUtils::SelectTag(
            "RemindClass",
            $codeMaster->getRemindClassMaster(),
            $orderCustomer['RemindClass']
        );

        // 追加連絡先状態１
        $custom["CinfoStatus1Tag"] = BaseHtmlUtils::SelectTag(
            "CinfoStatus1",
            $codeMaster->getCinfoStatusMaster(),
            $orderCustomer['CinfoStatus1']
        );

        // 追加連絡先状態２
        $custom["CinfoStatus2Tag"] = BaseHtmlUtils::SelectTag(
            "CinfoStatus2",
            $codeMaster->getCinfoStatusMaster(),
            $orderCustomer['CinfoStatus2']
        );

        // 追加連絡先状態３
        $custom["CinfoStatus3Tag"] = BaseHtmlUtils::SelectTag(
            "CinfoStatus3",
            $codeMaster->getCinfoStatusMaster(),
            $orderCustomer['CinfoStatus3']
        );

        // 入金遅れ日数
        if ($orderCustomer['CloseReceiptDate'] != null)
        {
            $receiptPastDays = BaseGeneralUtils::CalcSpanDays2(
                date('Y-m-d H:i:s', strtotime($orderCustomer['Clm_F_LimitDate'])),
                date('Y-m-d H:i:s', strtotime($orderCustomer['CloseReceiptDate'])));
            $custom["ReceiptPastDays"] = sprintf("%d 日", $receiptPastDays);
        }
        else
        {
            $custom["ReceiptPastDays"] = "*** 日";
        }

        // 最終回収手段
        $custom["FinalityCollectionMeanTag"] = BaseHtmlUtils::SelectTag(
            "FinalityCollectionMean",
            $codeMaster->getFinalityCollectionMeanMaster(),
            $orderCustomer['FinalityCollectionMean']
        );

        // 住民票
        $custom["ResidentCardTag"] = BaseHtmlUtils::SelectTag(
            "ResidentCard",
            $codeMaster->getResidentCardMaster(),
            $orderCustomer['ResidentCard']
        );

        // 手書き手紙
        $custom["LonghandLetterTag"] = BaseHtmlUtils::SelectTag(
            "LonghandLetter",
            $codeMaster->getLonghandLetterMaster(),
            $orderCustomer['LonghandLetter']
        );

        // オペレーター情報
        $custom['MyName'] = $this->app->authManagerAdmin->getUserInfo()->NameKj;
        $custom['MyOpId'] = $this->app->authManagerAdmin->getUserInfo()->OpId;

        if ($orderCustomer['FinalityRemindOpId'] != null)
        {
            $mdlOp = new TableOperator($this->app->dbAdapter);
            $remindOperator = $mdlop->findOperator($orderCustomer['FinalityRemindOpId'])->current();
            $custom['FinalityRemindOpName'] = ($remindOperator) ? $remindOperator['NameKj'] : '';
        }

        // 社内与信
        switch ($orderCustomer['Incre_Status'])
        {
            case 1:
                $custom['Incre_Status'] = 'OK';
                break;
            case -1:
                $custom['Incre_Status'] = 'NG';
                break;
            default:
                $custom['Incre_Status'] = '';
                break;
        }

        // DMI与信
        switch ($orderCustomer['Dmi_Status'])
        {
            case 1:
                $custom['Dmi_Status'] = 'OK';
                break;
            case -1:
                $custom['Dmi_Status'] = 'NG';
                break;
            default:
                $custom['Dmi_Status'] = '';
                break;
        }

        // 着荷確認結果
        switch ($deli['Deli_ConfirmArrivalFlg'])
        {
            case 1:
                $custom['Deli_ConfirmArrivalFlg'] = '確認済';
                break;
            case -1:
                $custom['Deli_ConfirmArrivalFlg'] = '未確認';
                break;
            default:
                $custom['Deli_ConfirmArrivalFlg'] = '';
                break;
        }

        // 入金方法
        if (! empty ( $orderCustomer ['ReceiptClass'] )) {
            // コードマスターから入金方法のコメントを取得
            $mdlc = new TableCode ( $this->app->dbAdapter );
            $ReceiptMethod = $mdlc->find ( 198, $orderCustomer ['ReceiptClass'] )->current ();
            $this->view->assign ( 'receiptMethod', $ReceiptMethod );
        }

        // 入金額／入金状況
        $custom['Rct_ReceiptAmount'] = $order['Rct_Status'] ? $order['Rct_ReceiptAmount'] : '';
        $custom['Rct_Status'] = $order['Rct_Status'];

        // キャンセル情報
        if ($cancel)
        {
            $custom['CancelDate'] = $cancel['CancelDate'];
            $custom['ApprovalDate'] = $cancel['ApprovalDate'];

            $codeval = $this->app->dbAdapter->query(" SELECT KeyContent FROM M_Code WHERE CodeId = 90 AND KeyCode = :KeyCode "
                )->execute(array(':KeyCode' => $cancel['CancelReasonCode']))->current()['KeyContent'];
            $custom['CancelReason'] = (trim($cancel['CancelReason']) == '') ?
                $codeval : ($codeval . '(' . $cancel['CancelReason'] . ')');
        }

        // 再請求情報
        $mdlch = new TableClaimHistory($this->app->dbAdapter);
        $reclaim1 = $mdlch->getLatestClaimHistory($oseq, 2);		// 再請求1
        $reclaim2 = $mdlch->getLatestClaimHistory($oseq, 3);		// 再請求2
        $reclaim3 = $mdlch->getLatestClaimHistory($oseq, 4);		// 再請求3
        $reclaim4 = $mdlch->getLatestClaimHistory($oseq, 5);		// 内容証明
        $reclaim5 = $mdlch->getLatestClaimHistory($oseq, 6);		// 再請求4
        $reclaim6 = $mdlch->getLatestClaimHistory($oseq, 7);		// 再請求5
        $reclaim7 = $mdlch->getLatestClaimHistory($oseq, 8);		// 再請求6
        $reclaim8 = $mdlch->getLatestClaimHistory($oseq, 9);		// 再請求7

        // count関数対策
        $claimHistoryDetailsLen = 0;
        $claimHistoryDetails = $this->getClaimHistoryDetails($oseq);
        if(!empty($claimHistoryDetails)) {
            $claimHistoryDetailsLen = count($claimHistoryDetails);
        }

        $this->view->assign('claimCount', $claimHistoryDetailsLen);

        // PrintedStatus
        $row_ch = $this->app->dbAdapter->query(" SELECT PrintedStatus FROM T_ClaimHistory WHERE PrintedFlg = 0 AND ValidFlg = 1 AND OrderSeq = :OrderSeq LIMIT 1 "
            )->execute(array(':OrderSeq' => $oseq))->current();
        if ($row_ch) {
            $custom['PrintedStatus'] = $row_ch['PrintedStatus'];
        }

        // 再請求指示状態
        $ri_cnt = $this->app->dbAdapter->query(" SELECT COUNT(1) AS cnt FROM T_ReclaimIndicate WHERE IndicatedFlg = 0 AND ValidFlg = 1 AND OrderSeq = :OrderSeq "
        )->execute(array(':OrderSeq' => $oseq))->current()['cnt'];
        if ($ri_cnt > 0) {
            $custom['PrintedStatus'] = 1; // CSV出力指示済み
        }

        // キャンセル完了時に印刷ジョブ転送済状態のチェック
        // ※印刷ジョブ転送済と表示するため(2013.02.18)
        $custom['PrintedTransBeforeCancelled'] = $order['PrintedTransBeforeCancelled'];

        $custom['reclaim1a'] = $reclaim1['ClaimDate'];
        $custom['reclaim1b'] = $reclaim1['LimitDate'];
        $custom['reclaim1c'] = $reclaim1['Additional'];
        $custom['reclaim2a'] = $reclaim2['ClaimDate'];
        $custom['reclaim2b'] = $reclaim2['LimitDate'];
        $custom['reclaim2c'] = $reclaim2['Additional'];
        $custom['reclaim3a'] = $reclaim3['ClaimDate'];
        $custom['reclaim3b'] = $reclaim3['LimitDate'];
        $custom['reclaim3c'] = $reclaim3['Additional'];
        $custom['reclaim4a'] = $reclaim4['ClaimDate'];
        $custom['reclaim4b'] = $reclaim4['LimitDate'];
        $custom['reclaim4c'] = $reclaim4['Additional'];
        $custom['reclaim5a'] = $reclaim5['ClaimDate'];
        $custom['reclaim5b'] = $reclaim5['LimitDate'];
        $custom['reclaim5c'] = $reclaim5['Additional'];
        $custom['reclaim6a'] = $reclaim6['ClaimDate'];
        $custom['reclaim6b'] = $reclaim6['LimitDate'];
        $custom['reclaim6c'] = $reclaim6['Additional'];
        $custom['reclaim7a'] = $reclaim7['ClaimDate'];
        $custom['reclaim7b'] = $reclaim7['LimitDate'];
        $custom['reclaim7c'] = $reclaim7['Additional'];
        $custom['reclaim8a'] = $reclaim8['ClaimDate'];
        $custom['reclaim8b'] = $reclaim8['LimitDate'];
        $custom['reclaim8c'] = $reclaim8['Additional'];

        // 請求取りまとめ関連
        // 請求取りまとめで取りまとめ先（親）があった場合には対象の注文IDを取得する
        if($order['OrderSeq'] != $order['P_OrderSeq']) {
            $parentorder = $mdlo->find($order['P_OrderSeq'])->current();

            $this->view->assign( 'ccpOseq', $order['P_OrderSeq']);
            $this->view->assign( 'parentOrder', array('seq' => $parentorder['OrderSeq'], 'id' => $parentorder['OrderId']));
        }

        // 請求取りまとめで取りまとめ元（子）があった場合には対象の注文IDを取得する
        if($order['CombinedClaimParentFlg']) {
            $sql  = " select OrderSeq as seq, OrderId as id ";
            $sql .= " from   T_Order  ";
            $sql .= " where  CombinedClaimTargetStatus in (91, 92) and P_OrderSeq = :P_OrderSeq and P_OrderSeq <> OrderSeq ";
            $sql .= " order by OrderSeq desc ";

            $stm = $this->app->dbAdapter->query($sql);

            $ri = $stm->execute(array(':P_OrderSeq' => $order['P_OrderSeq']));

            $rs = new \Zend\Db\ResultSet\ResultSet();
            $rs->initialize($ri);
            $childOrders = $rs->toArray();

            $this->view->assign( 'ccparf', $order['CombinedClaimParentFlg'] );
            $this->view->assign( 'childOrders', $childOrders );
        }

        $cjResult = new TableCjResult($this->app->dbAdapter);
        $cjResultDetail = new TableCjResultDetail($this->app->dbAdapter);

        //審査システム関連データ取得
        $cjResult_data = $cjResult->orderSeqSearch($oseq)->current();

        //セットされていない場合は空にする
        $cjResult_key_data = "";
        if ($cjResult_data && !is_null($cjResult_data['TotalScoreWeighting'])) {
            $cjResult_key_data = ($cjResult_data['TotalScoreWeighting'] + nvl($orderCustomer['Incre_JudgeScoreTotal'],0));
        }
        else if ($cjResult_data && !is_null($cjResult_data['TotalScore'])) {
            $cjResult_key_data = ($cjResult_data['TotalScore'] + nvl($orderCustomer['Incre_JudgeScoreTotal'],0));
        }

        //審査システム用データ詳細取得cjDetail
        $cjResultDetail = $cjResultDetail->orderSeqSearch($oseq);

        // メール履歴
        $sql = " SELECT Subject, MailSendDate FROM T_MailSendHistory WHERE ValidFlg = 1 AND OrderSeq = :OrderSeq ORDER BY MailSendDate DESC ";
        $mailHist = ResultInterfaceToArray($this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq)));

        //OrderSeqをキーとしデータ整形する
        $seq = false;
        foreach($cjResultDetail as $value){
            //違うSeqの情報になったらbreak
            if($seq !== false && $seq != $value['Seq']){
                break;
            }
            //データがすでにセットされているか？
            if(isset($cjResult_detail_key_data)){
                $seq = $value['Seq'];
                $cjResult_detail_key_data.="/「".$value['DetectionPatternName']."」".nvl($value['DetectionPatternScoreWeighting'], $value['DetectionPatternScore']);
            }else{
                $seq = $value['Seq'];
                //セットされていない場合データ整形
                $remarks = "「".$value['DetectionPatternName']."」".nvl($value['DetectionPatternScoreWeighting'], $value['DetectionPatternScore']);
                $cjResult_detail_key_data = $remarks;

            }
        }

        // 過剰入金色分けしきい値
        $excessPaymentColorThreshold = $this->app->dbAdapter->query(
            " SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'systeminfo' AND Name = 'ExcessPaymentColorThreshold' ")->execute(null)->current()['PropValue'];
        $orderCustomer['ExcessPaymentColorThreshold'] = intval($excessPaymentColorThreshold);

        // ネットDE受取対応
        $mdlato = new ATableOrder($this->app->dbAdapter);
        $row_atord = $mdlato->find($oseq)->current();
        $orderCustomer['RepayTCFlg'] = $row_atord['RepayTCFlg'];
        $orderCustomer['RepayPendingFlg'] = $row_atord['RepayPendingFlg'];

        // 伝票確認メール送信ストップ
        $orderCustomer['StopSendMailConfirmJournalFlg'] = $row_atord['StopSendMailConfirmJournalFlg'];

        // 注文マイページ情報アサイン
        $orderCustomer['MYPORD_Token'] = '';
        $orderCustomer['MYPORD_ValidToDate'] = '';
        $row_mo = $this->app->dbAdapter->query(" SELECT Token, DATE(ValidToDate) AS ValidToDate FROM T_MypageOrder WHERE OrderSeq = :OrderSeq ORDER BY Seq DESC LIMIT 1 "
                )->execute(array(':OrderSeq' => $oseq))->current();
        if ($row_mo) {
            $orderCustomer['MYPORD_Token'] = $row_mo['Token'];
            $orderCustomer['MYPORD_ValidToDate'] = $row_mo['ValidToDate'];
        }

        // 口座振替サービス
        if ($orderCustomer['CCCreditTransferFlg'] == 0) {
            $orderCustomer['CCCreditTransferFlg'] = '';
        }  else {
            $orderCustomer['CCCreditTransferFlg'] = $this->app->dbAdapter->query(" SELECT CreditTransferName FROM T_CreditTransfer WHERE CreditTransferId = :CreditTransferId LIMIT 1 "
            )->execute(array(':CreditTransferId' => $orderCustomer['CCCreditTransferFlg']))->current()['CreditTransferName'];
        }

        // アサイン
        $this->view->assign('oc', $orderCustomer);
        $this->view->assign('oem_note', isset($order['Oem_Note']) ? $order['Oem_Note'] : "");

        if(!is_null($oem)) $this->view->assign('oem', $oem);
        $this->view->assign('deli', $deli);
        $this->view->assign('items', $items);
        $this->view->assign('custom', $custom);						// 表示用に加工したデータの連想配列
        $this->view->assign('deliveryFee', $deliveryFee);			// 送料
        $this->view->assign('settlementFee', $settlementFee);		// 手数料
        $this->view->assign('exTax', $exTax);                       // 外税額
        $this->view->assign('taxClass', $taxClass);                 // 税区分
        $this->view->assign("sendMailTag",
            BaseHtmlUtils::SelectTag('sendMail',array(1 => '過剰入金', 3 => '不足入金', 2 => '返金(ｷｬﾝｾﾙ)', 4 => '伝票確認'), 1 )
        );                                                          // 同梱別送ﾌﾗｸﾞタグ
        $this->view->assign('mailHist', $mailHist);                 // メール履歴
        $this->view->assign('totalSumMoney', $totalSumMoney);		// 商品の合計金額
        $this->view->assign('reclaimFee', $reclaimFee);				// 再請求追加手数料
        $this->view->assign('totalClaimMoney', $totalClaimMoney);	// 請求合計
        $this->view->assign('baseTotal', $baseTotal);				// ベーストータル
        if(!empty($his)) {
            $this->view->assign('bill', $his);						// 請求書履歴情報
        }
        $this->view->assign('order', $order);	// 請求合計

        $this->view->assign('judge_system_point',$cjResult_key_data); //審査システム点数

        //審査システム結果
        $judge_system_result = (empty($cjResult_key_data) &&
                                strlen($cjResult_key_data) == 0 &&
                                $orderCustomer['DataStatus'] != 11) ? "審査システム判定不可" : $cjResult_detail_key_data;
        $this->view->assign('judge_system_result', $judge_system_result);

        // ツールURLをアサイン
        $this->view->assign('urlUnote', $this->app->tools['url']['unote']);				// 備考更新
        $this->view->assign('urlRegistBlk', $this->app->tools['url']['registblk']);		// ブラック登録
        $this->view->assign('urlRegistExc', $this->app->tools['url']['registexc']);		// 優良登録
        $this->view->assign('urlRegistCancelCancel', $this->app->tools['url']['registcancelcancel']);// キャンセル取消
        $this->view->assign('urlRegistCancelConfirmArrival', $this->app->tools['url']['registcancelconfirmarrival']);// 着荷確認取消
        $this->view->assign('urlReturnBill', $this->app->tools['url']['returnbill']);	//請求書不達メール送信

        if (isset($_SERVER["HTTP_REFERER"])) {
            $this->view->assign('backNavi',sprintf('<a href="%s">戻　る</a>', $_SERVER["HTTP_REFERER"]));
        }

        // 同梱ツール経由の請求実績の有無をアサイン
        $sb_histories = $mdlhis->findForSelfBillingByOrderSeq($oseq);
        $this->view->assign('sbHistories', $sb_histories);

        // 立替・売上データの反映
        $mdlpas = new TablePayingAndSales($this->app->dbAdapter);
        $pas = $mdlpas->findPayingAndSales(array('OrderSeq' => $oseq))->current();
        $this->view->assign('pas', $pas ? $pas : null);

        // 入金保留中のJNB口座情報を反映
        $accLogic = new \models\Logic\Jnb\LogicJnbAccount($this->app->dbAdapter);
        $this->view->assign('jnbAcc', $accLogic->findPendingAccountByOrderSeq($oseq));

        // 入金保留中のSMBC口座情報を反映
        $accLogic2 = new \models\Logic\Smbcpa\LogicSmbcpaAccount($this->app->dbAdapter);
        $this->view->assign('smbcAcc', $accLogic2->findPendingAccountByOrderSeq($oseq));

        // [調整額]リンクの生成
        $this->view->assign('linkStrPaying', $this->makeLinkPaying($oseq));

        // 注文日の期間チェック
        $this->view->assign('isInTerm', $this->isValidOrderdate($orderCustomer['ReceiptOrderDate'], $orderCustomer['RegistDate']));

        // 与信OKチケット情報
        $ticketLabel = $this->getTicketStatusLabel($oseq);
        $this->view->assign('OkTicketLabel', $ticketLabel);

        // 支払完了日
        $execDate = $this->app->dbAdapter->query(" SELECT pc.ExecDate FROM T_PayingControl pc INNER JOIN T_PayingAndSales pas ON pc.Seq = pas.PayingControlSeq WHERE pas.OrderSeq = :OrderSeq ")->execute(array(':OrderSeq' => $oseq))->current()['ExecDate'];
        $this->view->assign('execDate', $execDate);

        // サイト情報を取得
        $mdls = new TableSite($this->app->dbAdapter);
        $site = $mdls->findSite($orderCustomer['SiteId'])->current();
        $this->view->assign('Site', $site);

        // 請求先氏名ダブ表示
        $this->setPageTitle($orderCustomer['OrderId'] . ' ' . $orderCustomer['NameKj']);

        // 社内審査結果
        $mdlcl = new \models\Table\TableCreditLog($this->app->dbAdapter);
        $this->view->assign('IncreSnapShotString', $mdlcl->getIncreSnapShotString($oseq));

        // 不払率情報(サイト別集計取得)
        $val = array('cnt' => 0, 'cntall' => 0, 'sum' => 0, 'sumall' => 0, 'settlementfeesum' => 0);
        $sitenplist = array('siteid' => $site['SiteId'], 'sitenamekj' => $site['SiteNameKj'], 'settlementfeerate' => $site['SettlementFeeRate'], 'profitrate' => 0, 'profitandloss' => 0);
        for ($i=0; $i<6; $i++) {
            $sitenplist['type' . ($i + 1)] = $val;
        }
        $mdlst = new \models\Table\TableSiteTotal($this->app->dbAdapter);
        $row_st = $mdlst->find($site['SiteId'])->current();
        if ($row_st) {
            $aryNpTotals = Json::decode($row_st['NpTotal'], Json::TYPE_ARRAY);

            // count関数対策
            $aryNpTotalsLen = 0;
            if(!empty($aryNpTotals)) {
                $aryNpTotalsLen = count($aryNpTotals);
            }

            for ($j=0; $j<$aryNpTotalsLen; $j++) {
                $row = $aryNpTotals[$j];

                if ($row['type'] == 'Summary') {
                    $sitenplist['settlementfeerate'] = $row['SettlementFeeRate'];
                    $sitenplist['profitrate'] = $row['ProfitRate'];
                    $sitenplist['profitandloss'] = $row['ProfitAndLoss'];
                }
                else {
                    $sitenplist['type' . $row['type']]['cnt'] = $row['cnt'];
                    $sitenplist['type' . $row['type']]['cntall'] = $row['cntall'];
                    $sitenplist['type' . $row['type']]['sum'] = $row['sum'];
                    $sitenplist['type' . $row['type']]['sumall'] = $row['sumall'];
                    $sitenplist['type' . $row['type']]['settlementfeesum'] = $row['settlementfeesum'];
                }
            }
        }
        $this->view->assign('sitenplist', $sitenplist);

        // 不払い率背景色しきい値(％)
        $npRateColorThreshold = $this->app->dbAdapter->query(" SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'systeminfo' AND Name = 'NpRateColorThreshold' ")->execute(null)->current()['PropValue'];
        $this->view->assign('npRateColorThreshold', $npRateColorThreshold);

        // マイページからの請求書発行
        $sql = " SELECT MypageReissueRequestDate FROM T_ClaimControl WHERE OrderSeq = :OrderSeq AND MypageReissueClass > 90 ";
        $mypageclaim = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current()['MypageReissueRequestDate'];
        $this->view->assign('mypageclaim', $mypageclaim);

        // 領収書発行履歴
        $mdlmvrih = new MypageViewReceiptIssueHistory($this->app->dbAdapter);
        $row_mvrih = $mdlmvrih->findOrderSeq($oseq)->current();

        $this->view->assign('row_mvrih', $row_mvrih);

        if($orderCustomer['BillingAgentFlg'] == 1) {
            $mdloai = new TableOrderAddInfo($this->app->dbAdapter);
            $OdrAddInfData = $mdloai->find($oseq)->current();
            $this->view->assign('OrderAddInfo', $OdrAddInfData);
        }

        $obj = new \models\Table\TableUser($this->app->dbAdapter);
        $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);
        $this->view->assign('userId', $userId);

        // キャンセル理由
        $sql  = ' SELECT KeyCode ';
        $sql .= ' ,      KeyContent ';
        $sql .= '        FROM M_Code ';
        $sql .= '        WHERE CodeId = 90 ';
        $stm = $this->app->dbAdapter->query( $sql );
        $cancelReasons = ResultInterfaceToArray( $stm->execute( null ) );

        $lists['CancelReasonCode'] = array( self::UNSELECT_LIST_ITEM => '-' );
        foreach( $cancelReasons as $cancelReason ) {
            $lists['CancelReasonCode'][$cancelReason['KeyCode']] = $cancelReason['KeyContent'];
        }

        $this->view->assign( 'cancelList', $lists['CancelReasonCode'] );
        $lgCancel = new LogicCancel($this->app->dbAdapter);
        $this->view->assign( 'usedTodo2Pay', $lgCancel->_usedTodo2Pay($oseq));
        return $this->view;
	}

	/**
	 * 注文・購入者情報リストを取得する
	 * (V_OrderCustomer＋クエリ)
	 *
	 * @param int $oseq 注文SEQ
	 * @return ResultInterface
	 */
	private function getOrderCustomerRi($oseq)
	{
	    $sql = <<<EOQ
SELECT voc.*
       /* 追加取得項目 */
,      msh.Subject                 AS Subject                      /* メールタイトル */
,      msh.MailSendDate            AS MailSendDate                 /* メール送信日時 */
,      rc.ReceiptProcessDate       AS ReceiptProcessDate           /* 確定日 */
,      cc.ReceiptAmountTotal       AS ReceiptAmountTotal           /* 入金済額 */
,      o.OemClaimTransDate         AS OemClaimTransDate            /* OEM債権移管日 */
,      o.Dmg_DecisionDate          AS Dmg_DecisionDate             /* 貸倒確定日 */
,      o.Tel30DaysFlg              AS Tel30DaysFlg                 /* 架電30日 */
,      o.Tel90DaysFlg              AS Tel90DaysFlg                 /* 架電90日 */
,      c.Occupation                AS Occupation                   /* 職業 */
,      c.CorporateName             AS CorporateName                /* 法人名 */
,      c.DivisionName              AS DivisionName                 /* 部署名 */
,      c.CpNameKj                  AS cstCpNameKj                  /* 担当者名 */
,      c.AddressKn                 AS AddressKn                    /* 住所カナ */
,      c.EntCustId                 AS EntCustId                    /* 加盟店顧客 */
,      o.ServiceExpectedDate       AS ServiceExpectedDate          /* 役務提供予定日 */
,      o.CreditReplyDate           AS CreditReplyDate              /* 与信返信日時 */
,      o.CombinedClaimParentFlg    AS CombinedClaimParentFlg       /* 取りまとめ後に親となったフラグ */
,      o.CombinedClaimTargetStatus AS CombinedClaimTargetStatus    /* 取りまとめ対象注文ステータス */
,      a.CreditTransferRequestFlg  AS CreditTransferRequestFlg     /* 口座振替利用フラグ  */
,      e.CreditTransferFlg         AS CreditTransferFlg            /* 口座振替フラグ */
,      e.BillingAgentFlg           AS BillingAgentFlg              /* 請求代行プランフラグ */
,      a.ExtraPayType              AS ExtraPayType                 /* 追加支払い方法_区分 */
,      a.ExtraPayKey               AS ExtraPayKey                  /* 追加支払い方法_鍵 */
,      (SELECT OrderId FROM T_Order WHERE OrderSeq = P_OrderSeq AND P_OrderSeq = o.P_OrderSeq) AS ParentOrderId /* 取りまとめ注文代表 */
,      ''                          AS CustStsStr                   /* 顧客ステータス文字列 */
,      (SELECT TaxClass FROM T_Enterprise WHERE EnterpriseId = voc.EnterpriseId) AS TaxClass /* 税区分 */
,      (SELECT IFNULL(SUM(UseAmount), 0) FROM T_Order WHERE P_OrderSeq = o.P_OrderSeq AND OrderSeq <> o.OrderSeq AND Cnl_Status = 0) AS ChildUseAmountSum /* 他取りまとめ額 */
,      o.Oem_OrderId               AS Oem_OrderId                  /* OEM用任意注文番号 */
,      o.Oem_Note                  AS Oem_Note                     /* OEM先備考 */
,      rc.DepositDate              AS DepositDate                  /* 収納代行会社から、CBの口座へ入金される予定日 */
,      mc.RemindStopFlg            AS RemindStopFlg                /* 督促ストップフラグ */
,      ec.EntCustSeq               AS EntCustSeq                     /* 顧客ID */
       /* 以下ボタン関連 */
,      mc.ManCustId                AS ManCustId                    /* 管理顧客番号 */
,      o.Cnl_Status                AS Cnl_Status                   /* キャンセルステータス */
,      mc.BlackFlg                 AS BlackFlg                     /* ブラックフラグ */
,      mc.GoodFlg                  AS GoodFlg                      /* 優良フラグ */
,      rc.ReceiptDate              AS Rct_ReceiptDate              /* 顧客入金日 */
,      rc.ReceiptClass             AS ReceiptClass                 /* 入金方法 */
,      (SELECT DispDecimalPoint FROM T_Enterprise WHERE EnterpriseId = voc.EnterpriseId) AS DispDecimalPoint    /* 表示用小数点桁数 */
,      o.P_OrderSeq                AS P_OrderSeq                   /* 親注文番号 */
,      o.Chg_NonChargeFlg          AS Chg_NonChargeFlg             /* 立替処理－立替対象外フラグ(0：対象,1：対象外) */
,      CASE WHEN cc.ClaimAmount IS NULL THEN (SELECT SUM(UseAmount) FROM T_Order WHERE P_OrderSeq = o.P_OrderSeq AND Cnl_Status = 0)
            ELSE cc.ClaimAmount
       END  AS ClaimAmount                                         /* 請求金額 */
,      pas.PayingControlStatus     AS PayingControlStatus          /* 本締め／仮締め区分 */
,      o.Incre_JudgeScoreTotal     AS Incre_JudgeScoreTotal        /* 社内与信－スコア合計（審査共通） */
,      vcr.ReceiptDate             AS CloseReceiptDate             /* クローズ入金日 */
,      ec.RequestStatus            AS RequestStatus                /* 申込ステータス */
,      cc.CreditTransferFlg        AS CCCreditTransferFlg          /* 口座振替サービス */

FROM   V_OrderCustomer voc
       INNER JOIN T_Order o ON (o.OrderSeq = voc.OrderSeq)
       INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq)
       LEFT OUTER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.P_OrderSeq)
       LEFT OUTER JOIN T_ReceiptControl rc ON (rc.ReceiptSeq = cc.LastReceiptSeq)
       STRAIGHT_JOIN AT_Order a ON (a.OrderSeq = o.OrderSeq)
       STRAIGHT_JOIN T_Enterprise e ON (e.EnterpriseId = o.EnterpriseId)
       LEFT OUTER JOIN T_MailSendHistory msh ON (msh.OrderSeq = o.OrderSeq)
       LEFT OUTER JOIN T_EnterpriseCustomer ec ON (ec.EntCustSeq = c.EntCustSeq)
       LEFT OUTER JOIN T_ManagementCustomer mc ON (mc.ManCustId = ec.ManCustId)
	   LEFT OUTER JOIN T_PayingAndSales pas ON (pas.OrderSeq = voc.OrderSeq)
       LEFT OUTER JOIN V_CloseReceiptControl vcr ON (vcr.OrderSeq = cc.OrderSeq)
WHERE  1 = 1
AND    voc.OrderSeq = :OrderSeq;
EOQ;
	    return $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq));
	}

	/**
	 * 顧客スタータス文字列を生成
	 *
	 * @param int $oseq 注文SEQ
	 * @return string 顧客スタータス文字列
	 */
	private function makeCustStsStr($oseq)
	{
        $sql = <<<EOQ
SELECT IFNULL(mc.GoodFlg, 0) AS GoodFlg
,      IFNULL(mc.BlackFlg, 0) AS BlackFlg
,      IFNULL(mc.IdentityDocumentFlg, 0) AS IdentityDocumentFlg
FROM   T_Order o
       INNER JOIN T_Customer c ON (o.OrderSeq = c.OrderSeq)
       INNER JOIN T_EnterpriseCustomer ec ON (c.EntCustSeq = ec.EntCustSeq)
       INNER JOIN T_ManagementCustomer mc ON (ec.ManCustId = mc.ManCustId)
WHERE  1 = 1
AND    o.OrderSeq = :OrderSeq;
EOQ;
        $row = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();

        $ar = array();
        if ($row['GoodFlg'] == 1) { $ar[] = '優良顧客'; }
        if ($row['BlackFlg'] == 1) { $ar[] = 'ブラック顧客'; }
        if ($row['IdentityDocumentFlg'] == 1) { $ar[] = '身分証アップロード済'; }

        return implode( '、',$ar);
	}

	/**
	 * 注文詳細更新
	 */
	public function detailupAction()
	{
        $params = array_merge($this->params()->fromRoute(), $this->params()->fromPost());

        $oseq = isset($params['OrderSeq']) ? $params['OrderSeq'] : 0;
        if ($oseq == 0)
        {
            // 注文Seqが指定されていなければ、エラーページに飛ばす。
            return $this->_redirect('error/nop');
        }

        try {
            $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();

            // ユーザーIDの取得
            $obj = new \models\Table\TableUser($this->app->dbAdapter);
            $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

            // 現在のデータを取得
            $mdlvoc = new ViewOrderCustomer($this->app->dbAdapter);
            $oc = $mdlvoc->findOrderCustomerByOrderSeq($oseq)->current();

            // 更新データ－注文情報
            $uOrder['RemindClass']              = $params['RemindClass'];
            $uOrder['TouchHistoryFlg']          = (isset($params['TouchHistoryFlg'])) ? 1 : 0;
            $uOrder['BriefNote']                = $params['BriefNote'];
            $uOrder['LonghandLetter']           = $params['LonghandLetter'];
            $uOrder['VisitFlg']                 = (isset($params['VisitFlg'])) ? 1 : 0;
            $uOrder['Chg_NonChargeFlg']         = (isset($params['Chg_NonChargeFlg'])) ? 1 : 0;
            $uOrder['FinalityCollectionMean']   = $params['FinalityCollectionMean'];
            if ($params['FinalityRemindDate'] == '') {
                $nullColumns[] = 'FinalityRemindDate';
            }
            else {
                $uOrder['FinalityRemindDate']   = $params['FinalityRemindDate'];
            }
            if ($params['FinalityRemindOpId'] != 'NA') {
                $uOrder['FinalityRemindOpId']   = $params['FinalityRemindOpId'];
            }
            $uOrder['UpdateId']                 = $userId;
            // DATETIME型のNULL対応
            if ($params['PromPayDate'] == '') {
                $nullColumns[] = 'PromPayDate';
            }
            $uOrder['PromPayDate']              = $params['PromPayDate'];
            // DATETIME型のNULL対応
            if ($params['ClaimStopReleaseDate'] == '') {
                $nullColumns[] = 'ClaimStopReleaseDate';
            }
            $uOrder['ClaimStopReleaseDate']     = $params['ClaimStopReleaseDate'];
            $uOrder['InstallmentPlanAmount']    = $params['InstallmentPlanAmount'];
            $uOrder['Incre_Note']               = $params['Note'];
            //OEM先備考更新
            $uOrder['Oem_Note']                 = $params['OmeNote'];
            $uOrder['LetterClaimStopFlg']       = (isset($params['LetterClaimStopFlg'])) ? 1 : 0;
            $uOrder['MailClaimStopFlg']         = (isset($params['MailClaimStopFlg'])) ? 1 : 0;
            //架電
            $uOrder['Tel30DaysFlg']             = (isset($params['Tel30DaysFlg'])) ? 1 : 0;
            $uOrder['Tel90DaysFlg']             = (isset($params['Tel90DaysFlg'])) ? 1 : 0;

            // 更新データ－購入者情報
            $uCustomer['Carrier'] = $params['Carrier'];
            $uCustomer['ValidTel'] = $params['ValidTel'];
            $uCustomer['ValidMail'] = $params['ValidMail'];
            $uCustomer['ValidAddress'] = $params['ValidAddress'];
            $uCustomer['ResidentCard'] = $params['ResidentCard'];
            $uCustomer['Cinfo1'] = $params['Cinfo1'];
            $uCustomer['CinfoNote1'] = $params['CinfoNote1'];
            $uCustomer['CinfoStatus1'] = $params['CinfoStatus1'];
            $uCustomer['Cinfo2'] = $params['Cinfo2'];
            $uCustomer['CinfoNote2'] = $params['CinfoNote2'];
            $uCustomer['CinfoStatus2'] = $params['CinfoStatus2'];
            $uCustomer['Cinfo3'] = $params['Cinfo3'];
            $uCustomer['CinfoNote3'] = $params['CinfoNote3'];
            $uCustomer['CinfoStatus3'] = $params['CinfoStatus3'];
            $uCustomer['UpdateId'] = $userId;

            // 注文データ更新実行
            $mdlo = new TableOrder($this->app->dbAdapter);
            $mdlo->saveUpdateParts($uOrder, $oseq);

            // count関数対策
            if (!empty($nullColumns)) {
                $mdlo->setNullValue($nullColumns, $oseq);
            }

            // 購入者更新実行
            $mdlcus = new TableCustomer($this->app->dbAdapter);
            $mdlcus->saveUpdate($uCustomer, $oc['CustomerId']);

            // 注文検索サマリーテーブルの更新　2008.02.01
            $mdlos = new TableOrderSummary($this->app->dbAdapter);
            $mdlos->updateSummary($oseq, $userId);

            // 注文_会計テーブル更新実行
            $mdlato = new ATableOrder($this->app->dbAdapter);
            $rowato = $mdlato->find($oseq)->current();
            $uAtOrder['RepayTCFlg'] = (isset($params['RepayTCFlg'])) ? 1 : 0;
            $uAtOrder['RepayPendingFlg'] = (isset($params['RepayPendingFlg'])) ? 1 : 0;
            $uAtOrder['StopSendMailConfirmJournalFlg'] = (isset($params['StopSendMailConfirmJournalFlg'])) ? 1 : 0;
            $mdlato->saveUpdate($uAtOrder, $oseq);

            // 注文履歴登録
            $history = new CoralHistoryOrder($this->app->dbAdapter);
            if ($rowato['RepayPendingFlg'] == 1 && $uAtOrder['RepayPendingFlg'] == 0) {
                // 返金保留⇒保留解除
                $history->InsOrderHistory($oseq, 116, $userId);

            }elseif ($rowato['RepayPendingFlg'] == 0 && $uAtOrder['RepayPendingFlg'] == 1) {
                // 保留解除⇒返金保留
                $history->InsOrderHistory($oseq, 115, $userId);

            } else {
                // 何もしない
            }

            $this->app->dbAdapter->getDriver()->getConnection()->commit();
        }
        catch (\Exception $e) {
            $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
            if (strstr($e->getMessage(), 'Lock wait timeout')) {
                $_SESSION['isLockWaitTimeOut'] = true;
            }
        }

        // リダイレクトURL
        $url = 'rworder/detail/oseq/' . $oseq;

        // 不払い検索ハッシュがあったら付加しておく
        $content_hash  = isset($params['content_hash']) ? $params['content_hash'] : false;
        $content_index = isset($params['idx']) ? $params['idx'] : false;

        if( $content_hash && $content_index !== false ) {
            $url .= '/content_hash/' . $content_hash . '/idx/' . $content_index;
        }

        return $this->_redirect($url);
	}


	/**
	 * 初回請求書再発行
	 */
	public function reissueformAction()
	{
        $params = array_merge($this->params()->fromRoute(), $this->params()->fromPost());

        $oseq = isset($params['oseq']) ? $params['oseq'] : 0;

        $mdloc = new ViewOrderCustomer($this->app->dbAdapter);
        $oc = $mdloc->findOrderCustomerByOrderSeq($oseq, true)->current();

        // 日付関連
        $oc["ReceiptOrderDate"] = date('m/d', strtotime($oc["ReceiptOrderDate"]));  // 注文日
        $oc["Clm_F_ClaimDate"]  = date('m/d', strtotime($oc["Clm_F_ClaimDate"]));   // 初回請求日
        $oc["Clm_L_ClaimDate"]  = date('m/d', strtotime($oc["Clm_L_ClaimDate"]));   // 最終請求日

        $oc["UnitingAddress"]   = mb_substr($oc["UnitingAddress"], 0, 8, 'UTF-8');

        // 請求額
        // 利用額　＋　遅延損害金　＋　請求手数料　＋　追加請求手数料
        $oc['ClaimAmount']  = $oc['UseAmount']
                            + $oc['Clm_L_DamageInterestAmount']
                            + $oc['Clm_L_ClaimFee']
                            + $oc['Clm_L_AdditionalClaimFee'];

        $this->view->assign('list', $oc);
        return $this->view;
	}

	/**
	 * 初回請求再発行予約実行
	 */
	public function reissuedoneAction()
	{
        $params = array_merge($this->params()->fromRoute(), $this->params()->fromPost());

        $oseq = isset($params['OrderSeq']) ? $params['OrderSeq'] : 0;

        try {
            // SMBCバーチャル口座オープン用にロック獲得を試行
            $mdlo = new TableOrder($this->app->dbAdapter);
            $lockItem = $this->getLockItemForSmbcpaAccount($mdlo->find($oseq)->current());

            // ユーザーIDの取得
            $obj = new \models\Table\TableUser($this->app->dbAdapter);
            $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

            // 請求管理更新
            $sql = " UPDATE T_ClaimControl SET ReissueClass = 1, UpdateId = :UpdateId, UpdateDate = :UpdateDate WHERE OrderSeq = :OrderSeq ";
            $stm = $this->app->dbAdapter->query($sql);
            $stm->execute(array(':OrderSeq' => $oseq, ':UpdateId' => $userId, ':UpdateDate' => date('Y-m-d H:i:s')));

            // 注文更新
            $addNote = sprintf("（初回請求書再発行確定[%s]）\n----\n", date('Y-m-d H:i:s'));
            $sql = " UPDATE T_Order SET Incre_Note = CONCAT('$addNote', Incre_Note), UpdateId = :UpdateId, UpdateDate = :UpdateDate, LetterClaimStopFlg = 0, MailClaimStopFlg = 0 WHERE OrderSeq = :OrderSeq ";
            $stm = $this->app->dbAdapter->query($sql);
            $stm->execute(array(':OrderSeq' => $oseq, ':UpdateId' => $userId, ':UpdateDate' => date('Y-m-d H:i:s')));

            // 請求履歴更新
            $sql = " UPDATE T_ClaimHistory SET MailFlg = 1 WHERE OrderSeq = :OrderSeq AND MailFlg = 0 AND ValidFlg = 1 ";
            $stm = $this->app->dbAdapter->query($sql);
            $stm->execute(array(':OrderSeq' => $oseq));

            // ロックを獲得していたら開放
            try {
                if($lockItem) {
                    $lockItem->terminate();
                }
            }
            catch (\Exception $e) { ; }

            return $this->_redirect('rworder/detail/oseq/' . $oseq);
        }
        catch (\Exception $e) {

            // ロックを獲得していたら開放
            try {
                if($lockItem) {
                    $lockItem->terminate();
                }
            }
            catch (\Exception $e) { ; }

            // 処理されなかったので、リダイレクトせずにデフォルトビューでその旨を表示する。
            $this->view->assign('urlDetail', 'rworder/detail/oseq/' . $oseq);
            $this->view->assign('error', $e);

            return $this->view;
        }
	}

	/**
	 * キャンセル処理（申請）フォーム
	 */
	public function precancelformAction()
	{
        $params = array_merge($this->params()->fromRoute(), $this->params()->fromPost());

        $oseq = isset($params['oseq']) ? $params['oseq'] : 0;

        $mdloc = new ViewOrderCustomer($this->app->dbAdapter);
        $oc = $mdloc->findOrderCustomerByOrderSeq($oseq, true)->current();

        // 日付関連
        $oc["ReceiptOrderDate"] = date('m/d', strtotime($oc["ReceiptOrderDate"]));  //注文日
        $oc["Clm_F_ClaimDate"]  = date('m/d', strtotime($oc["Clm_F_ClaimDate"]));   //初回請求日
        $oc["Clm_L_ClaimDate"]  = date('m/d', strtotime($oc["Clm_L_ClaimDate"]));   //最終請求日

        $oc["UnitingAddress"] = mb_substr($oc["UnitingAddress"], 0, 8, 'UTF-8');

        // 請求額
        // 利用額　＋　遅延損害金　＋　請求手数料　＋　追加請求手数料
        $oc['ClaimAmount']  = $oc['UseAmount']
                            + $oc['Clm_L_DamageInterestAmount']
                            + $oc['Clm_L_ClaimFee']
                            + $oc['Clm_L_AdditionalClaimFee'];

        // キャンセル区分
        $oc['CancelType'] = BaseHtmlUtils::InputRadioTag(
            'CancelType',
            array(0 => '通常', 1 => '債権返却'),
            '0');

        $this->view->assign('list', $oc);

        // 不払い検索キャッシュの指定がある場合はビューに割り当てる
        $content_hash  = isset($params['content_hash']) ? $params['content_hash'] : false;
        $content_index = isset($params['idx']) ? $params['idx'] : false;
        if( $content_hash && $content_index !== false ) {
            $this->view->assign( 'hash', $content_hash );
            $this->view->assign( 'index_in_cache', $content_index );
        }

        // キャンセル理由
        $obj = new CoralCodeMaster($this->app->dbAdapter);
        $cancelReasonList = $obj->getCancelReasonMaster();
        unset($cancelReasonList[-1]);
        $this->view->assign('cancelReasonListTag',BaseHtmlUtils::SelectTag("cancelReasonList", $cancelReasonList , 1));
        $lgCancel = new LogicCancel($this->app->dbAdapter);
        $this->view->assign( 'usedTodo2Pay', $lgCancel->_usedTodo2Pay($oseq));
        return $this->view;
	}

	/**
	 * キャンセル処理（申請）
	 */
	public function precanceldoneAction()
	{
        $params = array_merge($this->params()->fromRoute(), $this->params()->fromPost());

        $oseq = isset($params['OrderSeq']) ? $params['OrderSeq'] : 0;
        $reason = isset($params['CancelReason']) ? $params['CancelReason'] : '';
        $type = isset($params['CancelType']) ? $params['CancelType'] : '';
        $reasonCode = isset($params['cancelReasonList']) ? $params['cancelReasonList'] : 1;
        $mail = new CoralMail($this->app->dbAdapter, $this->app->mail['smtp']);

        // ユーザーIDの取得
        $obj = new \models\Table\TableUser($this->app->dbAdapter);
        $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

        if ($oseq > 0) {
            $mdlo = new TableOrder($this->app->dbAdapter);
            $oc = $mdlo->find($oseq)->current();

            if ($oc['Cnl_Status'] > 0) {
                $msg = '既にキャンセル処理が行われています。';
            }
            else {
                $lgCancel = new LogicCancel($this->app->dbAdapter);
                //add $isToDo
                $isToDo = 0;
                if ($lgCancel->_usedTodo2Pay($oseq) == true) {
                    $isToDo = 1;
                }
                try {
                    $reasonSbps = $lgCancel->applies($oseq, $reason, $reasonCode, $type, true, $userId, $isToDo, Application::getInstance()->sbpsLogger);
                    if ($reasonSbps == "") {
                        if ($lgCancel->_usedTodo2Pay($oseq) == false) {
                            $msg = 'キャンセル処理（申請）を行いました。';
                        } else {
                            if ( $isToDo == 1) {
                                // 事業者へ送るメールなので、画面上の表示単位で送信することとする
                                $mail->SendCancelMail($oseq, $userId);
                            }
                            $msg = 'キャンセル処理（確定）を行いました。';
                        }
                    } else {
                        if (strpos($reasonSbps, "__sbps") !== false) {
                            $temp = explode('__sbps', $reasonSbps);
                            $msg = "SBPS側でエラーが発生しました。エラーコードは「" . $temp[0] . "」です。".'<br>';
                            $msg .= $lgCancel->_SBPaymentMakeErrorInfo($temp[0]);
                            $msg = str_replace(PHP_EOL, '<br>', $msg ) .'<br>';
                        } else {
                            $msg = $reasonSbps .'<br>';
                        }
                    }
                } catch(OrderCancelException $cancelError) {
                    $msg = $cancelError->getMessage();
                }
            }

            $urlDetail = 'rworder/detail/oseq/' . $oseq;
            // 不払い検索キャッシュの指定がある場合はURLに追加
            $content_hash  = isset($params['content_hash']) ? $params['content_hash'] : false;
            $content_index = isset($params['idx']) ? $params['idx'] : false;
            if( $content_hash && $content_index !== false ) {
                $urlDetail .= '/content_hash/' . $content_hash . '/idx/' . $content_index;
            }
            $urlCaption = '詳細画面へ';
        }
        else {
            $urlDetail = '';
            $urlCaption = 'トップへ';
            $msg = "無効な注文Seqが渡されました。";
        }
        $lgCancel = new LogicCancel($this->app->dbAdapter);
        $this->view->assign( 'usedTodo2Pay', $lgCancel->_usedTodo2Pay($oseq));
        $this->view->assign('urlDetail', $urlDetail);
        $this->view->assign('urlCaption', $urlCaption);
        $this->view->assign('msg', $msg);

        return $this->view;
	}

	/**
	 * キャンセル処理（申請取消し）フォーム
	 */
	public function cancelcancelformAction()
	{
        $params = $this->getParams();

        $oseq = isset($params['oseq']) ? $params['oseq'] : 0;

        $mdloc = new ViewOrderCustomer($this->app->dbAdapter);
        $oc = $mdloc->findOrderCustomerByOrderSeq($oseq, true)->current();

        // 日付関連
        $oc["ReceiptOrderDate"] = date('m/d', strtotime($oc["ReceiptOrderDate"]));  //注文日
        $oc["Clm_F_ClaimDate"]  = date('m/d', strtotime($oc["Clm_F_ClaimDate"]));   //初回請求日
        $oc["Clm_L_ClaimDate"]  = date('m/d', strtotime($oc["Clm_L_ClaimDate"]));   //最終請求日

        $oc["UnitingAddress"] = mb_substr($oc["UnitingAddress"], 0, 8, 'UTF-8');

        // 請求額
        // 利用額　＋　遅延損害金　＋　請求手数料　＋　追加請求手数料
        $oc['ClaimAmount']  = $oc['UseAmount']
        + $oc['Clm_L_DamageInterestAmount']
        + $oc['Clm_L_ClaimFee']
        + $oc['Clm_L_AdditionalClaimFee'];

        // キャンセル区分
        $oc['CancelType'] = BaseHtmlUtils::InputRadioTag(
            'CancelType',
            array(0 => '通常', 1 => '債権返却'),
            '0',false, true);

        $this->view->assign('list', $oc);

        // 不払い検索キャッシュの指定がある場合はビューに割り当てる
        $content_hash  = isset($params['content_hash']) ? $params['content_hash'] : false;
        $content_index = isset($params['idx']) ? $params['idx'] : false;
        if( $content_hash && $content_index !== false ) {
            $this->view->assign( 'hash', $content_hash );
            $this->view->assign( 'index_in_cache', $content_index );
        }

        // キャンセル理由
        $sql = " SELECT CancelReason, (SELECT KeyContent FROM M_Code WHERE CodeId = 90 AND KeyCode = CancelReasonCode) AS CancelReasonCodeValue FROM T_Cancel WHERE OrderSeq = :OrderSeq AND ValidFlg = 1 ";
        $row = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();
        if ($row) {
            $this->view->assign('CancelReason', $row['CancelReason']);
            $this->view->assign('CancelReasonCodeValue', $row['CancelReasonCodeValue']);
        }

        return $this->view;
	}

	/**
	 * キャンセル処理（申請取消し）
	 */
	public function cancelcanceldoneAction()
	{
        $params = $this->getParams();

        $oseq = isset($params['OrderSeq']) ? $params['OrderSeq'] : 0;
        $reason = isset($params['CancelReason']) ? $params['CancelReason'] : '';
        $type = isset($params['CancelType']) ? $params['CancelType'] : '';

        // ユーザーIDの取得
        $obj = new \models\Table\TableUser($this->app->dbAdapter);
        $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

        if ($oseq > 0) {
            $mdlo = new TableOrder($this->app->dbAdapter);
            $oc = $mdlo->find($oseq)->current();

            if ($oc['Cnl_Status'] != 1) {
                $msg = 'キャンセル申請中（確認待ち）以外は処理できません。';
            }
            else {
                $lgCancel = new LogicCancel($this->app->dbAdapter);
                $msg = $lgCancel->cancelApplies($oseq, true, $userId);

                if ($msg == "") {
                    $msg = 'キャンセル処理（申請）を取消しました。';
                }
            }

            $urlDetail = 'rworder/detail/oseq/' . $oseq;
            // 不払い検索キャッシュの指定がある場合はURLに追加
            $content_hash  = isset($params['content_hash']) ? $params['content_hash'] : false;
            $content_index = isset($params['idx']) ? $params['idx'] : false;
            if( $content_hash && $content_index !== false ) {
                $urlDetail .= '/content_hash/' . $content_hash . '/idx/' . $content_index;
            }
            $urlCaption = '詳細画面へ';
        }
        else {
            $urlDetail = '';
            $urlCaption = 'トップへ';
            $msg = "無効な注文Seqが渡されました。";
        }

        $this->view->assign('urlDetail', $urlDetail);
        $this->view->assign('urlCaption', $urlCaption);
        $this->view->assign('msg', $msg);

        return $this->view;

	}

	/**
	 * 編集画面
	 */
	public function editformAction()
	{
        $params = array_merge($this->params()->fromRoute(), $this->params()->fromPost());

        $oseq = isset($params['oseq']) ? $params['oseq'] : 0;
        if ($oseq == 0)
        {
            // 注文Seqが指定されていなければ、エラーページに飛ばす。
            return $this->_redirect('error/nop');
        }

        // 注文・購入者情報
        $orderCustomer = $this->app->dbAdapter->query($this->getEditBaseSql())->execute(array(':OrderSeq' => $oseq))->current();

        //注文とOEMをjoinしてOEMの名前取得
        $sql  = " SELECT OEM.OemId, OEM.OemNameKj ";
        $sql .= " FROM   T_Oem OEM LEFT JOIN T_Order ORD ON ORD.OemId = OEM.OemId ";
        $sql .= " WHERE  ORD.OrderSeq = :OrderSeq ";

        $stm = $this->app->dbAdapter->query($sql);

        $oem_name = $stm->execute(array(':OrderSeq' => $oseq))->current();

        //取得に失敗した場合はセットしない
        if($oem_name){
            // OEM情報
            $this->view->assign('oem', $oem_name);
        }

        // 配送情報
        $mdldeli = new ViewDelivery($this->app->dbAdapter);
        $deli = $mdldeli->findDelivery(array('OrderSeq' => $oseq, 'DataClass' => 1))->current();

        // 商品情報
        $mdloi = new TableOrderItems($this->app->dbAdapter);
        $deliveryFee = 0;
        $settlementFee = 0;
        $totalSumMoney = 0;
        $itemsNeta = $mdloi->findByOrderSeq($oseq);

        // マスター関連クラス
        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);

        foreach ($itemsNeta as $item)
        {
            switch((int)$item['DataClass'])
            {
                case 2:	// 送料
                    $deliveryFee = $item['SumMoney'];
                    break;
                case 3:	// 手数料
                    $settlementFee = $item['SumMoney'];
                    break;
                case 4:	// 外税額
                    $exTax = $item['SumMoney'];
                    break;
                default:
                    $items[] = $item;
                    $totalSumMoney += $item['SumMoney'];
                    break;
            }
        }

        // 商品の合計＋送料＋手数料＋外税額
        $totalSumMoney += $deliveryFee + $settlementFee + $exTax;

        // 配送方法タグ
        $mdldm = new TableDeliMethod($this->app->dbAdapter);
        $mrows = $mdldm->getValidAll();

        $mitems[0] = '-----';
        if (isset($mrows))
        {
            foreach ($mrows as $mrow)
            {
                $mitems[$mrow['DeliMethodId']] = $mrow['DeliMethodName'];
            }
        }

        $deliveryMethodTag = BaseHtmlUtils::SelectTag(
            "Deli_DeliveryMethod",
            $mitems,
            $deli['Deli_DeliveryMethod'],
            'style="font-size: 11px;" class="journal"'
        );

        // 加工
        // ステータス
        $custom["DataStatus"] = $codeMaster->getDataStatusCaption($orderCustomer['DataStatus']);

        $mdlato = new ATableOrder($this->app->dbAdapter);
        $atOdr = $mdlato->find($oseq)->current();

        // アサイン
        $this->view->assign('oc', $orderCustomer);
        $this->view->assign('deli', $deli);
        $this->view->assign('items', $items);
        $this->view->assign('custom', $custom);                             // 表示用に加工したデータの連想配列
        $this->view->assign('deliveryFee', $deliveryFee);                   // 送料
        $this->view->assign('settlementFee', $settlementFee);               // 手数料
        $this->view->assign('exTax', $exTax);                               // 外税額
        $this->view->assign('totalSumMoney', $totalSumMoney);               // 商品の合計金額
        $this->view->assign('deliveryMethodTag', $deliveryMethodTag);       // 配送方法
        $this->view->assign("claimSendingClassTag",
            BaseHtmlUtils::SelectTag('claimSendingClass',array(11 => '同梱', 12 => '別送'),
            ($orderCustomer['ClaimSendingClass'] == 11) ? 11 : 12,
            'style="font-size: 11px;"'
        ));// 同梱別送ﾌﾗｸﾞタグ
        $this->view->assign("userInfo",$this->app->authManagerAdmin->getUserInfo());
        $this->addJavaScript( '../js/corelib.js' );
        // 口座振替利用
        $this->view->assign("creditTransferRequestFlgTag"
                          , BaseHtmlUtils::SelectTag('CreditTransferRequestFlg',array(0 => '利用しない', 1 => '利用する（WEB申込み）', 2 => '利用する（紙面申込み）')
                          , (isset($orderCustomer['CreditTransferRequestFlg']) ? $orderCustomer['CreditTransferRequestFlg'] : 0)
                          , 'style="font-size: 11px;"'
        ));

        // 不払い検索キャッシュの指定がある場合はビューに割り当てる
        $content_hash  = isset($params['content_hash']) ? $params['content_hash'] : false;
        $content_index = isset($params['idx']) ? $params['idx'] : false;
        if( $content_hash && $content_index !== false ) {
            $this->view->assign( 'hash', $content_hash );
            $this->view->assign( 'index_in_cache', $content_index );
        }
        $this->view->assign('ao', $atOdr);

        return $this->view;
	}

	/**
	 * 編集確定
	 *
	 */
	public function editdoneAction()
	{
        $params = array_merge($this->params()->fromRoute(), $this->params()->fromPost());

        $oseq = isset($params['OrderSeq']) ? $params['OrderSeq'] : 0;
        if ($oseq == 0)
        {
            // 注文Seqが指定されていなければ、エラーページに飛ばす。
            return $this->_redirect('error/nop');
        }

        // ユーザーIDの取得
        $obj = new \models\Table\TableUser($this->app->dbAdapter);
        $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

        try {
            $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();

            // 現在のデータを取得
            $mdlvoc = new ViewOrderCustomer($this->app->dbAdapter);
            $oc = $mdlvoc->findOrderCustomerByOrderSeq($oseq)->current();
            $mdldeli = new ViewDelivery($this->app->dbAdapter);
            $delis = $mdldeli->findByOrderSeq($oseq);

            $mghelper = new LogicMergeOrderHelper($this->app->dbAdapter, $oseq);

            // 請求先
            if (isset($params['A_NameKj'])) {

                $uCustomer['NameKj'] = $params['A_NameKj'];
                $uCustomer['NameKn'] = $params['A_NameKn'];
                $uCustomer['PostalCode'] = $params['A_PostalCode'];
                $uCustomer['UnitingAddress'] = $params['A_UnitingAddress'];
                $uCustomer['Phone'] = $params['A_Phone'];
                $uCustomer['MailAddress'] = $params['A_MailAddress'];
                $uCustomer['EntCustId'] = $params['A_EntCustId'];           // 顧客番号
                $uCustomer['Occupation'] = $params['A_Occupation'];         // 職業
                $uCustomer['CorporateName'] = $params['A_CorporateName'];   // 法人名
                $uCustomer['DivisionName'] = $params['A_DivisionName'];     // 部署名
                $uCustomer['CpNameKj'] = $params['A_CpNameKj'];             // 担当者名
                $uCustomer['UpdateId'] = $userId;                           // 更新者

                $mdlpostalcode = new \models\Table\TablePostalCode($this->app->dbAdapter);
                $uCustomer['AddressKn'] = $mdlpostalcode->getAddressKanaStr($params['A_PostalCode']);   // 住所カナ

                // 別配送先
                $uOrder['AnotherDeliFlg'] = (isset($params['AnotherDeliFlg'])) ? 1 : 0;

                // 別管理
                $uOrder['Bekkan'] = (isset($params['Bekkan'])) ? 1 : 0;

                // 請求ストップ
                $uOrder['StopClaimFlg'] = (isset($params['StopClaimFlg'])) ? 1 : 0;

                // 戻り請求書
                $uOrder['ReturnClaimFlg'] = (isset($params['ReturnClaimFlg'])) ? 1 : 0;

                // 戻り請求書
                // → 追加フィールド"NoUpdateOutOfAmends"が設定されていたら処理しない（2011.6.8 eda）
                if ( !(isset($params['NoUpdateOutOfAmends']) ? $params['NoUpdateOutOfAmends'] : false) ) {
                    $uOrder['OutOfAmends'] = (isset($params['OutOfAmends'])) ? 1 : 0;
                }

                // 請求取りまとめステータスの設定 20131205 tkaki
                // 補償外案件の状況によって請求取りまとめを更新する
                if($mghelper->chkCcTargetStatusByOutOfAmends($uOrder['OutOfAmends']) != 9) {
                    $uOrder['CombinedClaimTargetStatus'] = $mghelper->chkCcTargetStatusByOutOfAmends($uOrder['OutOfAmends']);
                }

                // TODO: kashira - PrefectureCodeの判定・セット
                // TODO: kashira - 住所の「修正歴あり」の検討

                // 購入者更新実行
                $mdlcus = new TableCustomer($this->app->dbAdapter);
                $mdlcus->saveUpdate($uCustomer, $oc['CustomerId']);
            }

            // 配送先
            if (isset($params['AnotherDeliFlg'])) {
                // 別配送先が指定されているので、ポストデータをそのまま採用する。
                $uDeli['DestNameKj'] = $params['B_NameKj'];
                $uDeli['DestNameKn'] = $params['B_NameKn'];
                $uDeli['PostalCode'] = $params['B_PostalCode'];
                $uDeli['UnitingAddress'] = $params['B_UnitingAddress'];
                $uDeli['Phone'] = $params['B_Phone'];

                // TODO: kashira - PrefectureCodeの判定・セット
                // TODO: kashira - 住所の「修正歴あり」の検討
            }
            else {
                // 配送先は請求先と同一なので、元のデータあるいは請求先のポストデータを採用する。
                if (isset($params['A_NameKj'])) {
                    $uDeli['DestNameKj'] = $params['A_NameKj'];
                    $uDeli['DestNameKn'] = $params['A_NameKn'];
                    $uDeli['PostalCode'] = $params['A_PostalCode'];
                    $uDeli['UnitingAddress'] = $params['A_UnitingAddress'];
                    $uDeli['Phone'] = $params['A_Phone'];
                }
                else {
                    $uDeli['DestNameKj'] = $oc['NameKj'];
                    $uDeli['DestNameKn'] = $oc['NameKn'];
                    $uDeli['PostalCode'] = $oc['PostalCode'];
                    $uDeli['UnitingAddress'] = $oc['UnitingAddrss'];
                    $uDeli['Phone'] = $oc['Phone'];
                }
            }

            // 配送先更新実行
            $mdldd = new TableDeliveryDestination($this->app->dbAdapter);
            foreach ($delis as $deli) {
                $uDeli['UpdateId'] = $userId;   // 更新者
                $mdldd->saveUpdate($uDeli, $deli['DeliDestId']);
            }

            // 配送方法・伝票番号
            $mdloi = new TableOrderItems($this->app->dbAdapter);
            if (isset($params['Deli_DeliveryMethod']))
            {
                $mdloi->updateJournal($oseq, $params['Deli_DeliveryMethod'], $params['Deli_JournalNumber'], $userId);

                // 請求取りまとめステータスの設定 20131205 tkaki
                // 補償外案件の状況によって請求取りまとめを更新する
                if($mghelper->chkCcTargetStatusByDelivery($params['Deli_DeliveryMethod']) != 9) {
                    $uOrder['CombinedClaimTargetStatus'] = $mghelper->chkCcTargetStatusByDelivery($params['Deli_DeliveryMethod']);
                }
            }

            // 購入商品
            $i = 0;
            $useAmount = 0;
            while (isset($params['OrderItemId' . $i]))
            {
                // TODO: kashira - 商品削除を検討
                $price = $params['UnitPrice' . $i] * $params['ItemNum' . $i];
                if ((int)$params['UseAmountFractionClass'] == 0) { $price = floor( $price ); }
                if ((int)$params['UseAmountFractionClass'] == 1) { $price = round( $price ); }
                if ((int)$params['UseAmountFractionClass'] == 2) { $price = ceil(  $price ); }
                $useAmount += $price;
                $mdloi->saveUpdate(
                    array(
                            'ItemNameKj' => $params['ItemNameKj' . $i],
                            'UnitPrice' => $params['UnitPrice'. $i],
                            'ItemNum' => $params['ItemNum' . $i],
                            'SumMoney' => $price,
                            'UpdateId' => $userId,
                    ),
                    $params['OrderItemId' . $i]
                );

                $i++;
            }

            // 送料
            if (isset($params['DeliveryFee']))
            {
                $useAmount += $params['DeliveryFee'];
                $mdloi->updateDeliveryFee($oseq, $params['DeliveryFee'], $userId);
            }

            // 手数料
            if (isset($params['SettlementFee']))
            {
                $useAmount += $params['SettlementFee'];
                $mdloi->updateSettlementFee($oseq, $params['SettlementFee'], $userId);
            }

            // 外税額
            if (isset($params['ExTax']))
            {
                $useAmount += $params['ExTax'];
                $mdloi->updateTax($oseq, $params['ExTax'], $userId);
            }

            if ($useAmount > 0)
            {
                $uOrder['UseAmount'] = $useAmount;
            }

            // 同梱判定ﾌﾗｸﾞ
            if (isset($params['claimSendingClass'])) { $uOrder['ClaimSendingClass'] = $params['claimSendingClass']; }

            // AnotherDeliFlg = 0　（請求先情報と配送先情報が同じ場合に更新する／一致しない場合には更新しない。）
            if ((isset($params['AnotherDeliFlg']))
                &&
                (($params['A_NameKj'] == $params['B_NameKj']) &&
                 ($params['A_NameKn'] == $params['B_NameKn']) &&
                 ($params['A_PostalCode'] == $params['B_PostalCode']) &&
                 ($params['A_UnitingAddress'] == $params['B_UnitingAddress']) &&
                 ($params['A_Phone'] == $params['B_Phone']))) {
                $uOrder['AnotherDeliFlg'] = 0;
            }

            // 注文データ更新実行
            $uOrder['UpdateId'] = $userId;
            $mdlo = new TableOrder($this->app->dbAdapter);
            $mdlo->saveUpdate($uOrder, $oseq);

            // 注文検索サマリーテーブルの更新　2008.02.01
            $mdlos = new TableOrderSummary($this->app->dbAdapter);
            $mdlos->updateSummary($oseq, $userId);

            // 注文_会計へ更新
            if (isset($params['CreditTransferRequestFlg'])) {
                $mdlao = new ATableOrder($this->app->dbAdapter);
                $aOrder = null;
                $aOrder['CreditTransferRequestFlg'] = $params['CreditTransferRequestFlg'];
                $mdlao->saveUpdate($aOrder, $oseq);
            }

            // 注文履歴へ登録
            $history = new CoralHistoryOrder($this->app->dbAdapter);
            $history->InsOrderHistory($oseq, 15, $userId);

            $aOrder['ResumeFlg'] = (isset($params['ResumeFlg'])) ? 1 : 0;

            if ($aOrder['ResumeFlg'] == 1) {
                $mdlao = new ATableOrder($this->app->dbAdapter);
                $aOrder['DefectFlg'] = 0;
                $mdlao->saveUpdate($aOrder, $oseq);
            }

            // 住所有効を更新
            if ($params['A_SendAddress'] != $params['A_UnitingAddress']){

                $sql = " SELECT ValidAddress FROM T_Customer WHERE CustomerId = :CustomerId ";
                $vaddress = $this->app->dbAdapter->query($sql)->execute( array(':CustomerId' => $params['CustomerId']))->current()['ValidAddress'];

                if ($vaddress == '3') {
                    $sql = " UPDATE T_Customer SET ValidAddress = 1 WHERE CustomerId = :CustomerId ";
                    $stm = $this->app->dbAdapter->query($sql);
                    $stm->execute(array(':CustomerId' => $params['CustomerId']));
                }
            }

            $this->app->dbAdapter->getDriver()->getConnection()->commit();
        }
        catch (\Exception $e) {
            $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
        }

        $url = 'rworder/detail/oseq/' . $oseq;

        // 不払い検索キャッシュの指定がある場合はビューに割り当てる
        $content_hash  = isset($params['content_hash']) ? $params['content_hash'] : false;
        $content_index = isset($params['idx']) ? $params['idx'] : false;
        if( $content_hash && $content_index !== false ) {
            $url .= '/content_hash/' . $content_hash . '/idx/' . $content_index;
        }

        return $this->_redirect( $url );
	}

	/**
	 * 編集画面で使用する基礎SQLを取得
	 *
	 *@return string 基礎SQL文字列
	 */
	private function getEditBaseSql()
    {
        $sql = <<<EOQ
SELECT  odr.OrderSeq                    AS OrderSeq                     /* 注文SEQ */
,       odr.OrderId                     AS OrderId                      /* 注文ID */
,       odr.RegistDate                  AS RegistDate                   /* 注文登録日時 */
,       odr.ReceiptOrderDate            AS ReceiptOrderDate             /* ご注文日 */
,       odr.DataStatus                  AS DataStatus                   /* データステータス（表示用ステータス取得にも使用する） */
,       odr.UseAmount                   AS UseAmount                    /* 利用額 */
,       cst.NameKj                      AS NameKj                       /* 請求先氏名 */
,       cst.NameKn                      AS NameKn                       /* 請求先氏名カナ */
,       cst.PostalCode                  AS PostalCode                   /* 請求先郵便番号 */
,       cst.UnitingAddress              AS UnitingAddress               /* 請求先住所 */
,       cst.Phone                       AS Phone                        /* 請求先電話番号 */
,       cst.MailAddress                 AS MailAddress                  /* 請求先メールアドレス */
,       odr.AnotherDeliFlg              AS AnotherDeliFlg               /* 別配送先指定用 */
,       ent.EnterpriseId                AS EnterpriseId                 /* URL設定用 */
,       ent.EnterpriseNameKj            AS EnterpriseNameKj             /* 事業者名 */
,       si.SiteNameKj                   AS SiteNameKj                   /* サイト名 */
,       ent.CpNameKj                    AS CpNameKj                     /* 担当者名 */
,       ent.MailAddress                 AS EntMailAddress               /* 担当者メールアドレス */
,       ent.ContactPhoneNumber          AS ContactPhoneNumber           /* 事業者電話番号 */
,       odr.OutOfAmends                 AS OutOfAmends                  /* 補償対象外案件 */
,       cst.CustomerId                  AS CustomerId                   /* 購入者ID（購入者テーブル更新用） */
,       odr.ServiceExpectedDate         AS ServiceExpectedDate          /* 役務提供予定日 */
,       (SELECT OrderId FROM T_Order WHERE OrderSeq = P_OrderSeq AND P_OrderSeq = odr.P_OrderSeq) AS ParentOrderId /* 取りまとめ注文代表 */
,       odr.CombinedClaimParentFlg      AS CombinedClaimParentFlg       /* 取りまとめ後に親となったフラグ */
,       odr.CombinedClaimTargetStatus   AS CombinedClaimTargetStatus    /* 取りまとめ対象注文ステータス */
,       cst.Occupation                  AS Occupation                   /* 職業 */
,       cst.CorporateName               AS CorporateName                /* 法人名 */
,       cst.DivisionName                AS DivisionName                 /* 部署名 */
,       cst.CpNameKj                    AS cstCpNameKj                  /* 担当者名 */
,       cst.EntCustId                   AS EntCustId                    /* 加盟店顧客 */
,       cst.AddressKn                   AS AddressKn                    /* 住所 */
,       odr.ClaimSendingClass           AS ClaimSendingClass            /* 同梱判定フラグ */
,       si.SelfBillingFlg               AS SelfBillingFlg               /* 請求書同梱(0：行わない／1：行う) */
,       ent.TaxClass                    AS TaxClass                     /* 税区分(0:内税,1:外税) */
,       ent.DispDecimalPoint            AS DispDecimalPoint             /* 表示用桁数 */
,       ent.UseAmountFractionClass      AS UseAmountFractionClass       /* 利用額端数計算設定 */
,       odr.Rct_Status                  AS Rct_Status                   /* 顧客入金－ステータス（0：未入金／1：入金済み） */
,       pas.PayingControlStatus         AS PayingControlStatus          /* 本締め／仮締め区分 */
,       ent.CreditTransferFlg           AS CreditTransferFlg            /* 口座振替フラグ */
,       ato.CreditTransferRequestFlg    AS CreditTransferRequestFlg     /* 口座振替利用フラグ  */
        FROM    T_Order odr
        INNER JOIN T_Enterprise ent ON (odr.EnterpriseId = ent.EnterpriseId)
        INNER JOIN T_Site si ON (odr.SiteId = si.SiteId)
        INNER JOIN T_Customer cst ON (odr.OrderSeq = cst.OrderSeq)
        LEFT OUTER JOIN T_PayingAndSales pas ON (pas.OrderSeq = odr.OrderSeq)
        STRAIGHT_JOIN AT_Order ato ON (ato.OrderSeq = odr.OrderSeq)
        WHERE   odr.OrderSeq = :OrderSeq;
EOQ;
        return $sql;
    }

	/**
	 * 与信NG復活
	 *
	 * 与信がNGになったものを強制的に復活させる。
	 */
	public function revivalAction()
	{
        $params = array_merge($this->params()->fromRoute(), $this->params()->fromPost());

        $oseq = isset($params['oseq']) ? $params['oseq'] : 0;
        if ($oseq == 0)
        {
            // 注文Seqが指定されていなければ、エラーページに飛ばす。
            return $this->_redirect('error/nop');
        }

        // 与信NG復活機能の利用が可能な場合だけ処理する
        if ($this->canOrderRevival($oseq))
        {
            $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
            try {
                $udata["Incre_Status"]      = 1;    // 社内与信OK
                $udata["Dmi_Status"]        = 1;    // DMI与信OK
                $udata["DataStatus"]        = 31;   // 伝票入力待ち
                $udata["CloseReason"]       = 0;    // クローズ理由を0にしておく。
                $udata["CreditNgHiddenFlg"] = 0;    // 与信NG非表示フラグオフ

                $mdlo = new TableOrder($this->app->dbAdapter);
                $mdlo->saveUpdate($udata, $oseq);

                // ユーザーIDの取得
                $obj = new TableUser($this->app->dbAdapter);
                $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

                // 注文履歴へ登録
                $history = new CoralHistoryOrder($this->app->dbAdapter);
                $history->InsOrderHistory($oseq, 27, $userId);

                // 伝票番号の仮登録実行
                $shippingLogic = new \models\Logic\LogicShipping($this->app->dbAdapter, $userId);
                $datastatus = $udata["DataStatus"];
                if ($udata["DataStatus"] == 31) {
                    $jnResult = $shippingLogic->registerTemporaryJournalNumber($oseq);
                    $datastatus = ($jnResult) ? 41 : $datastatus;
                }

                // テスト注文時のクローズ処理
                if ($datastatus == 41) {
                    $shippingLogic->closeIfTestOrder($oseq);
                }

                $this->app->dbAdapter->getDriver()->getConnection()->commit();
            }
            catch(\Exception $e) {
                $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
                return $this->_redirect('error/nop');
            }
        }

        return $this->_redirect('rworder/detail/oseq/' . $oseq);
	}

	/**
	 * 注文詳細画面のボタン制御用ステータスを取得する。戻りはJSON専用
	 */
	public function getstatusAction() {
$this->app->logger->debug( '[RworderController::getstatusAction] method start !!' );
$mt1 = microtime(true);

        $results = array(
                'result' => false,
                'data' => array()
        );

        $req = array_merge($this->params()->fromRoute(), $this->params()->fromPost());
        $res = $this->getResponse();

        $res->getHeaders()->addHeaderLine( 'Content-Type', 'application/json' );

        try {
            // 注文Seq取得
            $oseq = isset($req['oseq']) ? $req['oseq'] : -1;
            if( $oseq < 0 ) throw new \Exception( 'order sequence not specified.' );

            $orders = new ViewOrderCustomer( $this->app->dbAdapter );
            $histories = new TableClaimHistory( $this->app->dbAdapter );
            $indicates = new TableReclaimIndicate($this->app->dbAdapter);
            $enterprises = new TableEnterprise($this->app->dbAdapter);

            // 注文情報取得
            $order = $this->getOrderCustomerRi($oseq)->current();
            if( ! $order ) throw new \Exception( "sequence '" . $oseq ."' not found." );

            // 請求履歴件数取得
            $his_count = $histories->getReservedCount($oseq);

            // 印刷指示中件数取得
            $ind_count = $indicates->getIndicateCount($oseq);

            //メールアドレスチェック
            $mailAddress = (!empty($order['MailAddress'])) ? true : false;

            // 商品情報
            $mdloi = new TableOrderItems($this->app->dbAdapter);
            $orderitems = $mdloi->findByOrderSeq($oseq)->current();

            // 立替・売上データ
            $mdlpas = new TablePayingAndSales($this->app->dbAdapter);
            $pas = $mdlpas->findPayingAndSales(array('OrderSeq' => $oseq))->current();

            // (システム日付 - 請求管理.初回－請求日（T_ClaimControl.F_ClaimDate） >= サイト.立替精算戻し判定日数（T_Site.PayingBackDays）)
            $isCanPayingBackDays = $this->IsCanPayingBackDays($oseq);

            // 立替精算戻し管理件数
            $sql = " SELECT COUNT(1) AS cnt FROM T_PayingBackControl WHERE ValidFlg = 1 AND OrderSeq = :OrderSeq ";
            $numOfPayingBack = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current()['cnt'];

            // 着荷確認日
            $today = date('Y-m-d');
            $arrivalDate = '';
            if ($orderitems['Deli_ConfirmArrivalDate'] != null) {
                $arrivalDate = date('Y-m-d', strtotime($orderitems['Deli_ConfirmArrivalDate']));
            }
            $deliConfirmArrivalDateJudge = false;
            // 当日のみ取消可能
            if ($today == $arrivalDate) {
                $deliConfirmArrivalDateJudge = true;
            }

            // 与信OKチケット発行ボタン押下可否
            $isTicketIssueButtonFlg = false;
            if ($order['DataStatus'] == 91 && ($order['CloseReason'] == 2 || $order['CloseReason'] == 3)) {
                $sql = ' SELECT COUNT(1) AS cnt FROM T_CreditOkTicket WHERE OrderSeq = :OrderSeq AND Status IN (0, 1) ';
                $prm = array(
                    ':OrderSeq' => $oseq,
                );
                $cnt = $this->app->dbAdapter->query($sql)->execute($prm)->current()['cnt'];
                if ($cnt <= 0) {
                    // 与信NGクローズ もしくは キャンセルクローズ かつ、発行中のチケットが存在しない場合は押下可能
                    $isTicketIssueButtonFlg = true;
                }
            }

            // 与信OKチケット削除ボタン押下可否
            $isTicketDeleteButtonFlg = false;
            $sql = ' SELECT COUNT(1) AS cnt FROM T_CreditOkTicket WHERE OrderSeq = :OrderSeq AND Status = 0 ';
            $prm = array(
                    ':OrderSeq' => $oseq,
            );
            $cnt = $this->app->dbAdapter->query($sql)->execute($prm)->current()['cnt'];
            if ($cnt > 0) {
                // 発行中のチケットが存在する場合は押下可能
                $isTicketDeleteButtonFlg = true;
            }

            // 口座振替ボタンの押下可否
            $isCreditTransferFlg = false;
            if (($order['CreditTransferFlg'] == 1) || ($order['CreditTransferFlg'] == 2) || ($order['CreditTransferFlg'] == 3)) {
                $isCreditTransferFlg = true;
            }

            // 加盟店情報取得
            $enterprise = $enterprises->findEnterprise($order['EnterpriseId'])->current();
            $limitOver = false;
            if ($enterprise['FirstClaimIssueCtlFlg'] == 1) {
                $limitdate = substr($order['Clm_F_LimitDate'], 0, 4).substr($order['Clm_F_LimitDate'], 5, 2).substr($order['Clm_F_LimitDate'], 8, 2);
                if (date('Ymd') > $limitdate) {
                    $limitOver = true;
                }
            }

            // 結果確定
            $results['data'] = array(
                    'orderSeq' => (int)($oseq),
                    'dataStatus' => (int)($order['DataStatus']),
                    'cancelStatus' => (int)($order['Cnl_Status']),
                    'cantCancel' => $order['Cnl_CantCancelFlg'] ? true : false,
                    'closeReason' => (int)($order['CloseReason']),
                    'letterClaimStopFlg' => $order['LetterClaimStopFlg'] ? true : false,
                    'stillPrintCount' => $his_count,
                    'indicateCount' => $ind_count,
                    'Cnl_CantCancelFlg' => $order['Cnl_CantCancelFlg'],
                    'LetterClaimStopFlg' => $order['LetterClaimStopFlg'],
                    'mailAddress' => $mailAddress,
                    'cannotRevival' => $this->canOrderRevival($oseq) ? false : true,
                    'BlackFlg' => $order['BlackFlg'],
                    'GoodFlg'  => $order['GoodFlg'],
                    'Deli_ConfirmArrivalFlg'  => $orderitems['Deli_ConfirmArrivalFlg'],
                    'Deli_ConfirmArrivalDateJudge'  => $deliConfirmArrivalDateJudge,
                    'ChargeDecisionFlg'  => ($pas) ? $pas['ChargeDecisionFlg'] : -1,
                    'PayingControlStatus'  => ($pas) ? $pas['PayingControlStatus'] : -1,
                    'PayingBackFlg'  => $order['PayingBackFlg'],
                    'isCanPayingBackDays' => $isCanPayingBackDays,
                    'numOfPayingBack' => $numOfPayingBack,
                    'PayingControlStatus'  => $pas['PayingControlStatus'],  /* 立替・売上管理.本締め／仮締め区分(0：仮締め,1：本締め) */
                    'lblCustSts' => $this->makeCustStsStr($oseq),
                    'ClearConditionForCharge'  => ($pas) ? $pas['ClearConditionForCharge'] : -1,
                    'TicketIssueButtonFlg' => $isTicketIssueButtonFlg,
                    'TicketDeleteButtonFlg' => $isTicketDeleteButtonFlg,
                    'CreditTransferFlg' => $isCreditTransferFlg,
                    'RequestStatus' => $order['RequestStatus'],
                    'LimitOrver' => $limitOver,
                    'FirstClaimIssueCtlFlg' => $enterprise['FirstClaimIssueCtlFlg'],
            );
            $results['result'] = true;
        }
        catch (\Exception $e) {
            $results['result'] = false;
            $results['data'] = array();
            $results['reason'] = $e->getMessage();
        }
        echo Json::encode( $results );
$this->app->logger->debug( '[RworderController::getstatusAction] method completed. time = ' . ( microtime(true) - $mt1 ) . ' sec.' );

        return $this->getResponse();
	}

    /**
     * 立替精算戻し可能日数の条件を満たすか？
     *
     * @param int $osec 注文SEQ
     * @return boolean true:満たす／false:満たさない
     */
    private function IsCanPayingBackDays($osec) {

        // サイト.立替精算戻し判定日数、の取得
        $sql = " SELECT IFNULL(sit.PayingBackDays, 0) AS PayingBackDays FROM T_Order odr INNER JOIN T_Site sit ON (sit.SiteId = odr.SiteId) WHERE odr.OrderSeq = :OrderSeq ";
        $sitePayingBackDays = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $osec))->current()['PayingBackDays'];

        // 請求管理.初回－請求日
        $sql = " SELECT IFNULL(cc.F_ClaimDate, NOW()) AS F_ClaimDate FROM T_Order odr LEFT OUTER JOIN T_ClaimControl cc ON (cc.OrderSeq = odr.P_OrderSeq) WHERE odr.OrderSeq = :OrderSeq ";
        $firstClaimDate = date('Y-m-d', strtotime($this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $osec))->current()['F_ClaimDate']));

        // 以下の条件を満たすときtrueを、そうでないときfalseを戻す
        // (システム日付 - 請求管理.初回－請求日（T_ClaimControl.F_ClaimDate） >= サイト.立替精算戻し判定日数（T_Site.PayingBackDays）)
        return (BaseGeneralUtils::CalcSpanDays($firstClaimDate, date('Y-m-d')) >= $sitePayingBackDays) ? true : false;
    }

	/**
	 * detailAction / detailupAction 用にリクエストから
	 * 注文Seqを抽出するヘルパーメソッド。
	 * 不払い検索関連時は現在のビューへ必要なパラメータのセットも暗黙で行う
	 *
	 * @return integer|false 注文シーケンス。不払い検索でキャッシュ不一致の場合のみfalseを返す
	 */
	private function getOrderSeqForDetail() {

        $oseq = false;
        $req = array_merge($this->params()->fromRoute(), $this->params()->fromPost());

        if ( isset($req['content_hash']) ) {
            // 不払い検索キャッシュのハッシュ指定がある場合はキャッシュから注文情報を取得する
            $content_hash = $req['content_hash'];

            // セッションから不払い検索キャッシュの復元を試みる
            $cache = SearchfCache::getInstanceFromStorage();

            if( ! $cache ) {
                // キャッシュが期限切れかクリアされている
                $invalid_cache_id = true;
            } else {
                // 復元したキャッシュの検索結果ハッシュと要求のハッシュが一致しない場合はエラー
                $cache->setDbAdapter( $this->app->dbAdapter );
                if( $content_hash != md5( serialize( $cache->getResults() ) ) ) $invalid_cache_id = true;
            }

            if( $invalid_cache_id ) {
                // キャッシュエラーの場合はメッセージをセットする
                $oseq = null;
                $this->view->assign( 'invalid_cache_id', '不正なキャッシュを参照しています。不払い検索結果が破棄されたか、不払い検索が行われていません。' );
            } else {
                // 復元したキャッシュにDBアダプタを割り当てる
                $cache->setDbAdapter( $this->app->dbAdapter );

                // 要求インデックスを取得
                $idx = isset($req['idx']) ? $req['idx'] : -1;
                $cached_list = $cache->getResults();
                // 注文Seqをキャッシュから取得する
                $oseq = $cached_list[ $idx ]['OrderSeq'];

                // count関数対策
                $cached_listLen = 0;
                if(!empty($cached_list)) {
                    $cached_listLen = count($cached_list);
                }

                // 不払いシーケンス情報をビューに割り当てる
                $this->view->assign( 'cached_count', $cached_listLen );
                $this->view->assign( 'hash', $content_hash );
                $this->view->assign( 'index_in_cache', $idx );
                $this->view->assign( 'next_index', $idx + 1 < $cached_listLen ? $idx + 1 : -1 );
                $this->view->assign( 'prev_index', $idx - 1 >= 0 ? $idx - 1 : -1 );

                // 前後のindexの注文Seqも割り当てる
                if( $this->view->prev_index > -1 ) {
                    $this->view->assign( 'prev_oseq', $cached_list[ $this->view->prev_index ]['OrderSeq'] );
                    $this->view->assign( 'prev_oid', $cached_list[ $this->view->prev_index ]['OrderId'] );
                    $this->view->assign( 'prev_name', $cached_list[ $this->view->prev_index ]['NameKj'] );
                }
                if( $this->view->next_index > -1 ) {
                    $this->view->assign( 'next_oseq', $cached_list[ $this->view->next_index ]['OrderSeq'] );
                    $this->view->assign( 'next_oid', $cached_list[ $this->view->next_index ]['OrderId'] );
                    $this->view->assign( 'next_name', $cached_list[ $this->view->next_index ]['NameKj'] );
                }
            }
        }

        // $oseqが未割り当ての場合は、パラメータから注文Seqを取得する
        if( ! $oseq ) {
            $oseq = isset($req['oseq']) ? $req['oseq'] : 0;
        }

        return $oseq;
	}

    //-----------------------------------------------------
    // clmnondeli : 請求書不達
    //-----------------------------------------------------
	/**
	 * 請求書不達リスト
	 */
	public function clmnondeliformAction()
	{
        $params = $this->getParams();

        $oseq = isset($params['oseq']) ? $params['oseq'] : 0;

        $mdloc = new ViewOrderCustomer($this->app->dbAdapter);
        $oc = $mdloc->findOrderCustomerByOrderSeq($oseq, true)->current();
        $oc['UseAmount'] = $this->app->dbAdapter->query(" SELECT UseAmountTotal FROM T_ClaimControl WHERE OrderSeq = (SELECT P_OrderSeq FROM T_Order WHERE OrderSeq = :OrderSeq) "
            )->execute(array(':OrderSeq' => $oseq))->current()['UseAmountTotal'];

        // 日付関連
        $oc["ReceiptOrderDate"] = date('m/d', strtotime($oc["ReceiptOrderDate"]));  // 注文日
        $oc["Clm_F_ClaimDate"]  = date('m/d', strtotime($oc["Clm_F_ClaimDate"]));   // 初回請求日
        $oc["Clm_L_ClaimDate"]  = date('m/d', strtotime($oc["Clm_L_ClaimDate"]));   // 最終請求日

        $oc["UnitingAddress"]   = mb_substr($oc["UnitingAddress"], 0, 8, 'UTF-8');

        // 請求額
        // 利用額　＋　遅延損害金　＋　請求手数料　＋　追加請求手数料
        $oc['ClaimAmount']  = $oc['UseAmount']
                            + $oc['Clm_L_DamageInterestAmount']
                            + $oc['Clm_L_ClaimFee']
                            + $oc['Clm_L_AdditionalClaimFee'];

        $this->view->assign('list', $oc);
        return $this->view;
	}

	/**
	 * 請求書不達確定
	 */
	public function clmnondelidoneAction()
	{
        $params = $this->getParams();

        $oseq = isset($params['oseq']) ? $params['oseq'] : 0;
        $poseq = $this->app->dbAdapter->query(" SELECT P_OrderSeq FROM T_Order WHERE OrderSeq = :OrderSeq "
            )->execute(array(':OrderSeq' => $oseq))->current()['P_OrderSeq'];

        // 必要通知項目の確認
        if (!($oseq > 0)) {
            return $this->view;
        }

        // ユーザーIDの取得
        $obj = new TableUser($this->app->dbAdapter);
        $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

        // 注文テーブルを更新
        $sql  = " UPDATE T_Order ";
        $sql .= " SET    ReturnClaimFlg = 1 ";
        $sql .= " ,      UpdateId = :UpdateId ";
        $sql .= " ,      UpdateDate = :UpdateDate ";
        $sql .= " WHERE  P_OrderSeq = :OrderSeq ";

        $prm = array (
                ':UpdateId' => $userId,
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':OrderSeq' => $poseq,
        );

        $ri = $this->app->dbAdapter->query($sql)->execute($prm);

        // (必要に応じて)メール送信
        $sendmail = isset($params['sendmail']) ? $params['sendmail'] : 'off';

        $mailmsg = '';  // 送信なし時は文字列は空のまま
        if ($sendmail == 'on') {
            try {
                $orders = new TableOrder( $this->app->dbAdapter );
                $histories = new TableClaimHistory( $this->app->dbAdapter );
                $indicates = new TableReclaimIndicate( $this->app->dbAdapter );

                // 注文情報取得
                $order = $orders->find( $oseq )->current();
                if (!$order) throw new \Exception( "この注文番号は有効ではありません。" );

                // 請求履歴件数取得
                $his_count = $histories->getReservedCount($poseq);
                // 印刷指示中件数取得
                $ind_count = $indicates->getIndicateCount($oseq);

                //送信前チェック
                if ($order['Cnl_Status'] > 0) {
                    throw new \Exception('この注文情報は既にキャンセルされています。');
                }
                elseif ($his_count > 0) {
                    throw new \Exception('印刷待ちのデータが存在しています。');
                }
                elseif ($ind_count > 0) {
                    throw new \Exception('CSV出力待ちのデータが存在しています。');
                }

                $mail = new \Coral\Coral\Mail\CoralMail($this->app->dbAdapter, $this->app->mail['smtp']);
                $mail->SendReturnBillMail($poseq, $userId);
                $mailmsg = 'メール送信に成功しました。';
            }
            catch(\Exception $e) {
                $mailmsg = $e->getMessage();
            }
        }

        $this->view->assign('mailmsg', $mailmsg);
        $this->view->assign('urlDetail', 'rworder/detail/oseq/' . $oseq);
        return $this->view;
	}

    //-----------------------------------------------------
    // sppay : 臨時立替
    //-----------------------------------------------------
	/**
	 * 臨時立替リスト
	 */
	public function sppayformAction()
	{
        $params = $this->getParams();

        $oseq = isset($params['oseq']) ? $params['oseq'] : 0;

        $this->view->assign('list', $this->getSppayList($oseq));// 臨時立替リストを取得
        return $this->view;
	}

	/**
	 * 臨時立替日更新
	 */
	public function sppaydoneAction()
	{
        $params = $this->getParams();

        unset($params['isError']);

        $oseq = isset($params['oseq']) ? $params['oseq'] : 0;

        // 必要通知項目の確認
        if (!($oseq > 0)) {
            $this->setTemplate('sppayform');
            $this->view->assign('isError', true);
            $this->view->assign('list', $this->getSppayList($oseq));
            return $this->view;
        }

        // ユーザーIDの取得
        $obj = new TableUser($this->app->dbAdapter);
        $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

        // 立替・売上管理テーブルを更新
        $sql  = " UPDATE T_PayingAndSales ";
        $sql .= " SET    SpecialPayingDate = :SpecialPayingDate ";
        $sql .= " ,      UpdateId = :UpdateId ";
        $sql .= " ,      UpdateDate = :UpdateDate ";
        $sql .= " WHERE  OrderSeq = :OrderSeq ";

        $prm = array (
            ':SpecialPayingDate' => date('Y-m-d H:i:s'),
            ':UpdateId' => $userId,
            ':UpdateDate' => date('Y-m-d H:i:s'),
            ':OrderSeq' => $oseq,
        );

        $ri = $this->app->dbAdapter->query($sql)->execute($prm);

        $this->view->assign('urlDetail', 'rworder/detail/oseq/' . $oseq);
        return $this->view;
	}

	/**
	 * 臨時立替リストを取得する
	 *
	 * @param int $oseq 注文SEQ
	 * @return array 臨時立替リスト
	 */
	private function getSppayList($oseq)
	{
        $sql = <<<EOQ
SELECT o.OrderSeq
,      o.OrderId
,      o.ReceiptOrderDate
,      c.NameKj
,      sit.SiteId
,      o.UseAmount
,      pas.SettlementFee
,      pas.ClaimFee
,      IFNULL(o.ServiceExpectedDate, '---') AS ServiceExpectedDate
,      (SELECT IFNULL(SUM(StampFee), 0) FROM T_StampFee WHERE OrderSeq = o.OrderSeq) AS StampFee
,      CASE o.Rct_Status
         WHEN '0' THEN '未入金'
         WHEN '1' THEN (SELECT KeyContent FROM M_Code WHERE CodeId = 198 AND KeyCode = rc.ReceiptClass)
         ELSE ''
       END AS ReceiptClass
,      pas.ChargeAmount
FROM   T_Order o
       INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq)
       INNER JOIN T_Site sit ON (sit.SiteId = o.SiteId)
       INNER JOIN T_PayingAndSales pas ON (pas.OrderSeq = o.OrderSeq)
       LEFT OUTER JOIN T_ReceiptControl rc ON (rc.OrderSeq = o.OrderSeq)
WHERE  o.OrderSeq = :OrderSeq
EOQ;
        return ResultInterfaceToArray($this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq)));
	}

	/**
	 * メール送信を行う。
	 */
	public function sendmailAction()
	{
        try
        {
           $params = $this->getParams();
            $mail = new CoralMail($this->app->dbAdapter, $this->app->mail['smtp']);
            $obj = new \models\Table\TableUser($this->app->dbAdapter);

            // パラメーターの取得
            $mtp = $params['mtp'];
            $oseq = $params['oseq'];

            // ユーザーIDの取得
            $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

            // メールの種類別にメールを送信する
            switch ($mtp) {
                case 1:  // 過剰入金メール
                    $mail->SendOverPaymentMail($oseq, $userId);
                    break;
                case 2:  // 返金(ｷｬﾝｾﾙ)メール
                    $mail->SendRepaymentMail($oseq, $userId);
                    break;
                case 3:  // 不足入金メール
                    $mail->SendLackOfPay($oseq, $userId);
                    break;
                case 4:  // 間違い伝票修正依頼メール
                    $lgc = new \models\Logic\LogicRequestModifyJournal($this->app->dbAdapter, $this->app->mail['smtp']);
                    $lgc->execOne($oseq, $userId);
                    break;

                default : // 該当なし
                    break;
            }

            $msg = '1';
        }
        catch(\Exception $e)
        {
            $msg = $e->getMessage();
$this->app->logger->err($msg);
        }

        echo \Zend\Json\Json::encode(array('status' => $msg));
        return $this->response;
    }

	/**
	 * (Ajax)伝票番号有効性チェック
	 */
	public function isValidDelijournalnumberAction()
	{
        $params = $this->getParams();

        // 伝票番号の半角化
        $convertedDeliJournalNumber = mb_convert_kana($params['Deli_JournalNumber'], "rn", "UTF-8");

        // 配送方法より[入力規則正規表現]取得
        $obj = new \models\Table\TableDeliMethod($this->app->dbAdapter);
        $validateRegex = $obj->find($params['Deli_DeliveryMethod'])->current()['ValidateRegex'];

        $msg = '1'; // 成功指示('1':成功で初期化)

        // チェック
        if (strlen($validateRegex) > 0) {
            mb_regex_encoding('UTF-8');
            if (!mb_ereg($validateRegex, $convertedDeliJournalNumber)) {
                $msg = '-1';// 正規化チェックエラー
            }
        }

        echo \Zend\Json\Json::encode(array('status' => $msg, 'convertedDeliJournalNumber' => $convertedDeliJournalNumber));
        return $this->response;
	}

	/**
	 * [調整額]リンク文字列の生成
	 *
	 * @param int $oseq 注文SEQ
	 * @return string リンク文字列 ※調整額リンク文字列が生成できないときは""を戻す
	 */
	private function makeLinkPaying($oseq)
    {
        $sql = <<<EOQ
SELECT pc.DecisionDate
,      pc.ExecScheduleDate
,      IFNULL(o.OemId,0) AS OemId
FROM   T_Order o
       INNER JOIN T_PayingAndSales pas ON (pas.OrderSeq = o.OrderSeq)
       INNER JOIN T_PayingControl pc ON (pc.Seq = pas.PayingControlSeq)
WHERE  1 = 1
AND    pas.PayingControlStatus = 0
AND    pas.CancelFlg = 0
AND    o.OrderSeq = :OrderSeq
EOQ;
        $row = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();
        if (!$row) {
            // 対応レコードが取得できない時は""を戻す
            return "";
        }

        return ("paying/dlist2/d/" . date('Y-m-d', strtotime($row['DecisionDate'])) . "/e/" . date('Y-m-d', strtotime($row['ExecScheduleDate'])) . "/oemid/" . $row['OemId']);
    }

    /**
     * 注文日が妥当か？
     *
     * @param string $odrDate 注文日 yyyy-MM-dd形式で通知
     * @param string $regDate 登録日 yyyy-MM-dd形式で通知
     * @return boolean
     */
    private function isValidOrderdate($odrDate, $regDate)
    {
        // 注文登録標準期間日数、の取得
        $obj = new \models\Table\TableSystemProperty($this->app->dbAdapter);
        // (過去日:デフォルト 60日)
        $daysPast   = (int)$obj->getValue('[DEFAULT]', 'systeminfo', 'OrderDefaultDaysPast'  );
        $daysPast   = ($daysPast > 0) ? $daysPast : 60;
        // (未来日:デフォルト180日)
        $daysFuture = (int)$obj->getValue('[DEFAULT]', 'systeminfo', 'OrderDefaultDaysFuture');
        $daysFuture = ($daysFuture > 0) ? $daysFuture : 180;

        if ($regDate < $odrDate) {
            // 未来日が指定されている時
            $diffDate = BaseGeneralUtils::CalcSpanDays($regDate, $odrDate);
            return ($diffDate < $daysFuture) ? true : false;
        }
        else if ($regDate > $odrDate) {
            // 過去日が指定されている時
            $diffDate = BaseGeneralUtils::CalcSpanDays($odrDate, $regDate);
            return ($diffDate < $daysPast) ? true : false;
        }

        // ($odrDate == $regDate) 注文日＝登録日
        return true;
    }

    /**
     * 指定注文が与信NG復活機能による復活が可能であるかを判断する。
     *
     * @access protected
     * @param int $oseq 注文SEQ
     * @return boolean
     */
    protected function canOrderRevival($oseq)
    {
        $orders = new TableOrder($this->app->dbAdapter);

        $order = $orders->find($oseq)->current();
        if(!$order) return false;                       // 注文SEQ不正時はfalse
        if($order['DataStatus'] != 91) return false;    // 注文がクローズされていなければfalse
        if($order['CloseReason'] != 3) return false;    // クローズ事由が与信NGでなければfalse

        $enterprises = new TableEnterprise($this->app->dbAdapter);
        $ent = $enterprises->find($order['EnterpriseId'])->current();
        if(!$ent) return false;                         // 事業者が見つからない場合はfalse
        if($ent['OrderRevivalDisabled']) return false;  // 与信NG復帰が事業者で禁止されている場合はfalse

        // ここまでたどり着いたら利用可能
        // → 与信NGクローズされていて且つ事業者が与信NG復活機能の利用を禁止されていない
        return true;
    }

    public function clmhisAction()
    {
        $params = $this->getParams();

        $oseq = isset($params['oseq']) ? $params['oseq'] : -1;

        $mdloc = new ViewOrderCustomer($this->app->dbAdapter);
        $orderCustomer = $mdloc->findOrderCustomerByOrderSeq($oseq)->current();

        //ペイジー収納機関番号取得
        $mdlCode = new TableCode($this->app->dbAdapter);
        $bk_number = $mdlCode->find(LogicPayeasy::PAYEASY_CODEID, LogicPayeasy::BK_NUMBER_KEYCODE)->current()['Note'];

        $this->view->assign('oc', $orderCustomer);
        $this->view->assign('list', $this->getClaimHistoryDetails($oseq));

        $this->view->assign('bk_number', $bk_number);

        // 入金保留中のJNB口座情報を反映
        $accLogic = new \models\Logic\Jnb\LogicJnbAccount($this->app->dbAdapter);
        $this->view->assign('jnbAcc', $accLogic->findPendingAccountByOrderSeq($oseq));

        // 入金保留中のSMBC口座情報を反映
        $accLogic2 = new \models\Logic\Smbcpa\LogicSmbcpaAccount($this->app->dbAdapter);
        $this->view->assign('smbcAcc', $accLogic2->findPendingAccountByOrderSeq($oseq));

        return $this->view;
    }

    protected function getClaimHistoryDetails($oseq)
    {
        $sql = " SELECT * FROM T_Order ord INNER JOIN T_ClaimHistory chs ON (chs.OrderSeq = ord.P_OrderSeq) LEFT OUTER JOIN T_OemClaimAccountInfo oca ON oca.ClaimHistorySeq = chs.Seq WHERE ord.OrderSeq = :OrderSeq ORDER BY chs.ClaimSeq ASC ";
        $ri = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq));
        return ResultInterfaceToArray($ri);
    }

	/**
	 * 領収書発行履歴画面
	 */
    public function receiptissuehistoryAction()
    {
        // パラメータ取得
        $params = $this->getParams();

        // パラメータから注文連番の取得
        $oseq = isset($params['oseq']) ? $params['oseq'] : -1;

        // 注文と請求管理データの取得
        $sql  = " SELECT";
        $sql .= " od.OrderSeq";
        $sql .= ", od.OrderId";
        $sql .= ", cm.NameKj";
        $sql .= ", cc.ClaimAmount";
        $sql .= ", cc.F_ClaimDate";
        $sql .= ", rc.ReceiptDate as CreditSettlementDecisionDate";
        $sql .= " FROM T_Order AS od";
        $sql .= " INNER JOIN T_ClaimControl AS cc ON cc.OrderSeq = od.P_OrderSeq";
        $sql .= " INNER JOIN T_Customer AS cm ON cm.OrderSeq = od.P_OrderSeq";
        $sql .= " INNER JOIN T_ReceiptControl AS rc ON rc.OrderSeq = od.P_OrderSeq";
        $sql .= " WHERE od.OrderSeq = :OrderSeq";

        $prm = array(':OrderSeq' => $oseq);

        $orderClaim = $this->app->dbAdapter->query($sql)->execute($prm)->current();

        // 領収書発行履歴の取得
        $mvrh = new MypageViewReceiptIssueHistory($this->app->dbAdapter);
        $receiptIssueHistory = ResultInterfaceToArray($mvrh->findOrderSeqSeqAsc($oseq));

        // 領収書発行件数
        $orderClaim['ReceiptIssueCount'] = empty($receiptIssueHistory) ? 0 : count($receiptIssueHistory);

        // 画面への受け渡し準備
        $this->view->assign('oc', $orderClaim);
        $this->view->assign('list', $receiptIssueHistory);

        return $this->view;
    }

    /**
     * SMBCバーチャル口座オープン用のロックアイテムを獲得する
     *
     * @access protected
     * @param array 対象注文の行オブジェクト
     * @return \models\Logic\ThreadPool\LogicThreadPoolItem | null
     */
    protected function getLockItemForSmbcpaAccount($orderRow = null)
    {
        if(!$orderRow) return null;

        $smbcpaTable = new \models\Table\TableSmbcpa($this->app->dbAdapter);
        $smbcpa = $smbcpaTable->findByOemId((int)$orderRow['OemId'])->current();
        if(!$smbcpa) return null;

        $pool = \models\Logic\LogicThreadPool::getPoolForSmbcpaAccountOpen($smbcpa['SmbcpaId'], $this->app->dbAdapter);
        return $pool->openAsSingleton($orderRow['OrderSeq']);
    }

    /**
     * チケット情報に表示するラベル文字列を作成する
     * @param int $oseq 注文SEQ
     */
    private function getTicketStatusLabel($oseq){

        $mdlOrder = new TableOrder($this->app->dbAdapter);

        $usingLabel = '';
        $hakkoLabel = '';

        // -------------------------------------------
        // 1. 使用注文が存在するか確認
        // -------------------------------------------
        $sql = " SELECT * FROM T_CreditOkTicket WHERE UseOrderSeq = :UseOrderSeq ";
        $prm = array(
                ':UseOrderSeq' => $oseq,
        );
        $row = $this->app->dbAdapter->query($sql)->execute($prm)->current();
        if ($row) {
            $orderId = $mdlOrder->find($row['OrderSeq'])->current()['OrderId'];
            $usingLabel = $orderId . 'のチケットを使用';
        }

        // -------------------------------------------
        // 2. 発行注文が存在するか確認
        // -------------------------------------------
        $sql = " SELECT * FROM T_CreditOkTicket WHERE OrderSeq = :OrderSeq ORDER BY Seq DESC LIMIT 1 ";
        $prm = array(
                ':OrderSeq' => $oseq,
        );
        $row = $this->app->dbAdapter->query($sql)->execute($prm)->current();
        if ($row) {
            if ($row['Status'] == 0 && strtotime(date('Y-m-d H:i:s')) <= strtotime($row['ValidToDate'])){
                $hakkoLabel = '発行済(有効)';
            } elseif ($row['Status'] == 0 && strtotime(date('Y-m-d H:i:s')) > strtotime($row['ValidToDate'])){
                $hakkoLabel = '発行済(期限切れ)';
            } elseif ($row['Status'] == 1) {
                $orderId = $mdlOrder->find($row['UseOrderSeq'])->current()['OrderId'];
                $hakkoLabel = $orderId . 'にて使用';
            }
        }

        // -------------------------------------------
        // 3. ラベル文字列の作成
        // -------------------------------------------
        if (strlen($usingLabel) > 0 && strlen($hakkoLabel) > 0) {
            return $usingLabel . '<BR>' . $hakkoLabel;
        }
        if (strlen($usingLabel) > 0) {
            return $usingLabel;
        }
        if (strlen($hakkoLabel) > 0) {
            return $hakkoLabel;
        }
        return '---';
    }

    /**
     * OKチケット発行処理を行う
     */
    public function reclaimAction()
    {
        $mdlo = new TableOrder($this->app->dbAdapter);
        $params = $this->getParams();
        $userName = $this->app->authManagerAdmin->getUserInfo()->NameKj;

        try
        {
            $oseq = $params['oseq'];
            $note['Incre_Note'] = $mdlo->find($oseq)->current()['Incre_Note'];
            $uOrder['Incre_Note'] = date('Y-m-d H:i') . " " . $userName . "【再請求書発行】" . "\n" . $note['Incre_Note'];
            $mdlo->saveUpdateParts($uOrder, $oseq);

            $mdlc = new TableCustomer($this->app->dbAdapter);
            $customer = $mdlc->findCustomer(array('OrderSeq' => $oseq))->current();

            if ($customer['ValidAddress'] == 3) {
                  $sql = " UPDATE T_Customer SET ValidAddress = 1 WHERE CustomerId = :CustomerId ";
                  $stm = $this->app->dbAdapter->query($sql);
                  $stm->execute(array(':CustomerId' => $customer['CustomerId']));
            }

            $this->app->dbAdapter->getDriver()->getConnection()->commit();
        }
        catch(\Exception $e)
        {
            $this->app->dbAdapter->getDriver()->getConnection()->rollback();
        }

        return $this->response;
    }

    /**
     * Check order if use todoItekara to pay
     */
    protected function _checkDisplayBtn($orderSeq) {
        $sql = "SELECT ExtraPayType, ExtraPayKey, ExtraPayNote FROM AT_Order WHERE OrderSeq = :OrderSeq";
        $row = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $orderSeq))->current();
        if ($row['ExtraPayType'] && $row['ExtraPayKey'] && $row['ExtraPayNote']) {
            return true;
        }
        return false;
    }
}

