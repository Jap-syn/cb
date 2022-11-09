<?php

namespace models\Logic;

use Coral\Base\IO\BaseIOUtility;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Json\Json;
use Zend\Http\Client;
use models\Table\TableJudgeSystemResponse;
use models\Table\TableManagementCustomer;
use models\Table\TableSystemProperty;

/**
 * 与信審査システム顧客情報反映ロジック
 */
class LogicIluCustomerListImport {

    /**
     * アダプタ
     *
     * @var Adapter
     */
    protected $_adapter = null;

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
     * コンストラクタ
     *
     * @param Adapter $adapter アダプタ
     * @param int $userId ユーザーID
     */
    public function __construct(Adapter $adapter, $userId = -1) {
        $this->_adapter = $adapter;
        $this->_userId = $userId;
    }

    /**
     * 与信審査システムに顧客情報を連携する
     * @throws Exception
     */
    public function customerlistimport() {
        $mdlsp = new TableSystemProperty($this->_adapter);          // システムプロパティ

        // API連携のタイムアウト時間
        $this->_timeouttime = $mdlsp->getValue ('cbadmin', 'cj_api', 'timeout_time');

        // 審査システム連携用のシステムID
        $this->_ilu_id = $mdlsp->getValue ('cbadmin', 'cj_api', 'ilu_id');

        // 審査システムバイパス設定
        $this->_bypass_ilu = $this->toBeBypassIlu($mdlsp->getValue ('cbadmin', 'cj_api', 'bypass.ilu'));


        // 顧客情報編集結果取得API連携
        $this->getCustomerListImportResultConnect();

        // 顧客情報編集API連携
        $this->customerListImportConnect();
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
     * @param $bypass_ilu システムプロパティのバイパス設定
     * @return boolean
     */
    private function toBeBypassIlu($bypass_ilu) {
     //   if($this->isBypassTime()) return true;
        return !is_null($bypass_ilu) && $bypass_ilu == 'true';
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
                ->setRawBody($params)
                ->setEncType('application/xml; charset=UTF-8', ';')
                ->setMethod('Post')
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
     * 顧客情報編集結果取得API連携
     */
    private function getCustomerListImportResultConnect() {
        // バイパス設定がある場合、処理なし
        if ($this->_bypass_ilu) {
            return;
        }

        $mdlsp = new TableSystemProperty($this->_adapter);          // システムプロパティ
        $mdljsr = new TableJudgeSystemResponse($this->_adapter);    // 審査システム応答
        $mdlmc = new TableManagementCustomer($this->_adapter);      // 管理顧客

        // 顧客情報編集結果取得API連携URL取得
        $getCustomerListImportResultUrl = $mdlsp->getValue ('cbadmin', 'cj_api', 'GetCustomerListImportResult');

        // 与信審査結果の顧客情報編集結果を未確認のデータを取得
        $confirmDatas = ResultInterfaceToArray($mdljsr->getUnconfirmedData());
        foreach ($confirmDatas as $confirmData) {
            // 顧客情報編集結果取得API連携

            // リクエストパラメータ作成
            $reqParams = $this->createRequestParamsResult($confirmData);


            // リクエスト to JSON
	    $xml_param = simplexml_load_string($reqParams);
            $req_data = Json::encode($xml_param);

            // 審査システム応答登録
            $jsrKey = $mdljsr->saveNew(array(
                    'SendDate' => date('Y-m-d H:i:s'),  // 送信日時
                    'ReceiveDate' => null,              // 受信日時
                    'Status' => 0,                      // ステータス　0:未送信
                    'AcceptNumber' => null,             // 受付番号
                    'ConfirmStatus' => 0,               // 確認済みステータス　0:不要
                    'JudgeClass' => 3,                  // 連携種類　3:顧客情報（２）
                    'SentRawData' => $req_data,         // 送信データ
                    'ReceivedRawData' => null,          // 受信データ
                    'Reserve' => null,                  // 予約項目
                    'RegistId' => $this->_userId,       // 登録者
                    'UpdateId' => $this->_userId,       // 更新者
                    'ValidFlg' => 1,                    // 有効フラグ
                )
            );

            // WebAPI連携実行
            $response = $this->apiConnect($getCustomerListImportResultUrl, $reqParams);

            // 審査システム応答（今回分）　送信済みに更新
            $mdljsr->saveUpdate(array(
                    'Status' => 1,                      // ステータス　1:送信済み
                    'UpdateId' => $this->_userId,       // 更新者
                )
                , $jsrKey
            );

	// 20160203-sode-temp XML出力
//	echo $response;



            // レスポンス to JSON
            $xml_param = simplexml_load_string($response);
            $resp_data = Json::encode($xml_param);

            // レスポンス解析
            $resData = $this->getResponseInfoResult($response);

            // DBに結果反映
            try {
                // トランザクション開始
                $this->_adapter->getDriver()->getConnection()->beginTransaction();

// 20160211 sode エラーでもOK エラーコード!=0で返ってくる場合があるため、訂正
		  if ($resData['result'] == 'OK' && $resData['error_code'] == 0) {
//                if ($resData['result'] == 'OK') {
                    // 連携結果：OK

                    // 審査システム応答（確認対象）　確認済みに更新
                    $mdljsr->saveUpdate(array(
                            'ConfirmStatus' => 2,           // 確認済みステータス　2:確認済み
                            'UpdateId' => $this->_userId,   // 更新者
                        )
                        , $confirmData['Seq']
                    );

                    // 審査システム応答（今回分）　結果を反映
                    $mdljsr->saveUpdate(array(
                            'ReceiveDate' => date('Y-m-d H:i:s'),   // 受信日時
                            'Status' => 9,                          // ステータス　9:受信済み
                            'ReceivedRawData' => $resp_data,        // 受信データ
                            'ConfirmStatus' => 2,                   // 確認済みステータス　2:確認済み
                            'UpdateId' => $this->_userId,           // 更新者
                        )
                        , $jsrKey
                    );

                    // 管理顧客の更新
                    // 成功顧客
                    foreach ($resData['success_customers'] as $info) {
                        $mdlmc->saveUpdate(array(
                                'IluCustomerId' => $info['customer_id'],    // 審査システム－顧客ＩＤ
                                'UpdateId' => $this->_userId,               // 更新者
                            )
                            , $info['system_customer_id']
                        );
                    }

                    // 失敗顧客
                    foreach ($resData['faild_customers'] as $info) {
                        $mdlmc->saveUpdate(array(
                                'IluCustomerListFlg' => 1,                  // 審査システム－顧客情報連携フラグ　1:必要
                                'UpdateId' => $this->_userId,               // 更新者
                            )
                            , $info['system_customer_id']
                        );
                    }
                }
// 20160211 sode エラーでもOK エラーコード!=0で返ってくる場合があるため、訂正
//                elseif ($resData['result'] == 'NG' && $resData['error_code'] == 20) {
		  elseif ($resData['error_code'] == 20) {
                    // 審査システム側が処理中

                    // 審査システム応答（今回分）　結果を反映
                    $mdljsr->saveUpdate(array(
                            'ReceiveDate' => date('Y-m-d H:i:s'),   // 受信日時
                            'Status' => 9,                          // ステータス　9:受信済み
                            'ReceivedRawData' => $resp_data,        // 受信データ
                            'ConfirmStatus' => 2,                   // 確認済みステータス　2:確認済み
                            'UpdateId' => $this->_userId,           // 更新者
                        )
                        , $jsrKey
                    );
                }
                else {
                    // 連携結果：NG

                    // 審査システム応答　確認済みに更新
                    $mdljsr->saveUpdate(array(
                            'ConfirmStatus' => 2,           // 確認済みステータス　2:確認済み,
                            'UpdateId' => $this->_userId,   // 更新者
                        )
                        , $confirmData['Seq']
                    );

                    // 審査システム応答（今回分）　結果を反映
                    $mdljsr->saveUpdate(array(
                            'ReceiveDate' => date('Y-m-d H:i:s'),   // 受信日時
                            'Status' => 9,                          // ステータス　9:受信済み
                            'ReceivedRawData' => $resp_data,        // 受信データ
                            'ConfirmStatus' => 3,                   // 確認済みステータス　3:エラー
                            'UpdateId' => $this->_userId,           // 更新者
                        )
                        , $jsrKey
                    );

                    // 連携した顧客は全て失敗顧客扱い
                    $custList = Json::decode($confirmData['Reserve'], Json::TYPE_ARRAY);
                    foreach ($custList as $cust) {
                        $mdlmc->saveUpdate(array(
                                'IluCustomerListFlg' => 1,          // 審査システム－顧客情報連携フラグ　1:必要
                                'UpdateId' => $this->_userId,       // 更新者
                            )
                            , $cust
                        );
                    }
                }

                // コミット
                $this->_adapter->getDriver()->getConnection()->commit();
            }
            catch (\Exception $ex) {
                // ロールバック
                $this->_adapter->getDriver()->getConnection()->rollBack();
                throw $ex;
            }
        }
    }

    /**
     * 顧客情報編集結果APIリクエストパラメータ作成
     *
     * @param array $data
     * @return string リクエストパラメータ（XML文字列）
     */
    private function createRequestParamsResult($data) {
        // XMLデータ作成
        $doc = new \DOMDocument("1.0", "utf-8");

        // 受付
        $rootElement = $doc->appendChild($doc->createElement("receipt"));

        // 受付番号
        $receiptnoElement = $doc->createElement("receipt_no");
        $receiptnoElement->appendChild($doc->createTextNode(strval($data['AcceptNumber'])));
        $rootElement->appendChild($receiptnoElement);

        // 文字列化
        $xmlData = $doc->saveXML();

        return $xmlData;
    }

    /**
     * 顧客情報編集結果APIのレスポンス解析
     *
     * @param string $response レスポンスデータ（XML文字列）
     * @return array レスポンス解析結果
     */
    private function getResponseInfoResult($response) {
        // SimpleXMLを経由して連想配列に展開
        $xml_param = simplexml_load_string($response);
        $resp_data = get_object_vars($xml_param);

        // 結果
        $result = '';
        if (!is_null($resp_data['result'])) {
            $result = $resp_data['result'];
        }

        // エラーコード
        $error_code = '';
        if (!is_null($resp_data['error_code'])) {
            $error_code = $resp_data['error_code'];
        }

        if ($result == 'OK') {
            // 成功顧客
            $success_customers = array();
            // 複数ではない場合は直接フィールド名が入ってくるため整形
            if (is_null($resp_data['success_customers']->import_info[1])) {
                $import_info = new \stdClass();
                $import_info->customer_id = intval($resp_data['success_customers']->import_info->customer_id);
                $import_info->system_customer_id = intval($resp_data['success_customers']->import_info->system_customer_id);
                $success_customers_array['success_customers']->import_info[0] = $import_info;
            }
            else {
                $success_customers_array = $resp_data;
            }
            // 顧客ID、システム顧客IDのペアを連想配列に整形
            foreach($success_customers_array['success_customers']->import_info as $key=>$param) {
                $info = array();
                $info['customer_id'] = intval($param->customer_id);
                $info['system_customer_id'] = intval($param->system_customer_id);
                $success_customers[] = $info;
            }

            // 失敗顧客
            $faild_customers = array();
            // 複数ではない場合は直接フィールド名が入ってくるため整形
            if (is_null($resp_data['faild_customers']->import_info[1])) {
                $import_info = new \stdClass();
                $import_info->customer_id = intval($resp_data['faild_customers']->import_info->customer_id);
                $import_info->system_customer_id = intval($resp_data['faild_customers']->import_info->system_customer_id);
                $faild_customers_array['faild_customers']->import_info[0] = $import_info;
            }
            else {
                $faild_customers_array = $resp_data;
            }
            // 顧客ID、システム顧客IDのペアを連想配列に整形
            foreach($faild_customers_array['faild_customers']->import_info as $key=>$param) {
                $info = array();
                $info['customer_id'] = intval($param->customer_id);
                $info['system_customer_id'] = intval($param->system_customer_id);
                $faild_customers[] = $info;
            }
        }

        // 解析結果をまとめる
        $respResult = array();
        $respResult['result'] = $result;
        $respResult['error_code'] = $error_code;
        $respResult['success_customers'] = $success_customers;
        $respResult['faild_customers'] = $faild_customers;

        return $respResult;
    }

    /**
     * 顧客情報編集API連携
     */
    private function customerListImportConnect() {
        // バイパス設定がある場合、処理なし
        if ($this->_bypass_ilu) {
            return;
        }

        $mdlsp = new TableSystemProperty($this->_adapter);          // システムプロパティ
        $mdljsr = new TableJudgeSystemResponse($this->_adapter);    // 審査システム応答
        $mdlmc = new TableManagementCustomer($this->_adapter);      // 管理顧客

        // 顧客情報取得API連携URL取得
        $getCustomerInformationUrl = $mdlsp->getValue ('cbadmin', 'cj_api', 'GetCustomerInformation');

        // 顧客情報編集API連携URL取得
        $customerListImportUrl = $mdlsp->getValue ('cbadmin', 'cj_api', 'CustomerListImport');

        // 顧客情報編集API連携が必要なデータを取得
        $sql = <<<EOQ
SELECT (CASE
          WHEN IluCustomerId IS NOT NULL
           AND ValidFlg = 0              THEN 'D'
          WHEN IluCustomerId IS NOT NULL THEN 'U'
          ELSE                                'C'
        END) AS mode                                /* 編集モード */
     , IFNULL(IluCustomerId, 0) AS IluCustomerId    /* 与信顧客ID */
     , ManCustId                                    /* システム顧客ID */
     , NameKj                                       /* 氏名 */
     , NameKn                                       /* 氏名かな */
     , REPLACE(PostalCode, '-', '') AS PostalCode   /* 郵便番号 */
     , UnitingAddress                               /* 住所 */
     , Phone                                        /* 電話番号 */
     , CASE
         WHEN (SELECT COUNT(1) FROM M_DummyMailAddressList WHERE MailAddress = T_ManagementCustomer.MailAddress) > 0 THEN ''
         ELSE MailAddress
       END AS MailAddress /* メールアドレス */
     , (CASE
          WHEN BlackFlg = 1 THEN 'B'
          WHEN GoodFlg = 1 THEN  'W'
          ELSE                   'N'
        END) AS status                              /* ステータス */
     , 0 AS version                                 /* レコードバージョン */
  FROM T_ManagementCustomer
 WHERE IluCustomerListFlg = 1
   AND NOT (    IluCustomerId IS NULL
            AND ValidFlg = 0
           )
 ORDER BY ManCustId
EOQ;
        $ri = $this->_adapter->query ( $sql )->execute (null);
        $custDatas = ResultInterfaceToArray($ri);

        if (!empty($custDatas)) {
            // 対象がある場合のみ処理を実行

            $udcusts = array();
            $manCustIdList = array();
            foreach ($custDatas as $custData) {
                // 更新・削除顧客のIDを取得
                if (in_array($custData['mode'], array('U', 'D'))) {
                    $udcusts[$custData['ManCustId']] = $custData['IluCustomerId'];
                }

                // 管理顧客番号のリストを作成（審査システム応答登録用）
                $manCustIdList[] = $custData['ManCustId'];
            }

            // 更新・削除顧客が存在する場合
            if (!empty($udcusts)) {
                // 顧客情報取得API連携

                // リクエストパラメータ作成
                $reqParams = $this->createRequestParamsInfo($udcusts);

                // WebAPI連携実行
                $response = $this->apiConnect($getCustomerInformationUrl, $reqParams);
//sode
// echo $response;
                // レスポンス解析
                $resData = $this->getResponseInfoInfo($response);

                // バージョンを反映
                foreach ($custDatas as $key=>$value) {
                    // 与信顧客IDで該当あり
                    if (!empty($resData[$value['IluCustomerId']])) {
                        $custDatas[$key]['version'] = $resData[$value['IluCustomerId']];
                    }
                }
            }

            // 顧客情報編集API連携

            // リクエストパラメータ作成
            $reqParams = $this->createRequestParamsImport($custDatas);

            // リクエスト to JSON
            $xml_param = simplexml_load_string($reqParams);
            $req_data = Json::encode($xml_param);

            // 連携する管理顧客番号をJSON形式で保存
            $reserve = Json::encode($manCustIdList);

            // 審査システム応答登録
            $jsrKey = $mdljsr->saveNew(array(
                    'SendDate' => date('Y-m-d H:i:s'),  // 送信日時
                    'ReceiveDate' => null,              // 受信日時
                    'Status' => 0,                      // ステータス　0:未送信
                    'AcceptNumber' => null,             // 受付番号
                    'ConfirmStatus' => 0,               // 確認済みステータス　0:不要
                    'JudgeClass' => 1,                  // 連携種類　3:顧客情報（１）
                    'SentRawData' => $req_data,         // 送信データ
                    'ReceivedRawData' => null,          // 受信データ
                    'Reserve' => $reserve,              // 予約項目
                    'RegistId' => $this->_userId,       // 登録者
                    'UpdateId' => $this->_userId,       // 更新者
                    'ValidFlg' => 1,                    // 有効フラグ
                )
            );

            // WebAPI連携実行
            $response = $this->apiConnect($customerListImportUrl, $reqParams);
	 //
 	echo "/r";
 	echo $response;

            // 審査システム応答（今回分）　送信済みに更新
            $mdljsr->saveUpdate(array(
                    'Status' => 1,                      // ステータス　1:送信済み
                    'UpdateId' => $this->_userId,       // 更新者
            )
            , $jsrKey
            );

            // レスポンス to JSON
            $xml_param = simplexml_load_string($response);
            $resp_data = Json::encode($xml_param);

            // レスポンス解析
            $resData = $this->getResponseInfoImport($response);

            // DBに結果反映
            if ($resData['result'] == 'OK') {
                // 連携結果：OK

                // 審査システム応答　結果を反映
                $mdljsr->saveUpdate(array(
                        'ReceiveDate' => date('Y-m-d H:i:s'),       // 受信日時
                        'Status' => 9,                              // ステータス　9:受信済み
                        'AcceptNumber' => $resData['receipt_no'],   // 受付番号
                        'ReceivedRawData' => $resp_data,            // 受信データ
                        'ConfirmStatus' => 1,                       // 確認済みステータス　1:必要
                        'UpdateId' => $this->_userId,               // 更新者
                    )
                    , $jsrKey
                );

                // 管理顧客を更新
                foreach ($manCustIdList as $customer) {
                    $mdlmc->saveUpdate(array(
                            'IluCustomerListFlg' => 0,              // 審査システム－顧客情報連携フラグ
                            'UpdateId' => $this->_userId,           // 更新者
                        )
                        , $customer
                    );
                }
            }
            else {
                // 連携結果：NG

                // 審査システム応答　結果を反映
                $mdljsr->saveUpdate(array(
                        'ReceiveDate' => date('Y-m-d H:i:s'),   // 受信日時
                        'Status' => 9,                          // ステータス　9:受信済み
                        'ReceivedRawData' => $resp_data,        // 受信データ
                        'ConfirmStatus' => 3,                   // 確認済みステータス　3:エラー
                        'UpdateId' => $this->_userId,           // 更新者
                    )
                    , $jsrKey
                );
            }
        }
    }

    /**
     * 顧客情報取得APIリクエストパラメータ作成
     *
     * @param array $data
     * @return string リクエストパラメータ（XML文字列）
     */
    private function createRequestParamsInfo($data) {
        // XMLデータ作成
        $doc = new \DOMDocument("1.0", "utf-8");

        // 顧客IDリスト
        $rootElement = $doc->appendChild($doc->createElement("customer_id_list"));

        foreach ($data as $key=>$value) {
            // 顧客ID
            $customeridElement = $doc->createElement("customer_id");
            $customeridElement->appendChild($doc->createTextNode(strval($value)));
            $rootElement->appendChild($customeridElement);
        }

        // 文字列化
        $xmlData = $doc->saveXML();
//sode
// echo $xmlDate;

        return $xmlData;
    }

    /**
     * 顧客情報取得APIのレスポンス解析
     *
     * @param string $response レスポンスデータ（XML文字列）
     * @return array レスポンス解析結果
     */
    private function getResponseInfoInfo($response) {
        // SimpleXMLを経由して連想配列に展開
        $xml_param = simplexml_load_string($response);
        $resp_data = get_object_vars($xml_param);

        // 顧客一覧
        $customerList = array();
//        if (isset($resp_data['customer_info'])) {
// 20160210-sode 修正 想定とレスポンスXMLが異なるため　388行目～を参考
//20160213-sode 修正 変数名が 388行目～の値そのままだったので修正
        if (isset($resp_data['customer_list']->customer_info)) {
            // 複数ではない場合は直接フィールド名が入ってくるため整形
            if (is_null($resp_data['customer_list']->customer_info[1])) {
                $customer_info = new \stdClass();
                $customer_info->id = intval($resp_data['customer_list']->customer_info->id);
                $customer_info->version = intval($resp_data['customer_list']->customer_info->version);
                $response_customers_array['customer_list']->customer_info[0] = $customer_info;
             //   $customerList[$info['customer_id']] = $info['version'];
            }
            else {
		$response_customers_array = $resp_data;
	    }
            foreach($response_customers_array['customer_list']->customer_info as $key=>$param) {
                $info = array();
                $info['customer_id'] = intval($param->id);
                $info['version'] = intval($param->version);
                $customerList[$info['customer_id']] = $info['version'];
            }

/*
            if (!is_array($resp_data['customer_info'])) {
                $info = array();
                $info['customer_id'] = intval($resp_data['customer_info']->id);
                $info['version'] = intval($resp_data['customer_info']->version);

                $customerList[$info['customer_id']] = $info['version'];
            }
            else {
                foreach($resp_data['customer_info'] as $key=>$param) {
                    $info = array();
                    $info['customer_id'] = intval($param->id);
                    $info['version'] = intval($param->version);

                    $customerList[$info['customer_id']] = $info['version'];
                }
            }
*/
        }

        return $customerList;
    }

    /**
     * 顧客情報編集APIリクエストパラメータ作成
     *
     * @param array $data

     * @return string リクエストパラメータ（XML文字列）
     */
    private function createRequestParamsImport($data) {
        // XMLデータ作成
        $doc = new \DOMDocument("1.0", "utf-8");


	//20160206-sode-add
//       $doc = new \DOMDocument("1.0");

        // 顧客情報連携
       // $rootElement = $doc->appendChild($doc->createElement("customer_list"));

		//20160203-sode-add $doc->createCDATASection(
	  $rootElement = $doc->appendChild($doc->createElement("customer_list2"));

		//20160205-sode-add
	    $rootElement->setAttribute("xmlns:xsd", "http://www.w3.org/2001/XMLSchema");

		//20160205-sode-add
	    $rootElement->setAttribute("xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");

        foreach ($data as $customer) {
            // 顧客情報
            $customerinfoElement = $doc->createElement("customer_info");

		//20160203-sode-add
	    $customerinfoElement->setAttribute("xmlns", "http://ilu.co.jp/yoshin/customer_list");

            // 編集モード
            $modeElement = $customerinfoElement->appendChild($doc->createElement("mode"));
        //    $modeElement->appendChild($doc->createTextNode(strval($customer['mode'])));
	    $modeElement->appendChild($doc->createTextNode($customer['mode']));


            // 与信顧客ID
            $idElement = $customerinfoElement->appendChild($doc->createElement("id"));
            $idElement->appendChild($doc->createTextNode(strval($customer['IluCustomerId'])));


	 //2016_02_08 sode
//	    $idElement->appendChild($doc->createCDATASection($customer['ManCustId']));



            // 親顧客ID
            $parent_idElement = $customerinfoElement->appendChild($doc->createElement("parent_id"));
//            $parent_idElement->appendChild($doc->createTextNode(strval(0)));
            $parent_idElement->appendChild($doc->createTextNode(0));


            // システム情報
            $system_infoElement = $customerinfoElement->appendChild($doc->createElement("system_info"));

            // システムID
            $system_idElement = $system_infoElement->appendChild($doc->createElement("system_id"));
            $system_idElement->appendChild($doc->createTextNode(strval($this->_ilu_id)));

            // システム顧客ID
            $system_customer_idElement = $system_infoElement->appendChild($doc->createElement("system_customer_id"));
            $system_customer_idElement->appendChild($doc->createTextNode(strval($customer['ManCustId'])));
//	    $system_customer_idElement->appendChild($doc->createCDATASection($customer['ManCustId']));



            // 氏名
            $nameElement = $customerinfoElement->appendChild($doc->createElement("name"));
          //  $nameElement->appendChild($doc->createTextNode(strval($customer['NameKj'])));
	  //20160203-sode-add
            $nameElement->appendChild($doc->createCDATASection($customer['NameKj']));


            // 氏名かな
            $name_kanaElement = $customerinfoElement->appendChild($doc->createElement("name_kana"));
          //  $name_kanaElement->appendChild($doc->createTextNode(strval($customer['NameKn'])));
	  //20160203-sode-add
	      $name_kanaElement->appendChild($doc->createCDATASection($customer['NameKn']));

            // 顧客住所
            $addressElement = $customerinfoElement->appendChild($doc->createElement("address"));

            // 住所種類
            $kindElement = $addressElement->appendChild($doc->createElement("kind"));
//            $kindElement->appendChild($doc->createTextNode(strval(0)));
           $kindElement->appendChild($doc->createTextNode(0));

            // 郵便番号
            $postal_codeElement = $addressElement->appendChild($doc->createElement("postal_code"));
          //  $postal_codeElement->appendChild($doc->createTextNode($customer['PostalCode']));
	  //20160203-sode-add
	    $postal_codeElement->appendChild($doc->createCDATASection($customer['PostalCode']));

            // 住所
            $addressChildElement = $addressElement->appendChild($doc->createElement("address"));
          //  $addressChildElement->appendChild($doc->createTextNode($customer['UnitingAddress']));
	  //20160203-sode-add
	    $addressChildElement->appendChild($doc->createCDATASection($customer['UnitingAddress']));

            // 電話番号
            $telnoElement = $customerinfoElement->appendChild($doc->createElement("telno"));
          //  $telnoElement->appendChild($doc->createTextNode(strval($customer['Phone'])));
	  //20160203-sode-add
	    $telnoElement->appendChild($doc->createCDATASection($customer['Phone']));

            // メールアドレス
            $mailaddressElement = $customerinfoElement->appendChild($doc->createElement("mailaddress"));
         //   $mailaddressElement->appendChild($doc->createTextNode(strval($customer['MailAddress'])));
	  //20160203-sode-add
	    $mailaddressElement->appendChild($doc->createCDATASection($customer['MailAddress']));

	//20160206 sode 職業とｽﾃｰﾀｽを入れ替え

	    // ステータス
            $statusElement = $customerinfoElement->appendChild($doc->createElement("status"));
         //   $statusElement->appendChild($doc->createTextNode(strval($customer['status'])));
	  //20160203-sode-add
	    $statusElement->appendChild($doc->createCDATASection($customer['status']));

            // 職業
            $jobElement = $customerinfoElement->appendChild($doc->createElement("job"));
         //   $jobElement->appendChild($doc->createTextNode(''));
	  //20160203-sode-add
	    $jobElement->appendChild($doc->createCDATASection(''));

/*
            // ステータス
            $statusElement = $customerinfoElement->appendChild($doc->createElement("status"));
            $statusElement->appendChild($doc->createTextNode(strval($customer['status'])));
*/


            // 誕生日
            $birthElement = $customerinfoElement->appendChild($doc->createElement("birth"));
          //  $birthElement->appendChild($doc->createTextNode(''));
	  //20160203-sode-add
	    $birthElement->appendChild($doc->createCDATASection(''));

            // 性別
            $sexElement = $customerinfoElement->appendChild($doc->createElement("sex"));
          //  $sexElement->appendChild($doc->createTextNode(''));
	  //20160203-sode-add
	    $sexElement->appendChild($doc->createCDATASection(''));

            // 備考
            $noteElement = $customerinfoElement->appendChild($doc->createElement("note"));
          //  $noteElement->appendChild($doc->createTextNode(''));
	  //20160203-sode-add
	    $noteElement->appendChild($doc->createCDATASection(''));

            // レコードバージョン
            $versionElement = $customerinfoElement->appendChild($doc->createElement("version"));
            $versionElement->appendChild($doc->createTextNode(strval($customer['version'])));

	// 20160203 sode-del ILU松島氏より<order_info>以下は不要
/*
            // 注文情報
            $order_infoElement = $customerinfoElement->appendChild($doc->createElement("order_info"));

            // 注文ID
            $order_idElement = $order_infoElement->appendChild($doc->createElement("order_id"));
            $order_idElement->appendChild($doc->createTextNode(''));

            // 注文日
            $order_dateElement = $order_infoElement->appendChild($doc->createElement("order_date"));
            $order_dateElement->appendChild($doc->createTextNode(''));

            // 支払い総額
            $total_amountElement = $order_infoElement->appendChild($doc->createElement("total_amount"));
            $total_amountElement->appendChild($doc->createTextNode(''));

            // 支払い済額
            $total_paidElement = $order_infoElement->appendChild($doc->createElement("total_paid"));
            $total_paidElement->appendChild($doc->createTextNode(''));

            // 注文ステータス
            $order_statusElement = $order_infoElement->appendChild($doc->createElement("order_status"));
            $order_statusElement->appendChild($doc->createTextNode(''));

            // システムID
            $order_system_idElement = $order_infoElement->appendChild($doc->createElement("system_id"));
            $order_system_idElement->appendChild($doc->createTextNode(''));

            // システム注文ID
            $system_order_idElement = $order_infoElement->appendChild($doc->createElement("system_order_id"));
            $system_order_idElement->appendChild($doc->createTextNode(''));

            // 支払い情報
            $payment_infoElement = $order_infoElement->appendChild($doc->createElement("payment_info"));

            // 支払い期日
            $date_for_paymentElement = $payment_infoElement->appendChild($doc->createElement("date_for_payment"));
            $date_for_paymentElement->appendChild($doc->createTextNode(''));

            // 要支払い額
            $amountElement = $payment_infoElement->appendChild($doc->createElement("amount"));
            $amountElement->appendChild($doc->createTextNode(''));

            // 支払い済額
            $paidElement = $payment_infoElement->appendChild($doc->createElement("paid"));
            $paidElement->appendChild($doc->createTextNode(''));

            // 最終支払い日
            $date_of_last_paidElement = $payment_infoElement->appendChild($doc->createElement("date_of_last_paid"));
            $date_of_last_paidElement->appendChild($doc->createTextNode(''));
*/

            $rootElement->appendChild($customerinfoElement);
        }

        // 文字列化
        $xmlData = $doc->saveXML();

	//sode
	echo $xmlData;

        return $xmlData;
    }

    /**
     * 顧客情報編集APIのレスポンス解析
     *
     * @param string $response レスポンスデータ（XML文字列）
     * @return array レスポンス解析結果
     */
    private function getResponseInfoImport($response) {
        // SimpleXMLを経由して連想配列に展開
        $xml_param = simplexml_load_string($response);
        $resp_data = get_object_vars($xml_param);

        // 結果
        $result = '';
        if (!is_null($resp_data['result'])) {
            $result = $resp_data['result'];
        }

        // エラーメッセージ
        $error_message = '';
        if (!is_null($resp_data['error_message'])) {
            $error_message = $resp_data['error_message'];
        }

        // 受付番号
        $receipt_no = '';
        if ($result == 'OK') {
            $receipt_no = $resp_data['receipt_no'];
        }

        // 解析結果をまとめる
        $respResult = array();
        $respResult['result'] = $result;
        $respResult['error_message'] = $error_message;
        $respResult['receipt_no'] = $receipt_no;

        return $respResult;
    }
}