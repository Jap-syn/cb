<?php
namespace models\Logic\CreditJudge\Connector;

use Zend\Http\Client;
use Zend\Json\Json;
use models\Logic\CreditJudge\SystemConnect\LogicCreditJudgeSystemConnectException;

/**
 * ジンテックと連携用
 */
class LogicCreditJudgeConnectorJintec
		extends LogicCreditJudgeConnectorAbstract {

    /**
     * オプションを指定してLogicCreditJudgeConnector_Jintecの
     * 新しいインスタンスを初期化する
     *
     * @param LogicCreditJudgeOptions | array | Zend\Config\Reader\Ini $options オプション
     */
    public function __construct($options) {
        parent::__construct($options);
    }

    /**
     * ジンテックにアクセスしデータを取得する
     *
     * @access protected
     * @return string レスポンスデータ
     */
    protected function _connect($params) {

        //ジンテックURL取得
    	$jintec_url = $this->_options->getJintecUrl();

		//ジンテックCID取得
		$jintec_cid = $this->_options->getJintecCid();

		//ジンテックID取得
		$jintec_id = $this->_options->getJintecId();

		//ジンテックパス取得
		$jintec_pass = $this->_options->getJintecPassword();

        //オプション設定
        $option = array(
                'adapter'=> 'Zend\Http\Client\Adapter\Curl', // SSL通信用に差し替え
                'ssltransport' => 'tls',
                'maxredirects' => 1,        // 20150717 試行回数(maxredirects) を 1 に設定
        );
        $client = new Client($jintec_url, $option);

        $client->getUri()->setQuery($params);

        try {
            // データ送信を実行する
            $response = $client->send();

            // 結果を取得する
            $status = $response->getStatusCode();

            if($status == 200) {
                // HTTPステータスが200だったら受信内容をそのまま返す
                return $response->getBody();
            }
            // ステータスが200以外の場合はコード1で例外をスロー
            throw new LogicCreditJudgeSystemConnectException('Jintec Request failed', 1);
        } catch(\Exception $err) {
            if(!$err->getCode()) {
                // コード指定がない場合はタイムアウトと見なす
                throw new LogicCreditJudgeSystemConnectException('Request Timed out');
            } else {
                // それ以外はそのままキャッチした例外をスロー
                throw $err;
            }
        }


    }

    /**
     * ジンテックのダミーレスポンスデータを返す。
     *
     * @access protected
     * @return string ダミーレスポンスデータ
     */
    protected function _connectDummy($params) {
        return $this->loadLocalJintecData();
    }

    /**
     * 送信データを接続先固有のフォーマットにエンコードする。
     * このメソッドは抽象メソッドなので、派生クラスで固有実装をする必要がある。
     *
     * @abstract
     * @access protected
     * @param array $params 連想配列形式の送信データ
     * @return string クラス固有の接続先が要求するフォーマットにエンコードされた送信用データ
     */
    protected function _encodeParams(array $params) {
        // 認証情報とマージ
        $params = array(
						'tel' => $params['phone'],
						'cid' => $this->getOptions()->getJintecCid(),
						'id' => $this->getOptions()->getJintecId(),
						'pass' => $this->getOptions()->getJintecPassword() );
        // クエリストリングとしてエンコード
        return http_build_query($params);
    }

    /**
     * 接続先から受信した生のレスポンスデータを連想配列にデコードする。
     * このメソッドは抽象メソッドなので、派生クラスで固有実装をする必要がある。
     *
     * @abstract
     * @access protected
     * @param string $response クラス固有の接続先から受信したレスポンスデータ
     * @return array 接続先から受信したデータを展開した連想配列
     */
    protected function _decodeResponse($response) {
        // SimpleXMLを経由して連想配列に展開
        $xml = simplexml_load_string($response);
        return Json::decode(Json::encode($xml), true);
    }

}
