<?php
namespace api\classes\Service\Response;

use api\classes\Service\Response\ServiceResponseAbstract;

/**
 * 伝票登録APIのレスポンスを管理するクラス
 */
class ServiceResponsePayeasy extends ServiceResponseAbstract {
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

        // 文字列として返却
        return $doc->saveXML();
    }
}