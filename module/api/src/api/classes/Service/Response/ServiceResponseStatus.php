<?php
namespace api\classes\Service\Response;

use api\Application;
use api\classes\Service\Response\ServiceResponseAbstract;
use api\classes\Service\Status\ServiceStatusConst;

/**
 * 与信状況問い合わせAPIのレスポンスを管理するクラス
 */
class ServiceResponseStatus extends ServiceResponseAbstract {
    /** レスポンスノード名定数：注文ID @var string */
    const RESULT_KEY_ORDER_ID = 'orderId';

    /** レスポンスノード名定数：任意注文番号 @var string */
    const RESULT_KEY_ENT_ORDER_ID = 'entOrderId';

    /** レスポンスノード名定数：注文状況 @var string */
    const RESULT_KEY_STATUS = 'orderStatus';

    /** 注文状況コード定数：与信中 @var int */
    const STATUS_NOW_PROCESSING = 0;

    /** 注文状況コード定数：与信OK @var int */
    const STATUS_INCRE_OK = 1;

    /** 注文状況コード定数：与信NG @var int */
    const STATUS_INCRE_NG = 2;

    /** 注文状況コード定数：キャンセル @var int */
    const STATUS_CANCELLED = 3;

    /** 注文状況コード定数：ID不正 @var int */
    const STATUS_INVALID = -1;

    /** 注文状況定数：与信中 @var string */
    const STS_MSG_NOW_PROCESSING = '与信中';

    /** 注文状況定数：与信OK @var string */
    const STS_MSG_INCRE_OK = '与信OK';

    /** 注文状況定数：与信NG @var string */
    const STS_MSG_INCRE_NG = '与信NG';

    /** 注文状況定数：キャンセル @var string */
    const STS_MSG_CANCELLED = 'キャンセル';

    /** 注文状況定数：ID不正 @var string */
    const STS_MSG_INVALID = 'ID不正';

    /**
     * 注文状況コードと注文状況テキストのマップを取得する
     *
     * @static
     * @access protected
     * @return array キーに注文状況コード、値に対応する注文状況テキストを格納した連想配列
     */
    protected static function __getStatusMap() {
        return array(
            // 与信中
            self::STATUS_NOW_PROCESSING => self::STS_MSG_NOW_PROCESSING,

            // 与信OK
            self::STATUS_INCRE_OK => self::STS_MSG_INCRE_OK,

            // 与信NG
            self::STATUS_INCRE_NG => self::STS_MSG_INCRE_NG,

            // キャンセル
            self::STATUS_CANCELLED => self::STS_MSG_CANCELLED,

            // ID不正
            self::STATUS_INVALID => self::STS_MSG_INVALID
        );
    }

    /**
     * 注文状況コードと注文状況テキストのマップを取得する
     *
     * @static
     * @access public
     * @return array キーに注文状況コード、値に対応する注文状況テキストを格納した連想配列
     */
    public static function getStatusMap() {
        return self::__getStatusMap();
    }

    /**
     * 結果項目リスト
     *
     * @access protected
     * @var array
     */
    protected $_results = array();

    /**
     * 指定注文IDに対応するT_Orderデータから問い合わせ結果項目を追加する
     *
     * @param string $orderId 注文ID
     * @param array | null $orderData T_Orderデータイメージの連想配列
     * @return ServiceResponseStatus このインスタンス
     */
    public function addResult($orderId, $orderData) {
        $status = $this->judgeStatus($orderData);

        $result = array(
            self::RESULT_KEY_ORDER_ID => $orderId,
            self::RESULT_KEY_STATUS => $status
        );

        // 任意注文番号が登録されている場合はレスポンスに含める
        $entOrderId = isset($orderData['Ent_OrderId']) ? trim($orderData['Ent_OrderId']) : null;
        if($entOrderId && strlen($entOrderId)) {
            $result[self::RESULT_KEY_ENT_ORDER_ID] = $entOrderId;
        }
        $this->_results[] = $result;

        return $this;
    }

    /**
     * 注文データから与信状況を判断する
     *
     * @access protected
     * @param array | null $orderData T_Orderデータイメージの連想配列
     * @return int 与信状況コード
     */
    protected function judgeStatus($orderData) {
        // 注文データがない場合はID不正
        if($orderData === null) {
            return self::STATUS_INVALID;
        }

        // キャンセル依頼中またはキャンセル済み
        if($orderData['Cnl_Status']) {
            return self::STATUS_CANCELLED;
        }

        $ds = (int)$orderData['DataStatus'];
        // DataStatusが存在しない場合はID不正
        if(!$ds) return self::STATUS_INVALID;

        // 与信中
        if($ds < 31) return self::STATUS_NOW_PROCESSING;

        // 与信NG
        if($orderData['DataStatus'] == 91 && $orderData['CloseReason'] == 3) {
            return self::STATUS_INCRE_NG;
        }

        // ここまでたどり着いたら与信OK
        return self::STATUS_INCRE_OK;
    }

    /**
     * オブジェクトのシリアライズ
     *
     * @return string シリアライズされた文字列
     */
    public function serialize() {
        $stsMap = self::__getStatusMap();

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

            // /response/results/reslut/orderStatus
            $statusElement = $doc->createElement(self::RESULT_KEY_STATUS);
            $statusElement->setAttribute('cd', $result[self::RESULT_KEY_STATUS]);
            $statusText = $stsMap[$result[self::RESULT_KEY_STATUS]];    // 与信状況テキスト
            $statusElement->appendChild($doc->createTextNode($statusText));

            $resultElement->appendChild($statusElement);

            $resultsElement->appendChild($resultElement);
        }

        // 文字列として返却
        return $doc->saveXML();
    }
}