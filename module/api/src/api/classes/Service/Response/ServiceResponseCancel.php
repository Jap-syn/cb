<?php
namespace api\classes\Service\Response;

use api\classes\Service\Response\ServiceResponseAbstract;
use api\classes\Service\Cancel\ServiceCancelRequestError;

/**
 * 注文キャンセルAPIのレスポンスを管理するクラス
 */
class ServiceResponseCancel extends ServiceResponseAbstract {
	/** レスポンスノード名定数：注文ID @var string */
	const RESULT_KEY_ORDER_ID = 'orderId';

    /** レスポンスノード名定数：キャンセル理由 @var string */
    const RESULT_KEY_CANCEL_REASON = 'reason';

    /** レスポンスノード名定数：キャンセル依頼個別結果 @var string */
    const RESULT_KEY_CANCEL_STATUS = 'cancelStatus';

	/** レスポンスノード名定数：個別エラー @var string */
	const RESULT_KEY_ID_ERROR = 'error';

	/** 個別エラーコード定数：対応注文なし @var string */
	const CANCEL_ERROR_NOT_EXISTS = 'E001';

	/** 個別エラーコード定数：キャンセル不可状態 @var string */
	const CANCEL_ERROR_CANNOT_CANCEL = 'E002';

	/** 個別エラーコード定数：その他のエラー @var string */
	const CANCEL_ERROR_OTHERS = 'E999';

    /** 個別エラー定数：対応注文なし @var string */
    const CANCEL_ERR_MSG_NOT_EXISTS = '対応注文なし';

	/** 個別エラー定数：キャンセル不可状態 @var string */
    const CANCEL_ERR_MSG_CANNOT_CANCEL = 'キャンセルできない注文';

    /** 個別エラー定数：その他のエラー @var string */
    const CANCEL_ERR_MSG_OTHERS = 'その他のエラー';

    const CANCEL_ERROR_SBPS_CANCEL = 'E003';

	/**
	 * 個別エラーコードと個別エラーテキストのマップを取得する
	 *
	 * @static
	 * @access protected
	 * @return array キーに申請結果コード、値に対応する申請結果テキストを格納した連想配列
	 */
	protected static function __getErrorMap() {
		return array(
            // 対応注文なし
            self::CANCEL_ERROR_NOT_EXISTS       => self::CANCEL_ERR_MSG_NOT_EXISTS,

            // 複数注文該当
            self::CANCEL_ERROR_CANNOT_CANCEL    => self::CANCEL_ERR_MSG_CANNOT_CANCEL,

            // その他のエラー
            self::CANCEL_ERROR_OTHERS           => self::CANCEL_ERR_MSG_OTHERS,
        );
	}

	/**
	 * 結果項目リスト
	 *
	 * @access protected
	 * @var array
	 */
	protected $_results = array();

	protected $errCode = null;

    protected $errMes = null;

    /**
     * キャンセル申請結果項目を追加する
     *
     * @param string $orderId 注文ID
     * @param string | null $cancelReason キャンセル理由
     * @param array $orderDatas T_Orderデータイメージの連想配列の配列
     * @param bool $hasError エラーが発生したかのフラグ。省略時はfalse
     * @param bool $isSbps
     * @param null $errCode
     * @param null $msg
     * @return ServiceResponseCancel このインスタンス
     */
	public function addResult($orderId, $cancelReason, $orderDatas, $hasError = false, $isSbps = false, $errCode = null, $msg = null) {
        if($cancelReason === null) $cancelReason = '';
        $cancelReason = trim($cancelReason);

        $result = array(
            self::RESULT_KEY_ORDER_ID       => $orderId,
            self::RESULT_KEY_CANCEL_REASON  => nvl($cancelReason),
            self::RESULT_KEY_CANCEL_STATUS  => ServiceResponseAbstract::ERROR
        );

        // 注文データの有無を先に判断
        $orderDatasCount = 0;
        if (!empty($orderDatas)) {
            $orderDatasCount = count($orderDatas);
        }
        $hasData = $orderDatas && $orderDatasCount == 1;

        if($hasError) {
            // 注文データがある場合はキャンセル不可、ない場合はその他エラー

            if ($isSbps == true) {
                $reason = $hasData ?
                    self::CANCEL_ERROR_SBPS_CANCEL :
                    self::CANCEL_ERROR_OTHERS;
                $this->errCode = $errCode;
                $this->errMes = $msg;
            } else {
                $reason = $hasData ?
                    self::CANCEL_ERROR_CANNOT_CANCEL :
                    self::CANCEL_ERROR_OTHERS;
            }
            $result[self::RESULT_KEY_CANCEL_STATUS] = new ServiceCancelRequestError($reason);
        } else
        if(!$orderDatas || empty($orderDatas)) {
            // 対応注文なし
            $result[self::RESULT_KEY_CANCEL_STATUS] =
                new ServiceCancelRequestError(self::CANCEL_ERROR_NOT_EXISTS);
        } else {
            // 正常
            $result[self::RESULT_KEY_CANCEL_STATUS] = ServiceResponseAbstract::SUCCESS;
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

		/** @var DOMElement /response */
		$rootElement = $doc->appendChild( $doc->createElement("response") );

		/** @var DOMElement /response/status */
		$statusElement = $rootElement->appendChild( $doc->createElement("status") );
		$statusElement->appendChild( $doc->createTextNode($this->status) );

		/** @var DOMElement /response/messages */
		$messagesElement = $rootElement->appendChild( $doc->createElement("messages") );
		foreach ( $this->messages as $message ) {
			/** @var DOMElement /response/messages/message */
			$messageElement = $doc->createElement("message");
			$messageElement->setAttribute( "cd", $message->messageCd );
			$messageElement->appendChild( $doc->createTextNode( $message->messageText ) );

			$messagesElement->appendChild( $messageElement );
		}

		/** @var DOMElement /response/results */
		$resultsElement = $rootElement->appendChild($doc->createElement('results'));
		foreach($this->_results as $result) {
			/** @var DOMElement /response/results/result */
			$resultElement = $doc->createElement('result');

            /** @var DOMElement /response/results/result/orderId */
            $orderIdElement = $doc->createElement(self::RESULT_KEY_ORDER_ID);
            $orderIdElement->appendChild($doc->createTextNode($result[self::RESULT_KEY_ORDER_ID]));
            $resultElement->appendChild($orderIdElement);

            /** @var DOMElement /response/results/result/reason */
            $reasonElement = $doc->createElement(self::RESULT_KEY_CANCEL_REASON);
            $reasonElement->appendChild($doc->createTextNode($result[self::RESULT_KEY_CANCEL_REASON]));
            $resultElement->appendChild($reasonElement);

            /** @var DOMElement /response/results/result/cancelStatus */
            $cnlStatusElement = $doc->createElement(self::RESULT_KEY_CANCEL_STATUS);
            if(!($result[self::RESULT_KEY_CANCEL_STATUS] instanceof ServiceCancelRequestError)) {
                // 申請受理時
                $cnlStatusElement->appendChild($doc->createTextNode($result[self::RESULT_KEY_CANCEL_STATUS]));
                $resultElement->appendChild($cnlStatusElement);
            } else {
                // 申請拒否時
                $cnlStatusElement->appendChild($doc->createTextNode(ServiceResponseAbstract::ERROR));
                $resultElement->appendChild($cnlStatusElement);

                /** @var Service_Cancel_RequestError */
                $errInfo = $result[self::RESULT_KEY_CANCEL_STATUS];

                /** @var DOMElement /response/results/result/error */
                $errElement = $doc->createElement(self::RESULT_KEY_ID_ERROR);
                $errElement->setAttribute('cd', $errInfo->reqErrorCode);
                if ($errInfo->reqErrorCode == self::CANCEL_ERROR_SBPS_CANCEL) {
                    $errText = "SBPS側でエラーが発生しました。". $this->errMes ."(". $this->errCode .") ";
                } else {
                    $errText = $errMap[$errInfo->reqErrorCode];  // 個別エラーメッセージ
                }
                $errElement->appendChild($doc->createTextNode($errText));
                $resultElement->appendChild($errElement);
            }

			$resultsElement->appendChild($resultElement);
		}

		// 文字列として返却
		return $doc->saveXML();
	}

}
