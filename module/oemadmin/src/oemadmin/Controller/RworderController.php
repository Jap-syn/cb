<?php
namespace oemadmin\Controller;

use oemadmin\Application;
use Zend\Json\Json;
use Coral\Coral\Controller\CoralControllerAction;
use models\View\ViewOrderCustomer;
use models\Table\TableOrder;
use models\View\ViewDelivery;
use models\Table\TableOrderItems;
use models\Table\TableCancel;
use models\Table\TableClaimHistory;
use models\Table\TableStampFee;
use models\Table\TablePayingControl;
use Coral\Coral\CoralCodeMaster;
use models\Table\TableOperator;
use Coral\Base\BaseHtmlUtils;
use Zend\Db\ResultSet\ResultSet;
use Coral\Base\BaseGeneralUtils;
use models\Table\TableCode;


class RworderController extends CoralControllerAction
{
	protected $_componentRoot = './application/views/components';

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

		$this->addStyleSheet($this->app->getOemCss())
		->addJavaScript('../../js/prototype.js');

		$this->setPageTitle($this->app->getOemServiceName()." - 注文情報");
	}

	/**
	 * 注文詳細画面
	 */
	public function detailAction()
	{
		$this->addJavaScript("../../js/corelib.js")
		->addJavaScript("../../js/base.ui.js")
		->addJavaScript("../../js/base.ui.datepicker.js")
		->addStyleSheet("../../css/base.ui.datepicker.css");

		$this
			->addJavaScript( '../../js/json+.js' )
			->addJavaScript( '../../js/json_format.js' );

		// 注文ステータスによる色分け用CSSのアサイン
		$this->addStyleSheet( '../../css/cbadmin/orderstatus/detail_' .
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
// 		$mdloc = new ViewOrderCustomer($this->app->dbAdapter);
// 		$orderCustomer = $mdloc->findOrderCustomerByOrderSeq($oseq);
		$orderCustomer = $this->getOrderCustomerRi($oseq)->current();
		$orderCustomer['AddressKn'] = BaseGeneralUtils::convertNarrowToWideEx( $orderCustomer['AddressKn'] );
		$orderCustomer['CustStsStr'] = $this->makeCustStsStr($oseq);    // 顧客スタータス文字列の生成
        $taxClass = $orderCustomer['TaxClass'];

		//注文情報
		$mdlo = new TableOrder( $this->app->dbAdapter );
		$order = $mdlo->find( $oseq )->current();

                if(is_null($order['OemId']) || $order['OemId'] != $this->app->authManagerAdmin->getUserInfo()->OemId) {
                    // url直指定でOemIDが不一致となった場合、エラーページに飛ばす。
                    return $this->_redirect('error/nop');
                }

		// 配送情報
		$mdldeli = new ViewDelivery($this->app->dbAdapter);
		$deli = $mdldeli->findDelivery(array('OrderSeq' => $oseq, 'DataClass' => 1))->current();

		// 商品情報
		$mdloi = new TableOrderItems($this->app->dbAdapter);
		$deliveryFee = 0;
		$settlementFee = 0;
        $exTax = 0;
		$reclaimFee = 0;
		$totalSumMoney = 0;
		$totalClaimMoney = 0;
		$itemsNeta = $mdloi->findByOrderSeq($oseq);

		// キャンセル情報
		$mdlcl = new TableCancel($this->app->dbAdapter);
		$cancel = $mdlcl->findCancel(array('OrderSeq' => $oseq))->current();

		//請求履歴情報
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

		// 再請求に伴う追加手数料
		$reclaimFee = $orderCustomer['Clm_L_DamageInterestAmount'] + $orderCustomer['Clm_L_ClaimFee'];

		// 請求金額
		$totalClaimMoney = $totalSumMoney + $reclaimFee - $orderCustomer['InstallmentPlanAmount'];

		// 支払済み金額変更に必要なベーストータル
		$baseTotal = $totalSumMoney + $reclaimFee;

		// 加工
		// ステータス
		$custom["DataStatus"] = $codeMaster->getDataStatusCaption($orderCustomer['DataStatus']);

                // OEM表示用として与信はまとめる
                if($orderCustomer['DataStatus'] <= 25){
                    $custom["DataStatus"] = '与信中';
                }

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
// 			$pastDays = NetB_GeneralUtils::CalcSpanDays2(new Zend_Date($orderCustomer->Clm_F_LimitDate), new Zend_Date());
			$pastDays = BaseGeneralUtils::CalcSpanDays2(date('Y-m-d H:i:s', strtotime($orderCustomer['Clm_F_LimitDate'])), date('Y-m-d H:i:s'));
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
// 			$receiptPastDays = NetB_GeneralUtils::CalcSpanDays2(new Zend_Date($orderCustomer->Clm_F_LimitDate), new Zend_Date($orderCustomer->Rct_ReceiptDate));
			$receiptPastDays = BaseGeneralUtils::CalcSpanDays2(date('Y-m-d H:i:s', strtotime($orderCustomer['Clm_F_LimitDate'])), date('Y-m-d H:i:s', strtotime($orderCustomer['CloseReceiptDate'])));
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
		// switch ($orderCustomer['Rct_ReceiptMethod'])
		// {
		// 	case 1:
		// 		$custom['Rct_ReceiptMethod'] = 'コンビニ';
		// 		break;
		// 	case 2:
		// 		$custom['Rct_ReceiptMethod'] = '郵便局';
		// 		break;
		// 	case 3:
		// 		$custom['Rct_ReceiptMethod'] = '銀行';
		// 		break;
		// 	case 4:
		// 		$custom['Rct_ReceiptMethod'] = 'LINE Pay';
		// 		break;
		// 	default:
		// 		$custom['Rct_ReceiptMethod'] = '';
		// 		break;
		// }

		// 入金方法
        if (! empty ( $orderCustomer ['ReceiptClass'] )) {
            // コードマスターから入金方法のコメントを取得
            $mdlc = new TableCode ( $this->app->dbAdapter );
            $ReceiptMethod = $mdlc->find ( 198, $orderCustomer ['ReceiptClass'] )->current ();
            $custom['Rct_ReceiptMethod'] = $ReceiptMethod['KeyContent'];
        }

		// キャンセル情報
		if ($cancel)
		{
			$custom['CancelDate'] = $cancel['CancelDate'];
			$custom['CancelReason'] = $cancel['CancelReason'];
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

		// 未印刷の請求データ有無（数）
		$custom['StillPrintCount'] = $mdlch->findClaimHistory(array('PrintedFlg' => 0, 'OrderSeq' => $oseq))->count();

		// キャンセル完了時に印刷ジョブ転送済状態のチェック
		// ※印刷ジョブ転送済と表示するため(2013.02.18)
		$custom['PrintedTransBeforeCancelled'] = $order['PrintedTransBeforeCancelled'];

		/*
		$reclaimtotal = 0;
		if ($reclaimtotal < (int)$reclaim1['Additional'])
		{
			$reclaimtotal = (int)$reclaim1['Additional'];
		}

		if ($reclaimtotal < (int)$reclaim2['Additional'])
		{
			$reclaimtotal = (int)$reclaim2['Additional'];
		}

		if ($reclaimtotal < (int)$reclaim3['Additional'])
		{
			$reclaimtotal = (int)$reclaim3['Additional'];
		}

		if ($reclaimtotal < (int)$reclaim4['Additional'])
		{
			$reclaimtotal = (int)$reclaim4['Additional'];
		}
		*/

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

// 		$custom['reclaim4ctotal'] = $reclaimtotal;
		// 請求取りまとめ関連
		// 請求取りまとめで取りまとめ先（親）があった場合には対象の注文IDを取得する
// 		if($order->CombinedClaimParentOrderSeq != null) {
// 			$parentorder = $mdlo->find($order->CombinedClaimParentOrderSeq)->current()->toArray();

// 			$this->view->assign( 'ccpOseq', $order->CombinedClaimParentOrderSeq);
// 			$this->view->assign( 'parentOrder', array('seq' => $parentorder['OrderSeq'], 'id' => $parentorder['OrderId']));
// 		}
		if($order['OrderSeq'] != $order['P_OrderSeq']) {
		    $parentorder = $mdlo->find($order['P_OrderSeq'])->current();

		    $this->view->assign( 'ccpOseq', $order['P_OrderSeq']);
		    $this->view->assign( 'parentOrder', array('seq' => $parentorder['OrderSeq'], 'id' => $parentorder['OrderId']));
		}

		// 請求取りまとめで取りまとめ元（子）があった場合には対象の注文IDを取得する
		if($order['CombinedClaimParentFlg']) {
			// CombinedClaimParentOrderSeqはインデックスが貼られていないため、パフォーマンス向上向けにCombinedClaimTargetStatus条件を
			// 追加したクエリ発行に改変（2014.2.19 eda）

			// --------------- 以下コメントアウト
			//$orders = $mdlo->findOrder(array('CombinedClaimParentOrderSeq' => $order->OrderSeq))->toArray();
			//
			//$childOrders = array();
			//foreach($orders as $target) {
			//	array_push($childOrders, array('seq'=>$target['OrderSeq'], 'id'=>$target['OrderId']));
			//}
			// --------------- 以下新クエリ発行
// 			$db = $this->app->dbAdapter;
// 			$q = 'select OrderSeq as seq, OrderId as id from T_Order '.
// 				'where CombinedClaimTargetStatus in (91, 92) and %s order by OrderSeq desc';
// 			$childOrders =
// 				$db->fetchAll(sprintf($q, $db->quoteInto('CombinedClaimParentOrderSeq = ?', $order->OrderSeq)));

// 			// ----------------------------- 改変ここまで

			$sql  = " select OrderSeq as seq, OrderId as id ";
			$sql .= " from   T_Order  ";
			$sql .= " where  CombinedClaimTargetStatus in (91, 92) and P_OrderSeq = :P_OrderSeq and P_OrderSeq <> OrderSeq ";
			$sql .= " order by OrderSeq desc ";

			$stm = $this->app->dbAdapter->query($sql);

			$ri = $stm->execute(array(':P_OrderSeq' => $order['P_OrderSeq']));

			$rs = new ResultSet();
			$rs->initialize($ri);
			$childOrders = $rs->toArray();

			$this->view->assign( 'ccparf', $order['CombinedClaimParentFlg'] );
			$this->view->assign( 'childOrders', $childOrders );
		}

		// キャリア
		$custom["CarrierArr"] = $codeMaster->getCarrierMaster();

        // 過剰入金色分けしきい値
        $excessPaymentColorThreshold = $this->app->dbAdapter->query(
            " SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'systeminfo' AND Name = 'ExcessPaymentColorThreshold' ")->execute(null)->current()['PropValue'];
        $orderCustomer['ExcessPaymentColorThreshold'] = intval($excessPaymentColorThreshold);

		// アサイン
		$this->view->assign('oc', $orderCustomer);
		$this->view->assign('deli', $deli);
		$this->view->assign('items', $items);
		$this->view->assign('custom', $custom);						// 表示用に加工したデータの連想配列
		$this->view->assign('deliveryFee', $deliveryFee);			// 送料
		$this->view->assign('settlementFee', $settlementFee);		// 手数料
        $this->view->assign('taxClass', $taxClass);                 // 税区分
        $this->view->assign('exTax', $exTax);                       // 外税額
		$this->view->assign('totalSumMoney', $totalSumMoney);		// 商品の合計金額
		$this->view->assign('reclaimFee', $reclaimFee);				// 再請求追加手数料
		$this->view->assign('totalClaimMoney', $totalClaimMoney);	// 請求合計
		$this->view->assign('baseTotal', $baseTotal);				// ベーストータル
		if(!empty($his)) {
			$this->view->assign('bill', $his);						// 請求書履歴情報
		}
		$this->view->assign('order', $order);	// 請求合計

                $carrierList = $codeMaster->getCarrierMaster();
                //$carrierList[0] = '-';
                $this->view->assign('carrier', $carrierList);

		// ツールURLをアサイン
		$this->view->assign('urlUnote', $this->app->tools['url']['unote']);				// 備考更新
		$this->view->assign('urlRegistBlk', $this->app->tools['url']['registblk']);		// ブラック登録
		$this->view->assign('urlRegistExc', $this->app->tools['url']['registexc']);		// 優良登録
		$this->view->assign('urlReturnBill', $this->app->tools['url']['returnbill']);	//請求書不達メール送信

		if (isset($_SERVER["HTTP_REFERER"]))
		{
			$this->view->assign(
				'backNavi',
				sprintf('<a href="%s">戻　る</a>', $_SERVER["HTTP_REFERER"])
			);
		}

		// 同梱ツール経由の請求実績の有無をアサイン
		$sb_histories = $mdlhis->findForSelfBillingByOrderSeq($oseq);
		$this->view->assign('sbHistories', $sb_histories);

		return $this->view;
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
// 		$req = $this->getRequest();
		$req = array_merge($this->params()->fromRoute(), $this->params()->fromPost());

        // 引数['content_hash']は未設定ですので、if判断をコメントする。
		/*if( $req['content_hash']) {
			// 不払い検索キャッシュのハッシュ指定がある場合はキャッシュから注文情報を取得する
// 			$content_hash = $req->getParam('content_hash');
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
				$this->view
					->assign( 'invalid_cache_id', '不正なキャッシュを参照しています。不払い検索結果が破棄されたか、不払い検索が行われていません。' );
			} else {
				// 復元したキャッシュにDBアダプタを割り当てる
				$cache->setDbAdapter( $this->app->dbAdapter );

				// 要求インデックスを取得
// 				$idx = $req->getParam('idx', -1);
				$idx = isset($req['idx']) ? $req['idx'] : -1;
				$cached_list = $cache->getResults();
				// 注文Seqをキャッシュから取得する
				$oseq = $cached_list[ $idx ]['OrderSeq'];

				// 不払いシーケンス情報をビューに割り当てる
				$this->view
					->assign( 'cached_count', count($cached_list) )
					->assign( 'hash', $content_hash )
					->assign( 'index_in_cache', $idx )
					->assign( 'next_index', $idx + 1 < count($cached_list) ? $idx + 1 : -1 )
					->assign( 'prev_index', $idx - 1 >= 0 ? $idx - 1 : -1 );

				// 前後のindexの注文Seqも割り当てる
				if( $this->view->prev_index > -1 ) {
					$this->view
						->assign( 'prev_oseq', $cached_list[ $this->view->prev_index ]['OrderSeq'] )
						->assign( 'prev_oid', $cached_list[ $this->view->prev_index ]['OrderId'] )
						->assign( 'prev_name', $cached_list[ $this->view->prev_index ]['NameKj'] );
				}
				if( $this->view->next_index > -1 ) {
					$this->view
						->assign( 'next_oseq', $cached_list[ $this->view->next_index ]['OrderSeq'] )
						->assign( 'next_oid', $cached_list[ $this->view->next_index ]['OrderId'] )
						->assign( 'next_name', $cached_list[ $this->view->next_index ]['NameKj'] );
				}
			}
		}*/

		// $oseqが未割り当ての場合は、パラメータから注文Seqを取得する
		if( ! $oseq ) {
		    $oseq = isset($req['oseq']) ? $req['oseq'] : 0;
		}

		return $oseq;
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
,      o.P_OrderSeq
,      rc.ReceiptProcessDate       AS ReceiptProcessDate           /* 確定日 */
,      cc.ReceiptAmountTotal       AS ReceiptAmountTotal           /* 入金済額 */
,      c.CorporateName             AS CorporateName                /* 法人名 */
,      c.DivisionName              AS DivisionName                 /* 部署名 */
,      c.CpNameKj                  AS cstCpNameKj                  /* 担当者名 */
,      c.AddressKn                 AS AddressKn                    /* 住所カナ */
,      c.EntCustId                 AS EntCustId                    /* 加盟店顧客 */
,      o.ServiceExpectedDate       AS ServiceExpectedDate          /* 役務提供予定日 */
,      o.CombinedClaimParentFlg    AS CombinedClaimParentFlg       /* 取りまとめ後に親となったフラグ */
,      (SELECT OrderId FROM T_Order WHERE OrderSeq = P_OrderSeq AND P_OrderSeq = o.P_OrderSeq) AS ParentOrderId /* 取りまとめ注文代表 */
,      ''                          AS CustStsStr                   /* 顧客ステータス文字列 */
,      (SELECT TaxClass FROM T_Enterprise WHERE EnterpriseId = voc.EnterpriseId) AS TaxClass /* 税区分 */
,      (SELECT IFNULL(SUM(UseAmount), 0) FROM T_Order WHERE P_OrderSeq = o.P_OrderSeq AND OrderSeq <> o.OrderSeq AND Cnl_Status = 0) AS ChildUseAmountSum /* 他取りまとめ額 */
,      o.Oem_Note                  AS Oem_Note                     /* OEM先備考 */
,      rc.ReceiptDate              AS Rct_ReceiptDate              /* 顧客入金日 */
,      rc.ReceiptClass             AS ReceiptClass                 /* 入金方法 */
,      (SELECT DispDecimalPoint FROM T_Enterprise WHERE EnterpriseId = voc.EnterpriseId) AS DispDecimalPoint    /* 表示用小数点桁数 */
,      CASE WHEN cc.ClaimAmount IS NULL THEN (SELECT SUM(UseAmount) FROM T_Order WHERE P_OrderSeq = o.P_OrderSeq AND Cnl_Status = 0)
            ELSE cc.ClaimAmount
       END  AS ClaimAmount                                         /* 請求金額 */
,      vcr.ReceiptDate             AS CloseReceiptDate             /* クローズ入金日 */

FROM   V_OrderCustomer voc
       INNER JOIN T_Order o ON (o.OrderSeq = voc.OrderSeq)
       INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq)
       LEFT OUTER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.P_OrderSeq)
	   LEFT OUTER JOIN T_ReceiptControl rc ON (rc.ReceiptSeq = cc.LastReceiptSeq)
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

	    return implode($ar, '、');
	}


	public function upAction() {

	    $params = array_merge($this->params()->fromRoute(), $this->params()->fromPost());

	    $oseq = isset($params['oseq']) ? $params['oseq'] : 0;
	    $oemOrderId = isset($params['Oem_OrderId']) ? $params['Oem_OrderId'] : null;
	    $oemNote = isset($params['OmeNote']) ? $params['OmeNote'] : null;

	    $mdlu = new \models\Table\TableUser($this->app->dbAdapter);
	    $userClass = 1;       // OEMオペレーター
	    $seq = $this->app->authManagerAdmin->getUserInfo()->OemOpId;
	    $userId = $mdlu->getUserId($userClass, $seq);

		$mdlo = new TableOrder( $this->app->dbAdapter );
		$prm_save = array(
		    'Oem_OrderId' => $oemOrderId,
		    'Oem_Note' => $oemNote,
		    'UpdateId' => $userId,
		);
		$mdlo->saveUpdate($prm_save, $oseq);

		// リダイレクトURL
		$url = 'rworder/detail/oseq/' . $oseq;

		return $this->_redirect($url);
	}
}

