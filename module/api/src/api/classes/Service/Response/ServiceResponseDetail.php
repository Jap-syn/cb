<?php
namespace api\classes\Service\Response;

use api\Application;
use api\classes\Service\Response\ServiceResponseAbstract;

/**
 * 注文状況取得APIのレスポンスを管理するクラス
 */
class ServiceResponseDetail extends ServiceResponseAbstract {
	/** レスポンスノード名定数：注文ID @var string */
	const RESULT_KEY_ORDER_ID = 'orderId';

	/** レスポンスノード名定数：任意注文番号 @var string */
	const RESULT_KEY_ENT_ORDER_ID = 'entOrderId';

	/** レスポンスノード名定数：注文受日 @var string */
	const RESULT_KEY_ORDER_DATE = 'orderDate';

    /** レスポンスノード名定数：注文登録日時 @var string */
    const RESULT_KEY_ORDER_DATE_TIME = 'orderDateTime';

    /** レスポンスノード名定数：処理ステータス @var string */
    const RESULT_KEY_ORDER_STATUS = 'orderStatus';

    /** レスポンスノード名定数：利用額 @var int */
    const RESULT_KEY_PAYMENT = 'payment';

    /** レスポンスノード名定数：サイトID @var int */
    const RESULT_KEY_SITE_ID = 'siteId';

    /** レスポンスノード名定数：サイト名 @var string */
    const RESULT_KEY_SITE_NAME = 'siteName';

    /** レスポンスノード名定数：サイトURL @var string */
    const RESULT_KEY_SITE_URL = 'siteUrl';

    /** レスポンスノード名定数：キャンセル状況 @var int */
    const RESULT_KEY_IS_CANCELED = 'isCanceled';

    /** レスポンスノード名定数：キャンセル日 @var string */
    const RESULT_KEY_CANCEL_DATE = 'cancelDate';

    /** レスポンスノード名定数：キャンセル理由 @var string */
    const RESULT_KEY_CANCEL_REASON = 'cancelReason';

    /** レスポンスノード名定数：配送伝票登録状況 @var int */
    const RESULT_KEY_IS_SHIPPED = 'isShipped';

    /** レスポンスノード名定数：伝票番号登録日 @var string */
    const RESULT_KEY_SHIPPING_DATE = 'shippingDate';

    /** レスポンスノード名定数：配送会社ID @var int */
    const RESULT_KEY_DELIVERY_ID = 'deliveryId';

    /** レスポンスノード名定数：配送会社名 @var string */
    const RESULT_KEY_DELIVERY_NAME = 'deliveryName';

    /** レスポンスノード名定数：伝票番号 @var string */
    const RESULT_KEY_JOURNAL_NUMBER = 'journalNumber';

    /** レスポンスノード名定数：請求書発行状況 @var int */
    const RESULT_KEY_IS_CLAIMED = 'isClaimed';

    /** レスポンスノード名定数：請求日 @var string */
    const RESULT_KEY_CLAIM_DATE = 'claimDate';

    /** レスポンスノード名定数：請求フォーマット @var int */
    const RESULT_KEY_CLAIM_FORMAT = 'claimFormat';

    /** レスポンスノード名定数：請求期限 @var string */
    const RESULT_KEY_CLAIM_LIMIT_DATE = 'claimLimitDate';

    /** レスポンスノード名定数：もうすぐお支払メール送信日 @var string */
    const RESULT_KEY_MAIL_PAYMENT_SOON_DATE = 'MailPaymentSoonDate';

    /** レスポンスノード名定数：支払い期限経過メール送信日 @var string */
    const RESULT_KEY_MAIL_LIMIT_PASSAGE_DATE = 'MailLimitPassageDate';

    /** レスポンスノード名定数：着荷確認状況 @var int */
    const RESULT_KEY_IS_ARRIVAL_CONFIRMED = 'isArrivalConfirmed';

    /** レスポンスノード名定数：着荷確認日時 @var string */
    const RESULT_KEY_ARRIVAL_CONFIRMED_DATE = 'arrivalConfirmedDate';

    /** レスポンスノード名定数：入金確認状況 @var int */
    const RESULT_KEY_IS_RECEIPT_CONFIRMED = 'isReceiptConfirmed';

    /** レスポンスノード名定数：入金確認日 @var string */
    const RESULT_KEY_RECEIPT_DATE = 'receiptDate';

    /** レスポンスノード名定数：立替支払状況 @var int */
    const RESULT_KEY_IS_REIMBURSED = 'isReimbursed';

    /** レスポンスノード名定数：立替日 @var string */
    const RESULT_KEY_REIMBURSED_DATE = 'reimbursedDate';

    /** レスポンスノード名定数：トラッキングID @var string */
    const RESULT_KEY_TRACKING_ID = 'trackingId';

    /** レスポンスノード名定数：強制解約通知日 @var string */
    const RESULT_KEY_CANCEL_NOTICE_DATE = 'cancelNoticeDate';


    /** 注文状況コード定数：与信中 @var int */
    const STATUS_NOW_JUDGE = 0;

    /** 注文状況コード定数：伝票番号入力待ち @var int */
    const STATUS_NOW_JOURNAL_NUM = 1;

    /** 注文状況コード定数：請求書発行待ち @var int */
    const STATUS_ISSUE_CLAIMED= 2;

    /** 注文状況コード定数：入金確認待ち @var int */
    const STATUS_RECEIPT_CONFIRM= 3;

    /** 注文状況コード定数：一部入金 @var int */
    const STATUS_RECEIPT_OK= 4;

    /** 注文状況コード定数：クローズ @var int */
    const STATUS_CLOCE= 9;

    /** 注文状況コード定数：キャンセル申請 @var int */
    const STATUS_CANCEL_REQUEST= 10;

    /** 注文状況コード定数：キャンセル済み @var int */
    const STATUS_CANCELED = 11;

    /** 注文状況コード定数：与信NG @var int */
    const STATUS_JUDGE_NG = 99;

    /** 注文状況コード定数：ID不正 @var int */
    const STATUS_INVALID = -1;

    /** 注文状況メッセージ：与信中 @var string */
    const STS_MSG_NOW_JUDGE = '与信中';

    /** 注文状況メッセージ：伝票番号入力待ち @var string */
    const STS_MSG_JOURNAL_NUM = '伝票番号入力待ち';

    /** 注文状況メッセージ：請求書発行待ち @var string */
    const STS_MSG_ISSUE_CLAIMED = '請求書発行待ち';

    /** 注文状況メッセージ：入金確認待ち @var string */
    const STS_MSG_RECEIPT_CONFIRM = '入金確認待ち';

    /** 注文状況メッセージ：一部入金 @var string */
    const STS_MSG_RECEIPT_OK = '一部入金済み';

    /** 注文状況メッセージ：クローズ @var string */
    const STS_MSG_CLOSE = 'クローズ';

    /** 注文状況メッセージ：キャンセル申請中 @var string */
    const STS_MSG_CANCEL_REQUESET = 'キャンセル申請中';

    /** 注文状況メッセージ：キャンセル済 @var string */
    const STS_MSG_CANCELED = 'キャンセル済';

    /** 注文状況メッセージ：与信NG @var string */
    const STS_MSG_JUDGE_NG = '与信NG';

    /** 注文状況メッセージ：ID不正 @var string */
    const STS_MSG_INVALID = 'ID不正';


    /**
     * 注文状況コードと注文状況テキストのマップを取得する
     *
     * @static
     * @access protected
     * @return array キーに注文状況コード、値に対応する注文状況テキストを格納した連想配列
     */
    protected static function __getOrderStatusMap() {
        return array(
            // 与信中
            self::STATUS_NOW_JUDGE => self::STS_MSG_NOW_JUDGE,

            // 伝票番号入力待ち
            self::STATUS_NOW_JOURNAL_NUM => self::STS_MSG_JOURNAL_NUM,

            // 請求書発行待ち
            self::STATUS_ISSUE_CLAIMED => self::STS_MSG_ISSUE_CLAIMED,

            // 入金確認待ち
            self::STATUS_RECEIPT_CONFIRM => self::STS_MSG_RECEIPT_CONFIRM,

            // 一部入金
            self::STATUS_RECEIPT_OK => self::STS_MSG_RECEIPT_OK,

            // クローズ
            self::STATUS_CLOCE => self::STS_MSG_CLOSE,

            // キャンセル申請中
            self::STATUS_CANCEL_REQUEST => self::STS_MSG_CANCEL_REQUESET,

            // キャンセル済
            self::STATUS_CANCELED => self::STS_MSG_CANCELED,

            // 与信NG
            self::STATUS_JUDGE_NG => self::STS_MSG_JUDGE_NG,

            // ID不正
            self::STATUS_INVALID => self::STS_MSG_INVALID,
        );
    }
    public static function getStatusMap() {

        return self::__getOrderStatusMap();
    }

    /**
     * 指定任意注文番号に対応するID変換結果項目を追加する
     *
     * @param string $orderId 注文ID
     * @param array $orderDatas T_Orderデータイメージの連想配列の配列
     * @param int $forceCancelClaimPattern 強制解約通知請求書
     * @param string $cancelNoticeDate 強制解約通知日
     * @return Service_Response_Idmap このインスタンス
     */
    public function addResult($orderId, $orderDatas, $forceCancelClaimPattern, $cancelNoticeDate) {

        $result = array(
            self::RESULT_KEY_ORDER_ID => $orderId,
            self::RESULT_KEY_ENT_ORDER_ID => isset($orderDatas['Ent_OrderId'])?$orderDatas['Ent_OrderId']:"",
            self::RESULT_KEY_ORDER_STATUS => self::STATUS_INVALID

        );

        foreach($orderDatas as $key => $value) {
            if ( $key != self::RESULT_KEY_ORDER_ID ) {
                 $result[$key] = $value;
            }
        }

        // 強制解約通知請求書が設定されている場合は強制解約通知日をレスポンスに含める
        if (!empty($forceCancelClaimPattern) && ($forceCancelClaimPattern != 0)) {
            $result[self::RESULT_KEY_CANCEL_NOTICE_DATE] = is_null($cancelNoticeDate) ? '' : $cancelNoticeDate;
        }

        $this->_results[] = $result;

        return $this;
    }

	/**
	 * オブジェクトのシリアライズ
	 * @return string シリアライズされた文字列
	 */
	public function serialize() {

	    $stsMap = self::getStatusMap();

		$doc = new \DOMDocument("1.0", "utf-8");

		// /response
		$rootElement = $doc->appendChild( $doc->createElement("response") );

		// /response/status
		$statusElement = $rootElement->appendChild( $doc->createElement("status") );
		$statusElement->appendChild( $doc->createTextNode($this->status) );

        // /response/messages
        $messagesElement = $rootElement->appendChild( $doc->createElement("messages") );
        foreach ( $this->messages as $message ) {
            // /response/messages/message
            $messageElement = $doc->createElement("message");
            $messageElement->setAttribute( "cd", $message->messageCd );
            $messageElement->appendChild( $doc->createTextNode( $message->messageText ) );
            $messagesElement->appendChild( $messageElement );
        }

        //--- response---
        $resultsElement = $rootElement->appendChild($doc->createElement('results'));
        foreach($this->_results as $result) {

            // /response/results/result
            $resultElement = $doc->createElement('result');

                        // /response/results/result/orderId
            $orderElement = $doc->createElement(self::RESULT_KEY_ORDER_ID);
            $orderElement->appendChild($doc->createTextNode($result[self::RESULT_KEY_ORDER_ID]));
            $resultElement->appendChild($orderElement);

            // /response/results/result/entOrderId
            if(isset($result[self::RESULT_KEY_ENT_ORDER_ID])) {
                $entIdElement = $doc->createElement(self::RESULT_KEY_ENT_ORDER_ID);
                $entIdElement->appendChild($doc->createTextNode($result[self::RESULT_KEY_ENT_ORDER_ID]));
                $resultElement->appendChild($entIdElement);
            }

            //注文状況テキスト
            $statusText = $stsMap[$result[self::RESULT_KEY_ORDER_STATUS]];
            $orderStatusElement = $doc->createElement(self::RESULT_KEY_ORDER_STATUS);
            $orderStatusElement->setAttribute('orderStatusName', $statusText);

            // 注文Statusが-1の場合以下の要素は表示しない
            if($result[self::RESULT_KEY_ORDER_STATUS] == -1){
                $orderStatusElement->appendChild($doc->createTextNode($result[self::RESULT_KEY_ORDER_STATUS]));
                $resultElement->appendChild($orderStatusElement);
                $resultsElement->appendChild($resultElement);
                continue;
            }
            // /response/results/result/orderDate
            $orderDateElement = $doc->createElement(self::RESULT_KEY_ORDER_DATE);
            $orderDateElement->appendChild($doc->createTextNode($result[self::RESULT_KEY_ORDER_DATE]));
            $resultElement->appendChild($orderDateElement);

            // /response/results/result/orderDateTime
            $orderDateTimeElement = $doc->createElement(self::RESULT_KEY_ORDER_DATE_TIME);
            $orderDateTimeElement->appendChild($doc->createTextNode($result[self::RESULT_KEY_ORDER_DATE_TIME]));
            $resultElement->appendChild($orderDateTimeElement);


            // /response/results/result/orderStatus
            $orderStatusElement->appendChild($doc->createTextNode($result[self::RESULT_KEY_ORDER_STATUS]));
            $resultElement->appendChild($orderStatusElement);

            // /response/results/result/payment
            $paymentElement = $doc->createElement(self::RESULT_KEY_PAYMENT);
            $paymentElement->appendChild($doc->createTextNode($result[self::RESULT_KEY_PAYMENT]));
            $resultElement->appendChild($paymentElement);

            // /response/results/result/siteId
            $siteIdElement = $doc->createElement(self::RESULT_KEY_SITE_ID);
            $siteIdElement->setAttribute('siteName', $result[self::RESULT_KEY_SITE_NAME]);
            // サイト名
            $siteIdElement->appendChild($doc->createTextNode($result[self::RESULT_KEY_SITE_ID]));

            $resultElement->appendChild($siteIdElement);

            // /response/results/result/siteUrl
            $siteUrlElement = $doc->createElement(self::RESULT_KEY_SITE_URL);
            $siteUrlElement->appendChild($doc->createTextNode($result[self::RESULT_KEY_SITE_URL]));
            $resultElement->appendChild($siteUrlElement);

            // /response/results/result/isCanceled
            $isCanceledElement = $doc->createElement(self::RESULT_KEY_IS_CANCELED);
            $isCanceledElement->appendChild($doc->createTextNode($result[self::RESULT_KEY_IS_CANCELED]));
            $resultElement->appendChild($isCanceledElement);

            // /response/results/result/cancelDate
            $cancelDateElement = $doc->createElement(self::RESULT_KEY_CANCEL_DATE);
            $cancelDateElement->appendChild($doc->createTextNode($result[self::RESULT_KEY_CANCEL_DATE]));
            $resultElement->appendChild($cancelDateElement);

            // /response/results/result/cancelReason
            $cancelReasonElement = $doc->createElement(self::RESULT_KEY_CANCEL_REASON);
            $cancelReasonElement->appendChild($doc->createTextNode($result[self::RESULT_KEY_CANCEL_REASON]));
            $resultElement->appendChild($cancelReasonElement);


            // /response/results/result/isShipped
            $isShippedElement = $doc->createElement(self::RESULT_KEY_IS_SHIPPED);
            $isShippedElement->appendChild($doc->createTextNode($result[self::RESULT_KEY_IS_SHIPPED]));
            $resultElement->appendChild($isShippedElement);

            // /response/results/result/shippingDate
            $shippingDateElement = $doc->createElement(self::RESULT_KEY_SHIPPING_DATE);
            $shippingDateElement->appendChild($doc->createTextNode($result[self::RESULT_KEY_SHIPPING_DATE]));
            $resultElement->appendChild($shippingDateElement);

            // /response/results/result/deliveryId

            $deliveryIdElement = $doc->createElement(self::RESULT_KEY_DELIVERY_ID);

            // 配送会社名
            if(!empty($result[self::RESULT_KEY_DELIVERY_ID])){
                $deliveryIdElement->setAttribute('deliveryName', $result[self::RESULT_KEY_DELIVERY_NAME]);
                $deliveryIdElement->appendChild($doc->createTextNode($result[self::RESULT_KEY_DELIVERY_ID]));
            }
            $resultElement->appendChild($deliveryIdElement);

                        // /response/results/result/journalNumber
            $journalNumberElement = $doc->createElement(self::RESULT_KEY_JOURNAL_NUMBER);
            $journalNumberElement->appendChild($doc->createTextNode($result[self::RESULT_KEY_JOURNAL_NUMBER]));
            $resultElement->appendChild($journalNumberElement);

            // /response/results/result/isClaimed
            $isClaimedElement = $doc->createElement(self::RESULT_KEY_IS_CLAIMED);
            $isClaimedElement->appendChild($doc->createTextNode($result[self::RESULT_KEY_IS_CLAIMED]));
            $resultElement->appendChild($isClaimedElement);

            // /response/results/result/claimDate
            $claimDateElement = $doc->createElement(self::RESULT_KEY_CLAIM_DATE);
            $claimDateElement->appendChild($doc->createTextNode($result[self::RESULT_KEY_CLAIM_DATE]));
            $resultElement->appendChild($claimDateElement);

            // /response/results/result/claimFormat
            $claimFormatElement = $doc->createElement(self::RESULT_KEY_CLAIM_FORMAT);
            $claimFormatElement->appendChild($doc->createTextNode($result[self::RESULT_KEY_CLAIM_FORMAT]));
            $resultElement->appendChild($claimFormatElement);

            // /response/results/result/claimLimitDate
            $claimLimitDateElement = $doc->createElement(self::RESULT_KEY_CLAIM_LIMIT_DATE);
            $claimLimitDateElement->appendChild($doc->createTextNode($result[self::RESULT_KEY_CLAIM_LIMIT_DATE]));
            $resultElement->appendChild($claimLimitDateElement);

            // /response/results/result/MailPaymentSoonDate
            $mailPaymentSoonDateElement = $doc->createElement(self::RESULT_KEY_MAIL_PAYMENT_SOON_DATE);
            $mailPaymentSoonDateElement->appendChild($doc->createTextNode($result[self::RESULT_KEY_MAIL_PAYMENT_SOON_DATE]));
            $resultElement->appendChild($mailPaymentSoonDateElement);

            // /response/results/result/MailLimitPassageDate
            $mailLimitPassageDateElement = $doc->createElement(self::RESULT_KEY_MAIL_LIMIT_PASSAGE_DATE);
            $mailLimitPassageDateElement->appendChild($doc->createTextNode($result[self::RESULT_KEY_MAIL_LIMIT_PASSAGE_DATE]));
            $resultElement->appendChild($mailLimitPassageDateElement);

            // /response/results/result/isArrivalConfirmed
            $isArrivalConfirmedElement = $doc->createElement(self::RESULT_KEY_IS_ARRIVAL_CONFIRMED);
            $isArrivalConfirmedElement->appendChild($doc->createTextNode($result[self::RESULT_KEY_IS_ARRIVAL_CONFIRMED]));
            $resultElement->appendChild($isArrivalConfirmedElement);

            // /response/results/result/arrivalConfirmedDate
            $arrivalConfirmedElement = $doc->createElement(self::RESULT_KEY_ARRIVAL_CONFIRMED_DATE);
            $arrivalConfirmedElement->appendChild($doc->createTextNode($result[self::RESULT_KEY_ARRIVAL_CONFIRMED_DATE]));
            $resultElement->appendChild($arrivalConfirmedElement);

            // /response/results/result/isReceiptConfirmed
            $isReceiptConfirmedElement = $doc->createElement(self::RESULT_KEY_IS_RECEIPT_CONFIRMED);
            $isReceiptConfirmedElement->appendChild($doc->createTextNode($result[self::RESULT_KEY_IS_RECEIPT_CONFIRMED]));
            $resultElement->appendChild($isReceiptConfirmedElement);

            // /response/results/result/receiptDate
            $receiptDateElement = $doc->createElement(self::RESULT_KEY_RECEIPT_DATE);
            $receiptDateElement->appendChild($doc->createTextNode($result[self::RESULT_KEY_RECEIPT_DATE]));
            $resultElement->appendChild($receiptDateElement);

            // /response/results/result/isReimbursed
            $isReimbursedElement = $doc->createElement(self::RESULT_KEY_IS_REIMBURSED);
            $isReimbursedElement->appendChild($doc->createTextNode($result[self::RESULT_KEY_IS_REIMBURSED]));
            $resultElement->appendChild($isReimbursedElement);

            // /response/results/result/reimbursedDate
            $reimbursedDateElement = $doc->createElement(self::RESULT_KEY_REIMBURSED_DATE);
            $reimbursedDateElement->appendChild($doc->createTextNode($result[self::RESULT_KEY_REIMBURSED_DATE]));
            $resultElement->appendChild($reimbursedDateElement);

            // /response/results/result/trackingId
            // 届いてから決済利用フラグが【利用する】の場合
            if ( $result['PaymentAfterArrivalFlg'] == '1' ) {
                $trackingIdElement = $doc->createElement(self::RESULT_KEY_TRACKING_ID);
                $trackingIdElement->appendChild($doc->createTextNode($result[self::RESULT_KEY_TRACKING_ID]));
                $resultElement->appendChild($trackingIdElement);
            }

            // /response/results/result/cancelNoticeDate
            if(isset($result[self::RESULT_KEY_CANCEL_NOTICE_DATE])) {
                $cancelNoticeDateElement = $doc->createElement(self::RESULT_KEY_CANCEL_NOTICE_DATE);
                $cancelNoticeDateElement->appendChild($doc->createTextNode($result[self::RESULT_KEY_CANCEL_NOTICE_DATE]));
                $resultElement->appendChild($cancelNoticeDateElement);
            }

            $resultsElement->appendChild($resultElement);
        }

		// 文字列として返却
		return $doc->saveXML();
	}
}