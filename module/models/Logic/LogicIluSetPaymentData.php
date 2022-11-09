<?php

namespace models\Logic;

use Coral\Base\BaseLog;
use Coral\Base\IO\BaseIOUtility;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Json\Json;
use Zend\Http\Client;
use Zend\Log\Logger;
use models\Table\TableJudgeSystemResponse;
use models\Table\TableManagementCustomer;
use models\Table\TableOem;
use models\Table\TableOrder;
use models\Table\TableSite;
use models\Table\TableSystemProperty;
use models\Logic\CreditJudge\LogicCreditJudgeAbstract;
use models\Logic\CreditJudge\LogicCreditJudgeSystemConnect;
use models\Logic\CreditJudge\SystemConnect\LogicCreditJudgeSystemConnectException;
use models\Table\TableClaimControl;

/**
 * 与信審査システム支払情報反映ロジック
 */
class LogicIluSetPaymentData {
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
     * 与信審査システムに支払情報を連携する
     * @throws Exception
     */
    public function setpaymentdata() {
        // API連携のタイムアウト時間
        $this->_timeouttime = $this->_config['cj_api']['timeout_time'];

        // 審査システム連携用のシステムID
        $this->_ilu_id = $this->_config['cj_api']['ilu_id'];

        // 審査システムバイパス設定
        $this->_bypass_ilu = $this->toBeBypassIlu();

        // 与信審査依頼API連携
        //$this->creditInspectionConnect();    // 20160208-sode 変更 与信審査処理廃止

        // 支払い情報編集API連携
        $this->setPaymentDataConnect();
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
    // 20160211-sode バイパス時間の考慮は不要
  //  if($this->isBypassTime()) return true;
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
     * 与信審査依頼API連携
     */
    private function creditInspectionConnect() {
        // バイパス設定がある場合、処理なし
        if ($this->_bypass_ilu) {
            return;
        }

        // 与信審査再連携対象取得
        $sql = <<<EOQ
SELECT OrderSeq
  FROM T_CjResult
       INNER JOIN (SELECT MAX(Seq) AS MaxSeq
                     FROM T_CjResult
                    GROUP BY OrderId
                  ) tbl ON tbl.MaxSeq = T_CjResult.Seq
 WHERE Status = 0
   AND ValidFlg = 1
   AND RegistDate <= :RegistDate
 ORDER BY OrderSeq
EOQ;
        $date = date('Y-m-d H:i:s', strtotime('-1 hour'));  // 1時間前
        $prm = array(
            ':RegistDate' => $date,
        );
        $ri = $this->_adapter->query($sql)->execute ($prm);
        $orders = ResultInterfaceToArray($ri);

        foreach ($orders as $order) {
            // 与信判定基準IDを取得する
            $creditCriterionId = $this->getCreditCriterionIdByOrder($order['OrderSeq']);

            // ILU審査システムへ注文登録
            $connector = new LogicCreditJudgeSystemConnect($this->_adapter, $this->_config['cj_api']);
            $connector->setCreditCriterionId($creditCriterionId);
            $connector->setUserId($this->_userId);
            $retry = 0;
            $retry_max = 2;
            while(++$retry <= $retry_max) {
                try {
                    $connector->sendTo($order['OrderSeq']);
                    break;  // エラーなしなら処理終了
                } catch(LogicCreditJudgeSystemConnectException $connError) {
                    // 接続絡みの例外時は既定回数リトライ
                    $this->debug((sprintf('[%s] SystemConnect::sentTo exception(%s times). -> %s', $order['OrderSeq'], $retry, $connError->getMessage())));
                    if($retry < $retry_max) {
                        // 既定回数未満の場合は1秒WAITを入れる
                        usleep(1 * 1000000);
                    } else {
                        // 既定回数に達したらエラー
                        throw $connError;
                    }
                } catch(\Exception $err) {
                    // その他の例外は上位へスロー
                    throw $err;
                }
            }
        }
    }

    /**
     * 支払い情報編集API連携
     */
    private function setPaymentDataConnect() {
        $mdljsr = new TableJudgeSystemResponse($this->_adapter);    // 審査システム応答
        $mdlcc = new TableClaimControl($this->_adapter); // 請求管理

        // 支払い情報編集URL
        $setPaymentDataUrl = $this->_config['cj_api']['SetPaymentData'];

        // 前回支払情報反映日時の取得
        $sql = <<<EOQ
SELECT MAX(SendDate) AS LastSendDate
  FROM T_JudgeSystemResponse
 WHERE Status = 9
   AND JudgeClass = 2
   AND ConfirmStatus <> 3
EOQ;
        $ri = $this->_adapter->query($sql)->execute (null);
        $lastSendDate = $ri->current();

        if($lastSendDate != false && is_null($lastSendDate['LastSendDate'])) {
            // 取得できない場合、全て対象
            $lastSendDate['LastSendDate'] = date('2016-02-23 20:00:00');

	//20160210-sode-temp テスト実行以降取得
	//TODO 本番稼働後移行後を取得？
	//$lastSendDate['LastSendDate'] = date('2019-06-15 00:00:00');

        }
        $prm = array(
            ':LastSendDate' => $lastSendDate['LastSendDate'],
        );

// 20160208-sode 変更 T_CjOrderIdControlの結合を[外部結合]⇒[内部結合]化
/* 20160213-sode 変更　	支払額0で審査システムにデータを渡すと、審査システムでは入金日で日付で請求期限日が更新されてしまう
			支払額0の場合は入金日ではなく、請求期限日をdate句にセットする
　　１－１）入金された注文（一部入金含む）通常の注文
	    変更前：MAX(p.ReceiptDate)           AS ReceiptDate
	    変更後： (CASE WHEN MAX(c.ReceiptAmountTotal) > 0 THEN MAX(p.ReceiptDate) ELSE c.F_LimitDate END)   AS ReceiptDate
*/

        $sql = <<<EOQ
/* １－１）入金された注文（一部入金含む）通常の注文 */
SELECT MAX(o.OrderId)               AS OrderId
     , MAX(o.DataStatus)            AS DataStatus
     , MAX(c.ReceiptAmountTotal)    AS ReceiptAmountTotal
     , MAX(o.UseAmount)             AS UseAmount
     , (CASE WHEN MAX(c.ReceiptAmountTotal) > 0 THEN MAX(p.ReceiptDate) ELSE c.F_LimitDate END)   AS ReceiptDate
     , NULL                         AS P_OrderSeq
     , MAX(co.IluOrderId)           AS IluOrderId
     , 5                            AS type
  FROM T_ReceiptControl p
       INNER JOIN T_Order o ON o.P_OrderSeq = p.OrderSeq
       INNER JOIN T_ClaimControl c ON o.P_OrderSeq = c.OrderSeq
       INNER JOIN T_CjOrderIdControl co ON co.OrderSeq = o.OrderSeq
 WHERE 1 = 1
   AND p.RegistDate > :LastSendDate
   AND IFNULL(o.CloseReason, 0) <> 2
   AND IFNULL(o.CombinedClaimTargetStatus, 0) = 0
   AND IFNULL(o.OutOfAmends, 0) <> 1
   AND o.T_OrderClass = 0
 GROUP BY o.OrderSeq

/* １－２）入金された注文（一部入金含む）請求取りまとめ対象の注文 */
UNION ALL
SELECT MAX(o.OrderId)               AS OrderId
     , MAX(o.DataStatus)            AS DataStatus
     , MAX(c.ReceiptAmountTotal)    AS ReceiptAmountTotal
     , MAX(o.UseAmount)             AS UseAmount
     , MAX(p.ReceiptDate)           AS ReceiptDate
     , MAX(o.P_OrderSeq)            AS P_OrderSeq
     , MAX(co.IluOrderId)           AS IluOrderId
     , 6                            AS type
  FROM T_ReceiptControl p
       INNER JOIN T_Order o ON o.P_OrderSeq = p.OrderSeq
       INNER JOIN T_ClaimControl c ON o.P_OrderSeq = c.OrderSeq
       INNER JOIN T_CjOrderIdControl co ON co.OrderSeq = o.OrderSeq
 WHERE 1 = 1
   AND p.RegistDate > :LastSendDate
   AND IFNULL(o.CloseReason, 0) <> 2
   AND IFNULL(o.CombinedClaimTargetStatus, 0) IN (91,92)
   AND IFNULL(o.OutOfAmends, 0) <> 1
   AND o.T_OrderClass = 0
 GROUP BY o.OrderSeq

/* ２）初回請求書（初回請求書再発行含む）が発行された注文 */
/* 20160207-sode 変更 cc.F_ClaimDate →cc.F_LimitDate */
UNION ALL
SELECT MAX(o.OrderId)               AS OrderId
     , MAX(o.DataStatus)            AS DataStatus
     , MAX(cc.ReceiptAmountTotal)   AS ReceiptAmountTotal
     , NULL                         AS UseAmount
     , MAX(F_LimitDate)             AS ReceiptDate
     , NULL                         AS P_OrderSeq
     , MAX(co.IluOrderId)           AS IluOrderId
     , 4                            AS type
  FROM T_Order o
       INNER JOIN T_ClaimControl cc ON cc.OrderSeq = o.P_OrderSeq
       INNER JOIN T_ClaimHistory ch ON ch.OrderSeq = o.P_OrderSeq
       INNER JOIN T_CjOrderIdControl co ON co.OrderSeq = o.OrderSeq
 WHERE 1 = 1
   AND o.DataStatus = 51
   AND ch.ClaimDate >= DATE(:LastSendDate)
   AND ch.ClaimPattern = 1
   AND ch.PrintedFlg = 1
   AND ch.ValidFlg = 1
   AND IFNULL(o.CloseReason, 0) <> 2
   AND IFNULL(o.OutOfAmends, 0) <> 1
   AND o.T_OrderClass = 0
 GROUP BY o.OrderSeq

/* ３）キャンセル承認された注文 */
UNION ALL
SELECT o.OrderId                    AS OrderId
     , o.DataStatus                 AS DataStatus
     , NULL                         AS ReceiptAmountTotal
     , NULL                         AS UseAmount
     , c.ApprovalDate               AS ReceiptDate
     , NULL                         AS P_OrderSeq
     , co.IluOrderId                AS IluOrderId
     , 1                            AS type
  FROM T_Order o
       INNER JOIN T_Cancel c ON c.OrderSeq = o.OrderSeq
       INNER JOIN T_CjOrderIdControl co ON co.OrderSeq = o.OrderSeq
 WHERE 1 = 1
   AND c.ApprovalDate > :LastSendDate
   AND c.ApproveFlg = 1
   AND IFNULL(o.CloseReason, 0) = 2
   AND IFNULL(o.OutOfAmends, 0) <> 1
   AND o.T_OrderClass = 0

/* ５）与信NGになった注文 */
UNION ALL
SELECT o.OrderId                    AS OrderId
     , o.DataStatus                 AS DataStatus
     , NULL                         AS ReceiptAmountTotal
     , NULL                         AS UseAmount
     , NULL                         AS ReceiptDate
     , NULL                         AS P_OrderSeq
     , co.IluOrderId                AS IluOrderId
     , 2                            AS type
  FROM T_Order o
       INNER JOIN T_CjOrderIdControl co ON co.OrderSeq = o.OrderSeq
 WHERE 1 = 1
   AND o.Incre_DecisionDate >= DATE(:LastSendDate)
   AND IFNULL(o.CloseReason, 0) = 3
   AND o.T_OrderClass = 0

/* ６）補償外となった注文 */
UNION ALL
SELECT MAX(o.OrderId)               AS OrderId
     , MAX(o.DataStatus)            AS DataStatus
     , NULL                         AS ReceiptAmountTotal
     , NULL                         AS UseAmount
     , NULL                         AS ReceiptDate
     , NULL                         AS P_OrderSeq
     , MAX(co.IluOrderId)           AS IluOrderId
     , 3                            AS type
  FROM T_Order o
       INNER JOIN T_ClaimHistory ch ON ch.OrderSeq = o.P_OrderSeq
       INNER JOIN T_CjOrderIdControl co ON co.OrderSeq = o.OrderSeq
 WHERE 1 = 1
   AND ch.ClaimDate >= DATE(:LastSendDate)
   AND ch.PrintedFlg = 1
   AND ch.ValidFlg = 1
   AND IFNULL(o.CloseReason, 0) <> 2
   AND IFNULL(o.OutOfAmends, 0) = 1
   AND o.T_OrderClass = 0
 GROUP BY o.OrderSeq

ORDER BY type
       , OrderId
EOQ;


        $ri = $this->_adapter->query($sql)->execute ($prm);
        $rows = ResultInterfaceToArray($ri);

        // 5000件ずつXMLを作成する形に切り分け → 修正時は以下の 5000 を修正すること
        $xml_slice_count = 5000;  // 5000件の切り分けを変更する場合は左記を修正してください
        $rowsCount = 0;
        if (!empty($rows)) {
            $rowsCount = count($rows);
        }
        $slice = (int)( ($rowsCount - 1) / $xml_slice_count);
        for ($i = 0; $i <= $slice; $i++ ) {
            $arrOrders[] = array_slice($rows, $i * $xml_slice_count, $xml_slice_count);
        }

        // 5000件ずつまわす
        foreach ($arrOrders as $orders) {

            // 5秒のインターバルを置きながら送信する
            sleep(5);

            // 対象がある場合のみ処理を実行

            // リクエストパラメータ用にデータ整形
            $paramDatas = array();
            $oldPOrderSeq = 0;
            $paymentBalance = 0;
            foreach ($orders as $order) {
                $paramData = array();

                if ($order['type'] == 1) {
                    // ３）キャンセル承認された注文

                    // 注文ID
                    $paramData['order_id'] = $order['IluOrderId'];

                    // システム注文ID
                    $paramData['system_order_id'] = $order['OrderId'];

                    // 注文ステータス　キャンセル
                    $paramData['status'] = 99;

                    // 連番
                    $paramData['sequence'] = '';

                    // 支払額
                    $paramData['paid_amount'] = '';

                    // 支払い期日 or 最終入金日
                    $paramData['date'] = '';
                }
                elseif ($order['type'] == 2) {
                    // ５）与信NGになった注文

                    // 注文ID
                    $paramData['order_id'] = $order['IluOrderId'];

                    // システム注文ID
                    $paramData['system_order_id'] = $order['OrderId'];

                    // 注文ステータス　キャンセル
                    $paramData['status'] = 99;

                    // 連番
                    $paramData['sequence'] = '';

                    // 支払額
                    $paramData['paid_amount'] = '';

                    // 支払い期日 or 最終入金日
                    $paramData['date'] = '';

                }
                elseif ($order['type'] == 3) {
                    // ６）補償外となった注文

                    // 注文ID
                    $paramData['order_id'] = $order['IluOrderId'];

                    // システム注文ID
                    $paramData['system_order_id'] = $order['OrderId'];

                    // 注文ステータス　キャンセル
                    $paramData['status'] = 99;

                    // 連番
                    $paramData['sequence'] = '';

                    // 支払額
                    $paramData['paid_amount'] = '';

                    // 支払い期日 or 最終入金日
                    $paramData['date'] = '';

                }
                elseif ($order['type'] == 4) {
                    // ２）初回請求書（初回請求書再発行含む）が発行された注文

                    // 注文ID
                    $paramData['order_id'] = $order['IluOrderId'];

                    // システム注文ID
                    $paramData['system_order_id'] = $order['OrderId'];

                    // 注文ステータス
                    $status = '';
                    if ($order['DataStatus'] == 51) {
                        // 未払い
                        $status = 10;
                    }
                    elseif ($order['DataStatus'] == 61) {
                        // 一部支払い
                        $status = 20;
                    }
                    elseif ($order['DataStatus'] == 91) {
                        // 支払済
                        $status = 30;
                    }
                    $paramData['status'] = $status;

                    // 連番
                    $paramData['sequence'] = 1;

                    // 支払額
                    $paramData['paid_amount'] = 0;

                    // 支払い期日 or 最終入金日
                    $paramData['date'] = $order['ReceiptDate'];
                }
                elseif ($order['type'] == 5) {
                    // １－１）入金された注文（一部入金含む）通常の注文

                    // 注文ID
                    $paramData['order_id'] = $order['IluOrderId'];

                    // システム注文ID
                    $paramData['system_order_id'] = $order['OrderId'];

                    // 注文ステータス
                    $status = '';
                    if ($order['DataStatus'] == 61) {
                        // 一部支払い
                        $status = 20;
                    }
                    elseif ($order['DataStatus'] == 91) {
                        // 支払済
                        $status = 30;
                    }
                    $paramData['status'] = $status;

                    // 連番
                    $paramData['sequence'] = 1;

                    // 支払額
                    $paramData['paid_amount'] = ($order['ReceiptAmountTotal'] > $order['UseAmount']) ? $order['UseAmount'] : $order['ReceiptAmountTotal'];// 20160208-sode 変更 過剰の場合はUseAmountとする

                    // 支払い期日 or 最終入金日
                    $paramData['date'] = $order['ReceiptDate'];
                }
                elseif ($order['type'] == 6) {
                    // １－２）入金された注文（一部入金含む）請求取りまとめ対象の注文

                    $prmDate = $order['ReceiptDate'];

                    // 請求取りまとめの親番号が変わった場合
                    if ($oldPOrderSeq != $order['P_OrderSeq']) {
                        // 入金残設定
                        $paymentBalance = $order['ReceiptAmountTotal'];

                        // 請求取りまとめ親番号設定
                        $oldPOrderSeq = $order['P_OrderSeq'];
                    }

                    // 支払額計算
                    $paid_amount = 0;
                    if ($paymentBalance >= $order['UseAmount']) {
                        // 利用額以上の入金がある場合、満額支払い
                        $paid_amount = $order['UseAmount'];

                        // 入金残から利用額を減らす
                        $paymentBalance -= $order['UseAmount'];
                    }
                    else {
                        // 利用額未満の場合、入金残が支払額
                        $paid_amount = $paymentBalance;

                        // 入金残は0
                        // TODO 20160213-sode 支払額($paid_amount)0となるときは、date句に請求期限をセットする
                        // （T.ClaimControl.F_LimitDateを取得し、$order['ReceiptDate']にセットする
                        if ( $paid_amount <= 0 ) {
                            $ri = $mdlcc->findClaim(array('OrderSeq' => $order['P_OrderSeq']));
                            $prmDate = $ri->current()['F_LimitDate'];
                        }

                        $paymentBalance = 0;
                    }

                    // 注文ID
                    $paramData['order_id'] = $order['IluOrderId'];

                    // システム注文ID
                    $paramData['system_order_id'] = $order['OrderId'];

                    // 注文ステータス
                    $status = '';
                    if ($order['DataStatus'] == 61) {
                        // 一部支払い
                        $status = 20;
                    }
                    elseif ($order['DataStatus'] == 91) {
                        // 支払済
                        $status = 30;
                    }
                    $paramData['status'] = $status;

                    // 連番
                    $paramData['sequence'] = 1;

                    // 支払額
                    $paramData['paid_amount'] = $paid_amount;

                    // 支払い期日 or 最終入金日
                    $paramData['date'] = $prmDate;
                }

                if (!empty($paramData)) {
                    if (strlen($paramData['date']) > 0) {
                        // 日付の形式変換
                        $paramData['date'] = date('Y/m/d', strtotime($paramData['date']));
                    }
                    $paramDatas[] = $paramData;
                }
            }

            // 支払い情報編集API連携

            // リクエストパラメータ作成
            $reqParams = $this->createRequestParamsPayment($paramDatas);

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
                    'JudgeClass' => 2,                  // 連携種類　2:支払情報
                    'SentRawData' => $req_data,         // 送信データ
                    'ReceivedRawData' => null,          // 受信データ
                    'Reserve' => null,                  // 予約項目
                    'RegistId' => $this->_userId,       // 登録者
                    'UpdateId' => $this->_userId,       // 更新者
                    'ValidFlg' => 1,                    // 有効フラグ
                )
            );

            // WebAPI連携実行
            $response = $this->apiConnect($setPaymentDataUrl, $reqParams);

	//20160211-sode-temp
//	 echo $response;


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
            $resData = $this->getResponseInfoPayment($response);

            // DBに結果反映
            if ($resData['result'] == 'OK') {
                // 連携結果：OK

                // 審査システム応答　結果を反映
                $mdljsr->saveUpdate(array(
                        'ReceiveDate' => date('Y-m-d H:i:s'),   // 受信日時
                        'Status' => 9,                          // ステータス　9:受信済み
                        'ReceivedRawData' => $resp_data,        // 受信データ
                        'UpdateId' => $this->_userId,           // 更新者
                    )
                    , $jsrKey
                );
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
     * 支払い情報編集APIリクエストパラメータ作成
     *
     * @param array $data
     * @return string リクエストパラメータ（XML文字列）
     */
    private function createRequestParamsPayment($data) {
        // XMLデータ作成
        $doc = new \DOMDocument("1.0", "utf-8");

        // 支払い情報編集
        $rootElement = $doc->appendChild($doc->createElement("payment_list"));

        foreach ($data as $order) {
            // 支払い情報
            $paymentinfoElement = $doc->createElement("payment_info");

            // 注文IDがある場合、注文IDのみ指定
            if (!empty($order['order_id'])) {
                // 注文ID
                $orderidElement = $paymentinfoElement->appendChild($doc->createElement("order_id"));
                $orderidElement->appendChild($doc->createTextNode(strval($order['order_id'])));

                // システムID
                $system_idElement = $paymentinfoElement->appendChild($doc->createElement("system_id"));
                $system_idElement->appendChild($doc->createTextNode(strval(0)));

                // システム注文ID
                $system_order_idElement = $paymentinfoElement->appendChild($doc->createElement("system_order_id"));
                $system_order_idElement->appendChild($doc->createTextNode(strval('')));
            }
            // 注文IDがない場合、システムID＋システム注文IDを指定
            else {
                // 注文ID
                $orderidElement = $paymentinfoElement->appendChild($doc->createElement("order_id"));
                $orderidElement->appendChild($doc->createTextNode(strval(0)));

                // システムID
                $system_idElement = $paymentinfoElement->appendChild($doc->createElement("system_id"));
                $system_idElement->appendChild($doc->createTextNode(strval($this->_ilu_id)));

                // システム注文ID
                $system_order_idElement = $paymentinfoElement->appendChild($doc->createElement("system_order_id"));
                $system_order_idElement->appendChild($doc->createTextNode(strval($order['system_order_id'])));
            }

            // 注文ステータス
  //          $statusElement = $paymentinfoElement->appendChild($doc->createElement("status"));
	 // 20160211-sode タグ名はstatus ではなくorder_statusがただしい
		$statusElement = $paymentinfoElement->appendChild($doc->createElement("order_status"));
            $statusElement->appendChild($doc->createTextNode(strval($order['status'])));

//　20160211-sode 支払済み額もステータス強制変更時は不要
            if ($order['status'] != 99) {// 20160208-sode 変更 ステータス強制変更（order_status=99を更新）する際はpayment_unit_info句は不要

            //20160206-sode 支払済み額（支払額と同じ値が入る）
            $total_paidElement = $paymentinfoElement->appendChild($doc->createElement("total_paid"));
            $total_paidElement->appendChild($doc->createTextNode(strval($order['paid_amount'])));


//            if ($order['status'] != 99) {// 20160208-sode 変更 ステータス強制変更（order_status=99を更新）する際はpayment_unit_info句は不要
            // 支払い単位情報
            $payment_unit_infoElement = $paymentinfoElement->appendChild($doc->createElement("payment_unit_info"));

            // 連番
            $sequenceElement = $payment_unit_infoElement->appendChild($doc->createElement("sequence"));
            $sequenceElement->appendChild($doc->createTextNode(strval($order['sequence'])));

            // 支払い額
            $paid_amountElement = $payment_unit_infoElement->appendChild($doc->createElement("paid_amount"));
            $paid_amountElement->appendChild($doc->createTextNode(strval($order['paid_amount'])));

            // 支払い期日 or 最終入金日
            $dateElement = $payment_unit_infoElement->appendChild($doc->createElement("date"));
            $dateElement->appendChild($doc->createTextNode(strval($order['date'])));
            }

            $rootElement->appendChild($paymentinfoElement);
        }

        // 文字列化
        $xmlData = $doc->saveXML();

	//20160211 temp-sode テスト用
//	echo $xmlData;

        return $xmlData;
    }

    /**
     * 支払い情報編集APIのレスポンス解析
     *
     * @param string $response レスポンスデータ（XML文字列）
     * @return array レスポンス解析結果
     */
    private function getResponseInfoPayment($response) {
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

        // 解析結果をまとめる
        $respResult = array();
        $respResult['result'] = $result;
        $respResult['error_message'] = $error_message;

        return $respResult;
    }

    /**
     * 指定注文の与信判定基準IDを取得する
     *
     * @param int $oseq 注文SEQ
     * @return int 与信判定基準ID(見つからない場合0)
     */
    protected function getCreditCriterionIdByOrder($oseq)
    {
        // 注文情報を取得
        $mdlo = new TableOrder($this->_adapter);
        $ri = $mdlo->find($oseq);
        // 取得できない場合、0
        if (!($ri->count() > 0)) {
            return 0;
        }
        $order = $ri->current();

        // サイトの与信判定基準ID
        $siteId = $order['SiteId'];
        if (isset($siteId)) {
            $mdlSite = new TableSite($this->_adapter);
            $ri = $mdlSite->findSite($siteId);
            // 取得できない場合、次の判定へ
            if ($ri->count() > 0) {
                $site = $ri->current();

                if (isset($site['CreditCriterion']) && $site['CreditCriterion'] > 0) {
                    // 取得できた場合終了
                    $this->debug(sprintf('[%s] getCreditCriterionIdByOrder Site CreditCriterionId = %s', $oseq, $site['CreditCriterion']));
                    return $site['CreditCriterion'];
                }
            }
        }

        // OEMの与信判定基準ID
        $oemId = $order['OemId'];
        if (isset($oemId)) {
            $mdlOem = new TableOem($this->_adapter);
            $ri = $mdlOem->find($oemId);
            // 取得できない場合、次の判定へ
            if ($ri->count() > 0) {
                $oem = $ri->current();

                if (isset($oem['CreditCriterion']) && $oem['CreditCriterion'] > 0) {
                    // 取得できた場合終了
                    $this->debug(sprintf('[%s] getCreditCriterionIdByOrder Oem CreditCriterionId = %s', $oseq, $oem['CreditCriterion']));
                    return $oem['CreditCriterion'];
                }
            }
        }

        // システムプロパティの与信判定基準ID
        $mdlSysP = new TableSystemProperty($this->_adapter);
        $sysP = $mdlSysP->getValue('[DEFAULT]', 'systeminfo', 'CreditCriterion');
        if (!is_null($sysP) && strlen($sysP) > 0 && $sysP >= 0) {
            // 取得できた場合終了
            $this->debug(sprintf('[%s] getCreditCriterionIdByOrder System CreditCriterionId = %s', $oseq, $sysP));
            return $sysP;
        }

        // 見つからなかった場合、0
        $this->debug(sprintf('[%s] getCreditCriterionIdByOrder Not Found CreditCriterionId = %s', $oseq, 0));
        return 0;
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
