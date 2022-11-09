<?php
namespace api\classes\Service\Response;

use api\classes\Service\Response\ServiceResponseAbstract;

/**
 * 配送会社一覧取得APIのレスポンスを管理するクラス
 */
class ServiceResponseDelivlist extends ServiceResponseAbstract {
    /** レスポンスノード名定数：配送会社コード @var string */
    const RESULT_KEY_DELIV_ID = 'delivId';

    /** レスポンスノード名定数：配送会社名 @var string */
    const RESULT_KEY_NAME = 'name';

    /** レスポンスノード名定数：配送会社略称 @var string */
    const RESULT_KEY_SHORT_NAME = 'shortName';

    /**
     * 結果項目リスト
     *
     * @access protected
     * @var array
     */
    protected $_results = array();

    /**
     * 配送会社行データから結果項目を追加する
     *
     * @param array $delivData M_DeliveryDestinationデータイメージの連想配列
     * @return ServiceResponseStatus このインスタンス
     */
    public function addResult($delivData) {
        $result = array(
            self::RESULT_KEY_DELIV_ID => $delivData['DeliMethodId'],
            self::RESULT_KEY_NAME => $delivData['DeliMethodName'],
            self::RESULT_KEY_SHORT_NAME => $delivData['DeliMethodNameB']
        );

        $this->_results[] = $result;

        return $this;
    }

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
        foreach($this->_results as $result) {
            // /response/results/result
            $resultElement = $doc->createElement('result');

            // /response/results/result/delivId
            $idElement = $doc->createElement(self::RESULT_KEY_DELIV_ID);
            $idElement->appendChild($doc->createTextNode($result[self::RESULT_KEY_DELIV_ID]));
            $resultElement->appendChild($idElement);

            // /response/results/result/name
            $nameElement = $doc->createElement(self::RESULT_KEY_NAME);
            $nameElement->appendChild($doc->createTextNode($result[self::RESULT_KEY_NAME]));
            $resultElement->appendChild($nameElement);

            // /response/results/result/shortName
            $snameElement = $doc->createElement(self::RESULT_KEY_SHORT_NAME);
            $snameElement->appendChild($doc->createTextNode($result[self::RESULT_KEY_SHORT_NAME]));
            $resultElement->appendChild($snameElement);

            $resultsElement->appendChild($resultElement);
        }

        // 文字列として返却
        return $doc->saveXML();
    }
}