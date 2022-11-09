<?php
namespace models\Logic\CreditJudge\Connector;

use Zend\Http\Client;
use Zend\Json\Json;
use models\Logic\CreditJudge\SystemConnect\LogicCreditJudgeSystemConnectException;
use Coral\Base\IO\BaseIOUtility;

/**
 * ILU審査システムのパターンマスターを取得するためのコネクタクラス
 */
class LogicCreditJudgeConnectorIluPatternMaster
                        extends LogicCreditJudgeConnectorAbstract {

    /**
     * オプションを指定してLogicCreditJudgeConnectorIluPatternMasterの
     * 新しいインスタンスを初期化する
     *
     * @param LogicCreditJudgeOptions | array | Zend\Config\Reader\Ini $options オプション
     */
    public function __construct($options) {
        parent::__construct($options);
    }

    /**
     * ILU審査システムのパターンマスター取得サービスに接続し、レスポンスデータを返す。
     *
     * @access protected
     * @param string $params パターンマスター取得サービス向け送信データ
     * @return string レスポンスデータ
     */
    protected function _connect($params) {
        $client = new Client($this->_options->getIluPatternMasterUrl());

        try {
            // データ送信を実行する
            $response = $client
                            ->setParameterGet(array('system_id' => $this->_options->getIluId()))
                            ->setRawBody($params)
                            ->setEncType('application/xml; charset=UTF-8', ';')
                            ->setMethod('Get')
                            ->send();

            // 結果を取得する
            $status = $response->getStatusCode();
            $res_msg = $response->getReasonPhrase();
            $res_msg = mb_convert_encoding($res_msg, mb_internal_encoding(), BaseIOUtility::detectEncoding($res_msg));

            if($status == 200) {
                // HTTPステータスが200だったら受信内容をそのまま返す
                return $response->getBody();
            }
            // ステータスが200以外の場合はコード1で例外をスロー
            throw new LogicCreditJudgeSystemConnectException(sprintf('Pattern Master Request failed (%s : %s)', $status, $res_msg), 1);
        } catch(\Exception $err) {
            if(!$err->getCode()) {
                // コード指定がない場合はタイムアウトと見なす
                throw new LogicCreditJudgeSystemConnectException(sprintf('Pattern Master Request Timed out (%s)', $err->getMessage()));
            } else {
                // それ以外はそのままキャッチした例外をスロー
                throw $err;
            }
        }
    }

    /**
     * ILU審査システムパターンマスター取得サービスのダミーレスポンスデータを返す。
     *
     * @access protected
     * @param string $params パターンマスター取得サービス向け送信データ
     * @return string ダミーレスポンスデータ
     */
    protected function _connectDummy($params) {
        return $this->loadLocalIluPatternMasterData();
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
        // このクラスの場合はパラメータをすべて無視する
        return '';
    }

    /**
     * 接続先から受信した生のレスポンスデータを連想配列にデコードする。
     * このメソッドは抽象メソッドなので、派生クラスで固有実装をする必要がある。
     *
     * @abstract
     * @access protected
     * @param string $response クラス固有の接続先から受信したレスポンスデータ
     * @return array レスポンスデータをデコードした連想配列
     */
    protected function _decodeResponse($response) {
        // SimpleXMLを経由して連想配列に展開
        $xml = simplexml_load_string($response);
        $params = Json::decode(Json::encode($xml), true);

		//patternデータ初期化
		$result = array();

		//NOをキーに連想配列作成(システムパターン)
		foreach($params['inspect_setting']['system_patterns']['system_pattern_detail'] as $value){
		    // 1行の場合、$valueが配列にならず要素が入ってくるので親を使用して終了
		    if (!is_array($value)) {
		        $result[$params['inspect_setting']['system_patterns']['system_pattern_detail']['no']] = $params['inspect_setting']['system_patterns']['system_pattern_detail'];
		        break;
		    }
			$result[$value['no']] = $value;
		}

		//Noをキーに連想配列作成(ユーザパターン)
		foreach($params['inspect_setting']['user_patterns']['user_pattern_detail'] as $value){
		    // 1行の場合、$valueが配列にならず要素が入ってくるので親を使用して終了
		    if (!is_array($value)) {
		        $result[$params['inspect_setting']['user_patterns']['user_pattern_detail']['no']] = $params['inspect_setting']['user_patterns']['user_pattern_detail'];
		        break;
		    }
			$result[$value['no']] = $value;
		}
		return $result;
    }

}
