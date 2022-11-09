<?php
namespace api\classes\Service\Response;

/**
 * 注文修正APIのレスポンスを管理するクラス
 */
class ServiceResponseModify extends ServiceResponseAbstract {
	/** レスポンスノード名定数：注文ID @var string */
	const RESULT_KEY_ORDER_ID = 'orderId';

	/**
	 * 任意注文番号
	 * @var string
	 */
	public $orderId;

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

		// /response/messages
		$messagesElement = $rootElement->appendChild( $doc->createElement("messages") );
		foreach ( $this->messages as $message ) {
            // /response/messages/message
			$messageElement = $doc->createElement("message");
			$messageElement->setAttribute( "cd", $message->messageCd );
			$messageElement->appendChild( $doc->createTextNode( $message->messageText ) );
			$messagesElement->appendChild( $messageElement );
		}

		// 文字列として返却
		return $doc->saveXML();
	}
}