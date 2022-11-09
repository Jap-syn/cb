<?php
namespace api\classes\Service\Response;

use api\classes\Service\Response\ServiceResponseAbstract;

/**
 * サイト情報更新APIのレスポンスを管理するクラス
 */
class ServiceResponseSitemod extends ServiceResponseAbstract {
	/** レスポンスノード名定数：サイトID @var string */
	const RESULT_KEY_SITE_ID = 'siteId';

	/** レスポンスノード名定数：サイト名 @var string */
	const RESULT_KEY_SITE_NAME = 'siteName';

	/** レスポンスノード名定数：サイトURL @var string */
	const RESULT_KEY_SITE_URL = 'siteUrl';

	/** レスポンスノード名定数：連絡先電話番号 @var string */
	const RESULT_KEY_PHONE = 'phone';

	/**
	 * サイトID
	 * @var int
	 */
	public $siteId;

	/**
	 * サイト名
	 * @var string
	 */
	public $siteName;

	/**
	 * サイトURL
	 * @var string
	 */
	public $siteUrl;

	/**
	 * 連絡先電話番号
	 * @var string
	 */
	public $phone;

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

		// /response/messages
		$messagesElement = $rootElement->appendChild( $doc->createElement("messages") );
		foreach ( $this->messages as $message ) {
            // /response/messages/message
			$messageElement = $doc->createElement("message");
			$messageElement->setAttribute( "cd", $message->messageCd );
			$messageElement->appendChild( $doc->createTextNode( $message->messageText ) );
			$messagesElement->appendChild( $messageElement );
		}

		// /response/results
		$resultsElement = $rootElement->appendChild($doc->createElement('results'));

		// /response/results/result
		$resultElement = $resultsElement->appendChild($doc->createElement('result'));

		// /response/results/siteId
		$siteIdElement = $resultElement->appendChild($doc->createElement(self::RESULT_KEY_SITE_ID));
		$siteIdElement->appendChild($doc->createTextNode($this->siteId));

		// /response/results/siteName
		$siteNameElement = $resultElement->appendChild($doc->createElement(self::RESULT_KEY_SITE_NAME));
		$siteNameElement->appendChild($doc->createTextNode($this->siteName));

		// /response/results/siteUrl
		$siteUrlElement = $resultElement->appendChild($doc->createElement(self::RESULT_KEY_SITE_URL));
		$siteUrlElement->appendChild($doc->createTextNode($this->siteUrl));

		// /response/results/sitePhone
		$phoneElement = $resultElement->appendChild($doc->createElement(self::RESULT_KEY_PHONE));
		$phoneElement->appendChild($doc->createTextNode($this->phone));

		// 文字列として返却
		return $doc->saveXML();
	}
}