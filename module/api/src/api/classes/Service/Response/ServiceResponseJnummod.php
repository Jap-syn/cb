<?php
namespace api\classes\Service\Response;

use api\classes\Service\Response\ServiceResponseAbstract;

/**
 * 伝票番号修正APIのレスポンスを管理するクラス
 */
class ServiceResponseJnummod extends ServiceResponseAbstract {
	/** レスポンスノード名定数：注文ID @var string */
	const RESULT_KEY_ORDER_ID = 'orderId';

	/** レスポンスノード名定数：配送会社ID @var string */
	const RESULT_KEY_DELIV_ID = 'delivId';

	/** レスポンスノード名定数：配送会社名 @var string */
    const RESULT_KEY_DELIV_NAME = 'delivName';

    /** レスポンスノード名定数：伝票番号 @var string */
    const RESULT_KEY_JOURNAL_NUM = 'journalNum';


	/**
	 * 結果項目リスト
	 *
	 * @access protected
	 * @var array
	 */
	protected $_results = array();


	/**
	 * オブジェクトのシリアライズ
	 *
	 * @return string シリアライズされた文字列
	 */
	public function serialize() {

		$doc = new \DOMDocument("1.0", "utf-8");

        // /response
        $rootElement = $doc->appendChild( $doc->createElement("response") );

        // /response/status
        $statusElement = $rootElement->appendChild( $doc->createElement("status") );
        $statusElement->appendChild( $doc->createTextNode($this->status) );

        // /response/results/result/orderId
        $orderElement = $doc->createElement(self::RESULT_KEY_ORDER_ID);
        $orderElement->appendChild($doc->createTextNode($this->_orderId));
        $rootElement->appendChild($orderElement);

        // /response/results/result/delivId
        $delivIdElement = $doc->createElement(self::RESULT_KEY_DELIV_ID);

        // 配送会社名
        $delivIdElement->setAttribute('name', $this->_delivName);
        $delivIdElement->appendChild($doc->createTextNode($this->_delivId));

        $rootElement->appendChild($delivIdElement);

        // /response/results/reslut/journalNum
        $journalNumElement = $doc->createElement(self::RESULT_KEY_JOURNAL_NUM);
        $journalNumElement->appendChild($doc->createTextNode($this->_journalNum));
        $rootElement->appendChild($journalNumElement);


        // /response/messages
        $messagesElement = $rootElement->appendChild( $doc->createElement("messages") );

        // /response/messages/message
        foreach ( $this->messages as $message ) {
            $messageElement = $doc->createElement("message");
            $messageElement->setAttribute( "cd", $message->messageCd );
            $messageElement->appendChild( $doc->createTextNode( $message->messageText ) );

            $messagesElement->appendChild( $messageElement );
        }

        // 文字列として返却
        return $doc->saveXML();
	}
}