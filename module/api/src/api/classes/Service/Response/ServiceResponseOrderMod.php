<?php
namespace api\classes\Service\Response;

use api\classes\Service\Response\ServiceResponseAbstract;
/**
 * 注文修正APIのレスポンスを管理するクラス
 */
class ServiceResponseOrderMod extends ServiceResponseAbstract {
	/** レスポンスノード名定数：任意注文番号 @var string */
	const RESULT_KEY_ORDER_ID = 'orderId';

	/** レスポンスノード名定数：後払い注文ＩＤ @var string */
	const RESULT_KEY_SYSTEM_ORDER_ID = 'systemOrderId';

	/** レスポンスノード名定数：与信状況コード @var string */
	const RESULT_KEY_ORDER_STATUS = 'orderStatus';


	/**
	 * 任意注文番号
	 * @var string
	 */
	public $orderId;

	/**
	 * 後払い注文ID
	 * @var string
	 */
	public $systemOrderId;

	/**
	 * 与信状況コード
	 * @var string
	 */
	public $orderStatus;

	/**
	 * オブジェクトのシリアライズ
	 * @return string シリアライズされた文字列
	 */
	public function serialize() {
		$doc = new \DOMDocument("1.0", "utf-8");

		// /response
		$rootElement = $doc->appendChild( $doc->createElement("response") );

		// /response/status
		$statusElement = $rootElement->appendChild( $doc->createElement("status") );
		$statusElement->appendChild( $doc->createTextNode($this->status) );

		// /response/orderId
		$orderIdElement = $rootElement->appendChild( $doc->createElement(self::RESULT_KEY_ORDER_ID) );
		$orderIdElement->appendChild( $doc->createTextNode($this->orderId) );

		// /response/systemOrderId
		$systemOrderIdElement = $rootElement->appendChild( $doc->createElement(self::RESULT_KEY_SYSTEM_ORDER_ID) );
		$systemOrderIdElement->appendChild( $doc->createTextNode($this->systemOrderId) );

		// /response/messages
		$messagesElement = $rootElement->appendChild( $doc->createElement("messages") );
		foreach ( $this->messages as $message ) {
            // /response/messages/message
			$messageElement = $doc->createElement("message");
			$messageElement->setAttribute( "cd", $message->messageCd );
			$messageElement->appendChild( $doc->createTextNode( $message->messageText ) );
			$messagesElement->appendChild( $messageElement );
		}

		// /response/systemOrderId
		$orderStatusElement = $rootElement->appendChild( $doc->createElement(self::RESULT_KEY_ORDER_STATUS) );
		$orderStatusElement->setAttribute( "cd", $this->orderStatus['cd']);
		$orderStatusElement->appendChild( $doc->createTextNode( $this->orderStatus['msg'] ) );

		// 文字列として返却
		return $doc->saveXML();
	}
}