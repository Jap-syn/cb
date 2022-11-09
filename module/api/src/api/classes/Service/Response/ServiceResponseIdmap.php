<?php
namespace api\classes\Service\Response;

use api\classes\Service\Response\ServiceResponseAbstract;
use api\classes\Service\Idmap\ServiceIdmapConst;
use api\classes\Service\Idmap\ServiceIdmapIdError;
use api\Application;

/**
 * 注文ID変換APIのレスポンスを管理するクラス
 */
class ServiceResponseIdmap extends ServiceResponseAbstract {
    /** レスポンスノード名定数：任意注文番号 @var string */
    const RESULT_KEY_ENT_ORDER_ID = 'entOrderId';

    /** レスポンスノード名定数：注文ID @var string */
    const RESULT_KEY_ORDER_ID = 'orderId';

    /** レスポンスノード名定数：個別エラー @var string */
    const RESULT_KEY_ID_ERROR = 'error';

    /** 個別エラーコード定数：対応注文なし @var string */
    const ID_ERROR_NOT_EXISTS = 'E001';

    /** 個別エラーコード定数：複数注文該当 @var string */
    const ID_ERROR_ORDER_DUPLICATE = 'E002';

    /** 個別エラーコード定数：その他のエラー @var string */
    const ID_ERROR_OTHERS = 'E999';

    /** 個別エラー定数：対応注文なし @var string */
    const ID_ERR_MSG_NOT_EXISTS = '対応注文なし';

    /** 個別エラー定数：複数注文該当 @var string */
    const ID_ERR_MSG_ORDER_DUPLICATE = '複数注文該当';

    /** 個別エラー定数：その他のエラー @var string */
    const ID_ERR_MSG_OTHERS = 'その他のエラー';

    /**
     * 個別エラーコードと個別エラーテキストのマップを取得する
     *
     * @static
     * @access protected
     * @return array キーに注文状況コード、値に対応する注文状況テキストを格納した連想配列
     */
    protected static function __getErrorMap() {
        return array(
            // 対応注文なし
            self::ID_ERROR_NOT_EXISTS => self::ID_ERR_MSG_NOT_EXISTS,

            // 複数注文該当
            self::ID_ERROR_ORDER_DUPLICATE => self::ID_ERR_MSG_ORDER_DUPLICATE,

            // その他のエラー
            self::ID_ERROR_OTHERS => self::ID_ERR_MSG_OTHERS
        );
    }

    /**
     * 結果項目リスト
     *
     * @access protected
     * @var array
     */
    protected $_results = array();

    /**
     * 指定任意注文番号に対応するID変換結果項目を追加する
     *
     * @param string $entOrderId 任意注文番号
     * @param array $orderDatas T_Orderデータイメージの連想配列の配列
     * @return ServiceResponseIdmap このインスタンス
     */
    public function addResult($entOrderId, $orderDatas) {
        $result = array(
            self::RESULT_KEY_ENT_ORDER_ID => $entOrderId
        );

        if(!$orderDatas || empty($orderDatas)) {
            // 対応注文なし
            $result[self::RESULT_KEY_ORDER_ID] = new ServiceIdmapIdError(self::ID_ERROR_NOT_EXISTS);
        } else {
            $orderDatasCount = 0;
            if (! empty ( $orderDatas )) {
                $orderDatasCount = count ( $orderDatas );
            }
            if ($orderDatasCount != 1) {
                // 複数注文該当
                $result [self::RESULT_KEY_ORDER_ID] = new ServiceIdmapIdError ( self::ID_ERROR_ORDER_DUPLICATE );
            } else {
                // 正常
                $result [self::RESULT_KEY_ORDER_ID] = $orderDatas [0] ['OrderId'];
            }
        }
        $this->_results[] = $result;

        return $this;
    }

    /**
     * オブジェクトのシリアライズ
     *
     * @return string シリアライズされた文字列
     */
    public function serialize() {
        $errMap = self::__getErrorMap();

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

            // /response/results/result/entOrderId
            $entIdElement = $doc->createElement(self::RESULT_KEY_ENT_ORDER_ID);
            $entIdElement->appendChild($doc->createTextNode($result[self::RESULT_KEY_ENT_ORDER_ID]));
            $resultElement->appendChild($entIdElement);

            if(!($result[self::RESULT_KEY_ORDER_ID] instanceof ServiceIdmapIdError)) {
                // 変換成功時
                // /response/results/result/orderId
                $orderElement = $doc->createElement(self::RESULT_KEY_ORDER_ID);
                $orderElement->appendChild($doc->createTextNode($result[self::RESULT_KEY_ORDER_ID]));
                $resultElement->appendChild($orderElement);
            } else {
                // 変換失敗時
                // //response/results/result/error
                /** @var ServiceIdmapIdError */
                $errInfo = $result[self::RESULT_KEY_ORDER_ID];
                $errElement = $doc->createElement(self::RESULT_KEY_ID_ERROR);
                $errElement->setAttribute('cd', $errInfo->idErrorCode);
                $errText = $errMap[$errInfo->idErrorCode];  // 個別エラーメッセージ
                $errElement->appendChild($doc->createTextNode($errText));
                $resultElement->appendChild($errElement);
            }

            $resultsElement->appendChild($resultElement);
        }

        // 文字列として返却
        return $doc->saveXML();
    }
}