<?php

namespace models\Logic;

use Coral\Base\BaseLog;
use Coral\Base\IO\BaseIOUtility;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Json\Json;
use Zend\Http\Client;
use Zend\Log\Logger;
use models\Table\TableSystemProperty;
use models\Table\TableSystemStatus;
use models\Logic\CreditJudge\LogicCreditJudgeAbstract;

/**
 * 与信審査システムサービス状態取得ロジック
 */
class LogicIluGetServiceStatus {
    /**
     * アダプタ
     *
     * @var Adapter
     */
    protected $_adapter = null;

    /**
     * アプリケーション設定
     *
     * @access protected
     * @var array (Zend\Config\Reader\Ini)
     */
    protected $_config;

    /**
     * ユーザID
     *
     * @var int
     */
    protected $_userId = null;

    /**
     * タイムアウト時間
     *
     * @var int
     */
    protected $_timeouttime = null;

    /**
     * 審査システム－システムID
     *
     * @var int
     */
    protected $_ilu_id = null;

    /**
     * バイパス設定
     *
     * @var bool
     */
    protected $_bypass_ilu = null;

    /**
     * ロガーインスタンス
     *
     * @access protected
     * @var BaseLog
     */
    protected $_logger;

    /**
     * このインスタンスで使用するロガーを取得する
     *
     * @return BaseLog
     */
    public function getLogger() {
        return $this->_logger;
    }
    /**
     * このインスタンスで使用するロガーを設定する
     *
     * @param BaseLog $logger
     * @return LogicCreditJudgePrejudgeThread
     */
    public function setLogger(BaseLog $logger = null) {
        $this->_logger = $logger;
        return $this;
    }

    /**
     * コンストラクタ
     *
     * @param Adapter $adapter アダプタ
     * @param array (Zend\Config\Reader\Ini) ロードされているアプリケーション設定
     * @param int $userId ユーザーID
     */
    public function __construct(Adapter $adapter, $config, $userId = -1) {
        $this->_adapter = $adapter;
        $this->_config = $config;
        $this->_userId = $userId;

        // ログ設定
        $this->setLogger(LogicCreditJudgeAbstract::getDefaultLogger());
    }

    /**
     * 与信審査システムからサービス状態を取得する
     * @throws Exception
     */
    public function getServiceStatus() {
        // API連携のタイムアウト時間
        $this->_timeouttime = $this->_config['cj_api']['timeout_time'];

        // 審査システム連携用のシステムID
        $this->_ilu_id = $this->_config['cj_api']['ilu_id'];

        // 審査システムバイパス設定
        $this->_bypass_ilu = $this->toBeBypassIlu();

        // サービス状態取得API連携
        $this->getServiceStatusConnect();
    }

    /**
     * ILU審査システム連携の回避時間を取得する
     *
     * @return boolean
     */
    private function isBypassTime() {
        $starttime = mktime(3,55,0);
        $endtime = mktime(4,15,0);
        $now = mktime();
        return $starttime <= $now && $now <= $endtime ? true : false;
    }

    /**
     * ILU審査システム連携をバイパスすべきかを判断する
     *
     * @return boolean
     */
    private function toBeBypassIlu() {
        if($this->isBypassTime()) return true;
        return isset($this->_config['cj_api']['bypass']) &&
                isset($this->_config['cj_api']['bypass']['ilu']) &&
                $this->_config['cj_api']['bypass']['ilu'] == 'true';
    }

    /**
     * API連携実行
     *
     * @param string $url API連携URL
     * @param string $params POSTするリクエストパラメータ
     * @throws LogicCreditJudgeSystemConnectException
     * @throws Exception
     */
    private function apiConnect($url, $params) {
        $client = new Client($url);

        //タイムアウト時刻取得
        $time_out = $this->_timeouttime;

        //タイムアウト設定
        $client->setOptions(array('timeout' => $time_out, 'keepalive' => true, 'maxredirects' => 1));  // 試行回数(maxredirects) を 1 に設定

        try {
            // データ送信を実行する
            $response = $client
                ->setParameterGet($params)
                ->setRawBody(array())
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
            throw new \Exception(sprintf('%s Request failed (%s : %s)', $url, $status, $res_msg), 1);
        }
        catch(\Exception $err) {
            if(!$err->getCode()) {
                // コード指定がない場合はタイムアウトと見なす
                throw new \Exception(sprintf('%s Request Timed out (%s)', $url, $err->getMessage()));
            }
            else {
                // それ以外はそのままキャッチした例外をスロー
                throw $err;
            }
        }
    }

    /**
     * サービス状態取得API連携
     */
    private function getServiceStatusConnect() {
        // サービス状態取得URL
        $getServiceStatusUrl = $this->_config['cj_api']['url']['ilu_pattern_master'];

        // サービス状態取得API連携

        // リクエストパラメータ設定
        $reqParams = array('system_id' => $this->_ilu_id);

        // WebAPI連携実行
        try {
            $response = $this->apiConnect($getServiceStatusUrl, $reqParams);
        }
        catch (\Exception $ex) {
            // 応答なしの場合
            $response = null;
            $apiException = $ex->getMessage();
        }

        if (!is_null($response)) {
            // 応答があった場合

            // レスポンス解析
            $resData = $this->getResponseInfoStatus($response);
        }
        else {
            // 応答なしの場合
            $resData = array();
            $resData['status'] = 'NG';
            $resData['last_error'] = $apiException;
        }

        // DBに結果反映
        $sysSt = new TableSystemStatus($this->_adapter);
        if ($resData['status'] == 'OK') {
            // 連携結果：OK
            $this->debug(sprintf('getServiceStatusConnect response OK'));

            // システムステータス更新
            $data = Json::encode($resData['pattern']);
            $sysSt->setCjServiceStatus($data);
        }
        else {
            // 連携結果：NG
            $this->debug(sprintf('getServiceStatusConnect response NG'));
            $this->debug(sprintf('getServiceStatusConnect NG message : %s', $resData['last_error']));

            // システムステータス更新
            $sysSt->setCjServiceStatus('');
        }
    }

    /**
     * サービス状態取得APIのレスポンス解析
     *
     * @param string $response レスポンスデータ（XML文字列）
     * @return array レスポンス解析結果
     */
    private function getResponseInfoStatus($response) {
        // SimpleXMLを経由して連想配列に展開
        $xml_param = simplexml_load_string($response);
        $resp_data = Json::decode(Json::encode($xml_param), Json::TYPE_ARRAY);

        // ステータス
        $status = '';
        if (!is_null($resp_data['status'])) {
            $status = $resp_data['status'];
        }

        // エラーメッセージ
        $last_error = '';
        if (!is_null($resp_data['last_error'])) {
            $last_error = $resp_data['last_error'];
        }

        $pattern = array();

        // inspect_setting検査
        if (isset($resp_data['inspect_setting']['file_title'])) {
            // inspect_settingが1つの場合
            $inspect_setting_array = array();
            $inspect_setting_array[] = $resp_data['inspect_setting'];
        }
        else {
            // inspect_settingが複数の場合
            $inspect_setting_array =$resp_data['inspect_setting'];
        }
        foreach ($inspect_setting_array as $inspect_setting) {
            // system_pattern_detail検査
            if (isset($inspect_setting['system_patterns']['system_pattern_detail']['no'])) {
                // system_pattern_detailが1つの場合
                $system_pattern_detail_array = array();
                $system_pattern_detail_array[] = $inspect_setting['system_patterns']['system_pattern_detail'];
            }
            else {
                $system_pattern_detail_array = $inspect_setting['system_patterns']['system_pattern_detail'];
            }
            //NOをキーに連想配列作成(システムパターン)
            if (is_array($system_pattern_detail_array)) {
                foreach($system_pattern_detail_array as $value){
                    $pattern[$value['no']] = $value;
                }
            }


            // system_pattern_detail検査
            if (isset($inspect_setting['user_patterns']['user_pattern_detail']['no'])) {
                // system_pattern_detailが1つの場合
                $user_pattern_detail_array = array();
                $user_pattern_detail_array[] = $inspect_setting['user_patterns']['user_pattern_detail'];
            }
            else {
                $user_pattern_detail_array = $inspect_setting['user_patterns']['user_pattern_detail'];
            }
            //Noをキーに連想配列作成(ユーザパターン)
            if (is_array($user_pattern_detail_array)) {
                foreach($user_pattern_detail_array as $value){
                    $pattern[$value['no']] = $value;
                }
            }
        }
        var_dump($pattern);
        // 解析結果をまとめる
        $respResult = array();
        $respResult['status'] = $status;
        $respResult['last_error'] = $last_error;
        $respResult['pattern'] = $pattern;

        return $respResult;
    }

    /**
     * 指定の優先度でログメッセージを出力する
     *
     * @param string $message ログメッセージ
     * @param int $priority 優先度
     */
    public function log($priority, $message) {
        $logger = $this->getLogger();
        $message = sprintf('[%s] %s', get_class($this), $message);
        if($logger) {
            $logger->log($priority, $message);
        }
    }

    /**
     * DEBUGレベルでログメッセージを出力する
     *
     * @param string $message ログメッセージ
     */
    public function debug($message) {
        $this->log(Logger::DEBUG, $message);
    }

    /**
     * INFOレベルでログメッセージを出力する
     *
     * @param string $message ログメッセージ
     */
    public function info($message) {
        $this->log(Logger::INFO, $message);
    }

    /**
     * NOTICEレベルでログメッセージを出力する
     *
     * @param string $message ログメッセージ
     */
    public function notice($message) {
        $this->log(Logger::NOTICE, $message);
    }

    /**
     * WARNレベルでログメッセージを出力する
     *
     * @param string $message ログメッセージ
     */
    public function warn($message) {
        $this->log(Logger::WARN, $message);
    }

    /**
     * ERRレベルでログメッセージを出力する
     *
     * @param string $message ログメッセージ
     */
    public function err($message) {
        $this->log(Logger::ERR, $message);
    }

    /**
     * CRITレベルでログメッセージを出力する
     *
     * @param string $message ログメッセージ
     */
    public function crit($message) {
        $this->log(Logger::CRIT, $message);
    }

    /**
     * ALERTレベルでログメッセージを出力する
     *
     * @param string $message ログメッセージ
     */
    public function alert($message) {
        $this->log(Logger::ALERT, $message);
    }

    /**
     * EMERGレベルでログメッセージを出力する
     *
     * @param string $message ログメッセージ
     */
    public function emerg($message) {
        $this->log(Logger::EMERG, $message);
    }
}
