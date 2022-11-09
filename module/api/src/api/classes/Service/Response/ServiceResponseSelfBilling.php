<?php
namespace api\classes\Service\Response;

use api\classes\Service\ServiceSelfBilling;
use api\Application;

/**
 * 同梱APIのレスポンスを管理するクラス
 */
class ServiceResponseSelfBilling extends ServiceResponseAbstract {

	 /* アクション
	 *
	 * @access protected
	 * @var array
	 */
	protected $_action;

	 /* 結果項目リスト
	 *
	 * @access protected
	 * @var array
	 */
	protected $_results = array();

	/**
	 * 結果を返却する
	 *
	 * @param array $data
	 * @return ServiceResponseSelfBilling このインスタンス
	 */
	public function addResult($data) {
		$this->_results = $data;
		return $this;
  	}

	/**
	 * アクションを設定する
	 *
	 * @param array $data
	 * @return ServiceResponseSelfBilling このインスタンス
	 */
	public function addAction($action) {
		$this->_action = $action;
		return $this;
 	}

	/**
	 * オブジェクトのシリアライズ
	 *
	 * @return string シリアライズされた文字列
	 */
	public function serialize() {
		$doc = new \DOMDocument("1.0", "utf-8");

		// Response
		$rootElement = $doc->appendChild( $doc->createElement("Response") );

		// Status
		$statusElement = $rootElement->appendChild( $doc->createElement("Status") );
		$statusElement->appendChild( $doc->createTextNode($this->status) );

		// Messages
		$messagesElement = $rootElement->appendChild( $doc->createElement("Messages") );
		foreach ( $this->messages as $message ) {
			$messageElement = $doc->createElement("Message");
			$messageElement->setAttribute( "cd", $message->messageCd );
			$messageElement->appendChild( $doc->createTextNode( $message->messageText ) );
			$messagesElement->appendChild( $messageElement );
		}

		// Results
		$resultsElement = $rootElement->appendChild( $doc->createElement("Results") );

		// Result
		if(!empty($this->_results['Result'])) {
			$results = $this->_results['Result'];
			foreach($results as $value) {
				$resultElement = $resultsElement->appendChild( $doc->createElement("Result") );
				switch($this->_action) {
					case ServiceSelfBilling::CMD_FETCH_PRE_TARGETS:
						// 注文ID
						$orderidElement = $resultElement->appendChild( $doc->createElement("OrderId"));
						$orderidElement->appendChild($doc->createTextNode($value['OrderId']));
						// 注文受日時
						$rorderdateElement = $resultElement->appendChild($doc->createElement("ReceiptOrderDate"));
						$rorderdateElement->appendChild($doc->createTextNode($value['ReceiptOrderDate']));
						// 注文登録日時
						$registdateElement = $resultElement->appendChild( $doc->createElement("RegistDate"));
						$registdateElement->appendChild($doc->createTextNode($value['RegistDate']));
						// 漢字氏名
						$namekjElement = $resultElement->appendChild( $doc->createElement("NameKj"));
						$namekjElement->appendChild($doc->createTextNode($value['NameKj']));
						// 住所
						$uaddressElement = $resultElement->appendChild( $doc->createElement("UnitingAddress"));
						$uaddressElement->appendChild($doc->createTextNode($value['UnitingAddress']));
						// 合計
						$useamonutElement = $resultElement->appendChild( $doc->createElement("UseAmount"));
						$useamonutElement->appendChild($doc->createTextNode($value['UseAmount']));
						// 任意注文番号
						$entorderElement = $resultElement->appendChild( $doc->createElement("Ent_OrderId"));
						$entorderElement->appendChild($doc->createTextNode($value['Ent_OrderId']));
						// 配送先氏名
						$destnamekjElement = $resultElement->appendChild( $doc->createElement("DestNameKj"));
						$destnamekjElement->appendChild($doc->createTextNode($value['DestNameKj']));
						// 配送先郵便番号
						$destpostalElement = $resultElement->appendChild( $doc->createElement("DestPostalCode"));
						$destpostalElement->appendChild($doc->createTextNode($value['DestPostalCode']));
						// 配送先住所
						$destuaddElement = $resultElement->appendChild( $doc->createElement("DestUnitingAddress"));
						$destuaddElement->appendChild($doc->createTextNode($value['DestUnitingAddress']));
						// 配送先電話番号
						$destphoneElement = $resultElement->appendChild( $doc->createElement("DestPhone"));
						$destphoneElement->appendChild($doc->createTextNode($value['DestPhone']));
						break;

					case ServiceSelfBilling::CMD_COUNT_PRE_TARGETS:
						$countElement = $resultElement->appendChild( $doc->createElement("Count"));
						$countElement->appendChild($doc->createTextNode($value['Count']));
						break;

					case ServiceSelfBilling::CMD_CAN_ENQUEUE:
						$orderidElement = $resultElement->appendChild( $doc->createElement("OrderId"));
						$orderidElement->appendChild($doc->createTextNode($value['OrderId']));
						$execresultElement = $resultElement->appendChild( $doc->createElement("ExecResult"));
						$execresultElement->appendChild($doc->createTextNode($value['ExecResult']));
						if(!empty($value['ErrorCd'])) {
							$errorElement = $resultElement->appendChild( $doc->createElement("Error"));
							$errorElement->setAttribute( "cd", $value['ErrorCd'] );
							$errorElement->appendChild( $doc->createTextNode( $value['ErrorMessage'] ));
						}
						break;

					case ServiceSelfBilling::CMD_ENQUEUE:
						$orderidElement = $resultElement->appendChild( $doc->createElement("OrderId"));
						$orderidElement->appendChild($doc->createTextNode($value['OrderId']));
						$execresultElement = $resultElement->appendChild( $doc->createElement("ExecResult"));
						$execresultElement->appendChild($doc->createTextNode($value['ExecResult']));
						if(!empty($value['ErrorCd'])) {
							$errorElement = $resultElement->appendChild( $doc->createElement("Error"));
							$errorElement->setAttribute( "cd", $value['ErrorCd'] );
							$errorElement->appendChild( $doc->createTextNode( $value['ErrorMessage'] ));
						}
						break;

					case ServiceSelfBilling::CMD_COUNT_TARGETS:
						$countElement = $resultElement->appendChild( $doc->createElement("Count"));
						$countElement->appendChild($doc->createTextNode($value['Count']));
						break;

					case ServiceSelfBilling::CMD_IS_TARGET:
						$orderidElement = $resultElement->appendChild( $doc->createElement("OrderId"));
						$orderidElement->appendChild($doc->createTextNode($value['OrderId']));
						$execresultElement = $resultElement->appendChild( $doc->createElement("ExecResult"));
						$execresultElement->appendChild($doc->createTextNode($value['ExecResult']));
						if(!empty($value['ErrorCd'])) {
							$errorElement = $resultElement->appendChild( $doc->createElement("Error"));
							$errorElement->setAttribute( "cd", $value['ErrorCd'] );
							$errorElement->appendChild( $doc->createTextNode( $value['ErrorMessage'] ));
						}
						break;

					case ServiceSelfBilling::CMD_FETCH_TARGETS:
                    case ServiceSelfBilling::CMD_FETCH_TARGET_CONDITIONS:
						// 顧客郵便番号
						$postalElement = $resultElement->appendChild( $doc->createElement("PostalCode"));
						$postalElement->appendChild($doc->createTextNode($value['PostalCode']));
						// 顧客住所
						$unitaddElement = $resultElement->appendChild( $doc->createElement("UnitingAddress"));
						$unitaddElement->appendChild($doc->createTextNode($value['UnitingAddress']));
						// 漢字氏名
						$nameElement = $resultElement->appendChild( $doc->createElement("NameKj"));
						$nameElement->appendChild($doc->createTextNode($value['NameKj']));
						// 注文Id
						$orderidElement = $resultElement->appendChild( $doc->createElement("OrderId"));
						$orderidElement->appendChild($doc->createTextNode($value['OrderId']));
						// 注文日
						$rorderdateElement = $resultElement->appendChild( $doc->createElement("ReceiptOrderDate"));
						$rorderdateElement->appendChild($doc->createTextNode($value['ReceiptOrderDate']));
						// 購入店名
						$entnameElement = $resultElement->appendChild( $doc->createElement("SiteNameKj"));
						$entnameElement->appendChild($doc->createTextNode($value['SiteNameKj']));
						// 購入店URL
						$enturlElement = $resultElement->appendChild( $doc->createElement("Url"));
						$enturlElement->appendChild($doc->createTextNode($value['Url']));
						// 購入店電話番号
						$cntphonElement = $resultElement->appendChild( $doc->createElement("Phone"));
						$cntphonElement->appendChild($doc->createTextNode($value['Phone']));
						// 合計金額
						$useamountElement = $resultElement->appendChild( $doc->createElement("UseAmount"));
						$useamountElement->appendChild($doc->createTextNode($value['UseAmount']));
						// 小計
						$subtotalElement = $resultElement->appendChild( $doc->createElement("SubTotal"));
						$subtotalElement->appendChild($doc->createTextNode($value['SubTotal']));
						// 送料
						$carriageElement = $resultElement->appendChild( $doc->createElement("CarriageFee"));
						$carriageElement->appendChild($doc->createTextNode($value['CarriageFee']));
						// 決済手数料
						$settleElement = $resultElement->appendChild( $doc->createElement("ChargeFee"));
						$settleElement->appendChild($doc->createTextNode($value['ChargeFee']));
						// 請求回数
						$claimElement = $resultElement->appendChild( $doc->createElement("ReIssueCount"));
						$claimElement->appendChild($doc->createTextNode($value['ReIssueCount']));
						// 支払期限日
						$paydateElement = $resultElement->appendChild( $doc->createElement("LimitDate"));
						$paydateElement->appendChild($doc->createTextNode($value['LimitDate']));
						// バーコードデータ
						$barcodeElement = $resultElement->appendChild( $doc->createElement("Cv_BarcodeData"));
						$barcodeElement->appendChild($doc->createTextNode($value['Cv_BarcodeData']));
						// バーコード文字列1
						$barcodestr1Element = $resultElement->appendChild( $doc->createElement("Cv_BarcodeString1"));
						$barcodestr1Element->appendChild($doc->createTextNode($value['Cv_BarcodeString1']));
						// バーコード文字列2
						$barcodestr2Element = $resultElement->appendChild( $doc->createElement("Cv_BarcodeString2"));
						$barcodestr2Element->appendChild($doc->createTextNode($value['Cv_BarcodeString2']));
						// 郵貯文字列
						$yudtElement = $resultElement->appendChild( $doc->createElement("Yu_DtCode"));
						$yudtElement->appendChild($doc->createTextNode($value['Yu_DtCode']));

						// 商品情報
						$itemsElement = $resultElement->appendChild( $doc->createElement("OrderItems"));
						$i = 1;
						foreach($value['OrderItems'] as $item) {
							$itemElement = $itemsElement->appendChild( $doc->createElement("OrderItem"));
							// 商品名
							$itemnameElement = $itemElement->appendChild( $doc->createElement("ItemNameKj". $i));
							$itemnameElement->appendChild($doc->createTextNode($item["ItemNameKj". $i]));
							// 数量
							$itemnumElement = $itemElement->appendChild( $doc->createElement("ItemNum". $i));
							$itemnumElement->appendChild($doc->createTextNode($item["ItemNum". $i]));
							// 単価
							$upriseElement = $itemElement->appendChild( $doc->createElement("UnitPrice". $i));
							$upriseElement->appendChild($doc->createTextNode($item["UnitPrice". $i]));
							// 金額
							$moneyElement = $itemElement->appendChild( $doc->createElement("SumMoney". $i));
							$moneyElement->appendChild($doc->createTextNode($item["SumMoney". $i]));
							if(date('Y-m-d') > '2019-09-30'){
    							// 消費税
    							$taxRateElement = $itemElement->appendChild( $doc->createElement("TaxRate". $i));
    							$taxRateElement->appendChild($doc->createTextNode($item["TaxRate". $i]));
							}
							$i++;
						}
						// 小計
						$subtotal2Element = $resultElement->appendChild( $doc->createElement("SubTotal"));
						$subtotal2Element->appendChild($doc->createTextNode($value['SubTotal']));
						// 任意注文番号
						$entoidElement = $resultElement->appendChild( $doc->createElement("Ent_OrderId"));
						$entoidElement->appendChild($doc->createTextNode($value['Ent_OrderId']));
						// うち消費税額
						$cfeeElement = $resultElement->appendChild( $doc->createElement("TaxAmount"));
						$cfeeElement->appendChild($doc->createTextNode($value['TaxAmount']));
						if(date('Y-m-d') > '2019-09-30'){
    						// ８％対象合計金額
    						$suAmount1Element = $resultElement->appendChild( $doc->createElement("SubUseAmount_1"));
    						$suAmount1Element->appendChild($doc->createTextNode($value['SubUseAmount_1']));
    						// ８％対象消費税額
    						$stAmount1Element = $resultElement->appendChild( $doc->createElement("SubTaxAmount_1"));
    						$stAmount1Element->appendChild($doc->createTextNode($value['SubTaxAmount_1']));
    						// １０％対象合計金額
    						$suAmount2Element = $resultElement->appendChild( $doc->createElement("SubUseAmount_2"));
    						$suAmount2Element->appendChild($doc->createTextNode($value['SubUseAmount_2']));
    						// １０％対象消費税額
    						$stAmount2Element = $resultElement->appendChild( $doc->createElement("SubTaxAmount_2"));
    						$stAmount2Element->appendChild($doc->createTextNode($value['SubTaxAmount_2']));
						}
						// CVS収納代行会社名
						$cvagentElement = $resultElement->appendChild( $doc->createElement("Cv_ReceiptAgentName"));
						$cvagentElement->appendChild($doc->createTextNode($value['Cv_ReceiptAgentName']));
						// CVS収納代行加入者名
						$cvagentcdElement = $resultElement->appendChild( $doc->createElement("Cv_SubscriberName"));
						$cvagentcdElement->appendChild($doc->createTextNode($value['Cv_SubscriberName']));
						// 銀行口座 - 銀行コード
						$cvagentcdElement = $resultElement->appendChild( $doc->createElement("Bk_BankCode"));
						$cvagentcdElement->appendChild($doc->createTextNode($value['Bk_BankCode']));
						// 銀行口座 - 支店コード
						$branchcdElement = $resultElement->appendChild( $doc->createElement("Bk_BranchCode"));
						$branchcdElement->appendChild($doc->createTextNode($value['Bk_BranchCode']));
						// 銀行名
						$banknameElement = $resultElement->appendChild( $doc->createElement("Bk_BankName"));
						$banknameElement->appendChild($doc->createTextNode($value['Bk_BankName']));
						// 銀行口座 - 支店名
						$branchnameElement = $resultElement->appendChild( $doc->createElement("Bk_BranchName"));
						$branchnameElement->appendChild($doc->createTextNode($value['Bk_BranchName']));
						// 銀行口座 - 口座種別
						$depclassElement = $resultElement->appendChild( $doc->createElement("Bk_DepositClass"));
						$depclassElement->appendChild($doc->createTextNode($value['Bk_DepositClass']));
						// 銀行口座 - 口座番号
						$acnumElement = $resultElement->appendChild( $doc->createElement("Bk_AccountNumber"));
						$acnumElement->appendChild($doc->createTextNode($value['Bk_AccountNumber']));
						// 銀行口座 - 口座名義
						$acholElement = $resultElement->appendChild( $doc->createElement("Bk_AccountHolder"));
						$acholElement->appendChild($doc->createTextNode($value['Bk_AccountHolder']));
						// 銀行口座 - 口座名義カナ
						$acholknElement = $resultElement->appendChild( $doc->createElement("Bk_AccountHolderKn"));
						$acholknElement->appendChild($doc->createTextNode($value['Bk_AccountHolderKn']));
						// ゆうちょ口座 - 加入者名
						$yunameElement = $resultElement->appendChild( $doc->createElement("Yu_SubscriberName"));
						$yunameElement->appendChild($doc->createTextNode($value['Yu_SubscriberName']));
						// ゆうちょ口座 - 口座番号
						$yunumElement = $resultElement->appendChild( $doc->createElement("Yu_AccountNumber"));
						$yunumElement->appendChild($doc->createTextNode($value['Yu_AccountNumber']));
						// ゆうちょ口座 - 払込負担区分
						$yuclassElement = $resultElement->appendChild( $doc->createElement("Yu_ChargeClass"));
						$yuclassElement->appendChild($doc->createTextNode($value['Yu_ChargeClass']));
						// ゆうちょ口座 - MT用OCRコード1
						$yumtcd1Element = $resultElement->appendChild( $doc->createElement("Yu_MtOcrCode1"));
						$yumtcd1Element->appendChild($doc->createTextNode($value['Yu_MtOcrCode1']));
						// ゆうちょ口座 - MT用OCRコード1
						$yumtcd2Element = $resultElement->appendChild( $doc->createElement("Yu_MtOcrCode2"));
						$yumtcd2Element->appendChild($doc->createTextNode($value['Yu_MtOcrCode2']));
						if(date('Y-m-d') > '2019-09-30'){
						// 事業者登録番号
    						$cNumberElement = $resultElement->appendChild( $doc->createElement("CorporationNumber"));
    						$cNumberElement->appendChild($doc->createTextNode($value['CorporationNumber']));
						}
						if(isset($value['ConfirmNumber']) && isset($value['CustomerNumber'])){
						//ペイジー
    						$confirmNumberElement = $resultElement->appendChild( $doc->createElement("ConfirmNumber"));
    						$confirmNumberElement->appendChild($doc->createTextNode($value['ConfirmNumber']));
    						$customerNumberElement = $resultElement->appendChild( $doc->createElement("CustomerNumber"));
    						$customerNumberElement->appendChild($doc->createTextNode($value['CustomerNumber']));
    						$bkNumberElement = $resultElement->appendChild( $doc->createElement("Bk_Number"));
		            $bkNumberElement->appendChild($doc->createTextNode($value['Bk_Number']));
						}
						// マイページログインパスワード
						if(isset($value['MypagePassword'])){
						  $mypagePasswordElement = $resultElement->appendChild( $doc->createElement("MypagePassword"));
						  $mypagePasswordElement->appendChild($doc->createTextNode($value['MypagePassword']));
						}
						// 注文マイページの利用
						if(isset($value['MypageUrl'])){
    						$mypageUrlElement = $resultElement->appendChild( $doc->createElement("MypageUrl"));
    						$mypageUrlElement->appendChild($doc->createTextNode($value['MypageUrl']));
						}
						// クレジット手続き期限日
						if(isset($value['CreditLimitDate'])){
    						$creditLimitDateElement = $resultElement->appendChild( $doc->createElement("CreditLimitDate"));
    						$creditLimitDateElement->appendChild($doc->createTextNode($value['CreditLimitDate']));
						}
						break;

					case ServiceSelfBilling::CMD_PROCESSED:
						$orderidElement = $resultElement->appendChild( $doc->createElement("OrderId"));
						$orderidElement->appendChild($doc->createTextNode($value['OrderId']));
						$execresultElement = $resultElement->appendChild( $doc->createElement("ExecResult"));
						$execresultElement->appendChild($doc->createTextNode($value['ExecResult']));
						if(!empty($value['ErrorCd'])) {
							$errorElement = $resultElement->appendChild( $doc->createElement("Error"));
							$errorElement->setAttribute( "cd", $value['ErrorCd'] );
							$errorElement->appendChild( $doc->createTextNode( $value['ErrorMessage'] ));
						}
						break;
					default:
						break;
						// ここには絶対にやってこない
				}
			}

		}

		// 文字列として返却
		return $doc->saveXML();
	}
}