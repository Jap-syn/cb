<?php
namespace api\classes\Service\Response;

use api\classes\Service\Response\ServiceResponseAbstract;

/**
 * 伝票登録APIのレスポンスを管理するクラス
 */
class ServiceResponseShipping extends ServiceResponseAbstract {
    /** レスポンスノード名定数：注文ID @var string */
    const RESULT_KEY_ORDER_ID = 'orderId';

    /** レスポンスノード名定数：配送会社ID @var string */
    const RESULT_KEY_DELIV_ID = 'delivId';

    /** レスポンスノード名定数：伝票番号 @var string */
    const RESULT_KEY_JOURNAL_NUM = 'journalNum';

    /**
     * 任意注文番号
     * @var string
     */
    public $orderId;

    /**
     * 配送会社ID
     * @var string
     */
    public $delivId;

    /**
     * 配送会社名
     * @var string
     */
    public $delivName;

    /**
     * 伝票番号
     * @var string
     */
    public $journalNum;

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

        // /response/delivId
        $deliIdElement = $rootElement->appendChild( $doc->createElement(self::RESULT_KEY_DELIV_ID) );
        $deliIdElement->appendChild( $doc->createTextNode($this->delivId) );
        if(isset($this->delivName) && strlen($this->delivName)) {
            // /response/delivId/@name
            $deliIdElement->setAttribute('name', $this->delivName);
        }

        // /response/journalNum
        $jnElement = $rootElement->appendChild($doc->createElement(self::RESULT_KEY_JOURNAL_NUM));
        $jnElement->appendChild($doc->createTextNode($this->journalNum));

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