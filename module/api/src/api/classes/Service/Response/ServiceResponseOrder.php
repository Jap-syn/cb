<?php
namespace api\classes\Service\Response;

use api\classes\Service\Response\ServiceResponseAbstract;

/**
 * 注文登録APIのレスポンスを管理するクラス
 */
class ServiceResponseOrder extends ServiceResponseAbstract {

    /**
     * 任意注文番号
     * @var string
     */
    public $orderId;

    /**
     * 自動採番された注文番号
     * @var string
     */
    public $systemOrderId;

    /**
     * 与信状況コードを返却するかの判定
     * @var boolean
     */
    public $rtOrderStatus;

    /**
     * 与信状況コード
     * @var array
     */
    public $orderStatus;

    /**
     * オブジェクトのシリアライズ
     * @return string シリアライズされた文字列
     */
    public function serialize() {
        $doc = new \DOMDocument("1.0", "utf-8");

        // response
        $rootElement = $doc->appendChild( $doc->createElement("response") );

        // status
        $statusElement = $rootElement->appendChild( $doc->createElement("status") );
        $statusElement->appendChild( $doc->createTextNode($this->status) );

        // orderId
        $orderIdElement = $rootElement->appendChild( $doc->createElement("orderId") );
        $orderIdElement->appendChild( $doc->createTextNode($this->orderId) );

        // systemOrderId
        if ( ServiceResponseAbstract::SUCCESS === $this->status ) {
            $systemOrderIdElement = $rootElement->appendChild( $doc->createElement("systemOrderId") );
            $systemOrderIdElement->appendChild( $doc->createTextNode($this->systemOrderId) );
        }

        // messages
        $messagesElement = $rootElement->appendChild( $doc->createElement("messages") );
        foreach ( $this->messages as $message ) {
            $messageElement = $doc->createElement("message");
            $messageElement->setAttribute( "cd", $message->messageCd );
            $messageElement->appendChild( $doc->createTextNode( $message->messageText ) );
            $messagesElement->appendChild( $messageElement );
        }

        // orderstatus
        // → 全件orderstatusを追加するよう仕様変更（2014.4.28 eda）
        $orderStatusElement = $rootElement->appendChild( $doc->createElement("orderStatus") );
        $orderStatusElement->setAttribute( "cd", $this->orderStatus['cd']);
        $orderStatusElement->appendChild( $doc->createTextNode( $this->orderStatus['msg'] ) );

        // 文字列として返却
        return $doc->saveXML();
    }
}