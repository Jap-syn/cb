<?php
chdir(dirname(__DIR__));

// Setup autoloading
require 'init_autoloader.php';

setlocale( LC_ALL, 'ja_JP.UTF-8' );
ini_set( 'default_charset', 'UTF-8' );
mb_internal_encoding( 'UTF-8' );
mb_http_output('UTF-8');
mb_regex_encoding( 'UTF-8' );

ini_set( 'max_execution_time', 0 );

use Coral\Base\Application\BaseApplicationAbstract;
use Coral\Base\BaseLog;
use Coral\Base\IO\BaseIOUtility;
use Zend\Config\Reader\Ini;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Http\Client;
use Zend\Json\Json;
use models\Logic\CreditJudge\LogicCreditJudgeOptions;
use models\Logic\CreditJudge\Connector\LogicCreditJudgeConnectorIlu;
use models\Table\TableCjOrderIdControl;
use models\Table\TableManagementCustomer;
use models\Table\TableUser;
use models\Table\TableClaimControl; //20160219-sode add

/**
 * 支払情報連携
 *
 */
class Application extends BaseApplicationAbstract {
    protected $_application_id = 'tools-trans_iludata-batch';

    /**
     * Application の唯一のインスタンスを取得します。
     *
     * @static
     * @access public
     * @return Application
     */
    public static function getInstance() {
        if( self::$_instance === null ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Application の新しいインスタンスを初期化します。
     *
     * @ignore
     * @access private
     */
    private function __construct() {
        parent::init();
    }

    /**
     * @var Adapter
     */
    private $dbAdapter;

    /**
     * ログクラス
     *
     * @var BaseLog
     */
    private $logger;

    /**
     * 設定データ
     *
     * @access private
     * @var array
     */
    private $configs;

    /**
     * ユーザーID
     *
     * @var int
     */
    private $userId;

    /**
     * ILUコネクタ
     *
     * @access private
     * @var LogicCreditJudgeConnectorIlu
     */
    private $connector;

    /**
     * 審査システムから受信した展開済みデータ
     *
     * @access private
     * @param array
     */
    private $received_data;
    /**
     * 審査システムから受信したデータを取得する
     *
     * @return array
     */
    private function getReceivedData() {
        return $this->received_data;
    }
    /**
     * 審査システムから受信したXMを展開したLデータをセットする
     *
     * @param array $data 受信したXMLを展開したデータ
     */
    private function setReceivedData($data) {
        $this->received_data = $data;
    }

    /**
     * 送信に使用したXMLデータを取得する
     *
     * @return string
     */
    public function getSentXml($format = false) {
        if(!$format) return $this->connector->getSentData();
        $dom = new \DOMDocument('1.0', 'utf-8');
        $dom->loadXML($this->connector->getSentData());
        $dom->formatOutput = true;
        return $dom->saveXML();
    }

    /**
     * 受信したXMLデータを取得する
     *
     * @return string
     */
    public function getReceivedXml() {
        return $this->connector->getReceivedData();
    }

    /**
     * 受付番号
     * @var int
     */
    private $receipt_no;

    /**
     * コマンドラインのパラメータ
     */
    private static $_argv;
    public static function setArgv($arg) {
        self::$_argv = $arg;
    }

    /**
     * アプリケーションを実行します。
     *
     * @access public
     */
    public function run() {
        $exitCode = 1;

        try {
            // iniファイルから設定を取得
            $configPath = __DIR__ . '/../module/cbadmin/config/config.ini';

            // データベースアダプタをiniファイルから初期化します
            $data = array();
            if (file_exists($configPath))
            {
                $reader = new Ini();
                $data = $reader->fromFile($configPath);
            }
            $this->dbAdapter = new Adapter($data['database']);

            // ログ設定の読み込み
            $logConfig = $data['log'];
            // 標準ログクラス初期化
            $this->logger = BaseLog::createFromArray( $logConfig );

            $start = microtime(true);
$this->logger->info('trans_iludata_02.php start');

            $globalConfig = include __DIR__ . '/../config/autoload/global.php';
            // 接続時間を設定する
            $rds_session_timezone = $globalConfig['RDS_SESSION_TIMEZONE'];
            if (isset($rds_session_timezone)) {
                $this->dbAdapter->query('SET SESSION time_zone = :time_zone')->execute(array(':time_zone'=>$rds_session_timezone));
            }

            // 設定をシステムプロパティテーブルから読み込み
            $apinfo = $this->getApplicationiInfo($this->dbAdapter, 'cbadmin');
            // iniファイルの内容をマージ
            $data = array_merge($data, $apinfo);

            $this->configs = $data;

            // ユーザーID取得
            $mdlu = new TableUser($this->dbAdapter);
            $this->userId = $mdlu->getUserId(TableUser::USERCLASS_SYSTEM, TableUser::SEQ_BATCH_USER);

            // 審査システムデータ移行処理
            $this->_exec();

$this->logger->info('trans_iludata_02.php end');
$this->logger->info(sprintf('trans_iludata_02.php elapsed time = %s', (microtime(true) - $start)));

            $exitCode = 0; // 正常終了

        } catch( \Exception $e ) {
            // エラーログを出力
            if ( isset($this->logger) ) {
$this->logger->err($e->getMessage());
            }
        }

        // 終了コードを指定して処理終了
        exit($exitCode);

    }


    /**
     * 審査システムデータ移行処理
     *  TODO sode 各処理を別バッチとする
     */
    private function _exec() {
/*
        // 与信審査依頼連携
        $this->CreditInspection();
*/

        // 支払情報連携
        $this->SetPaymentData();

/*
        // 顧客情報登録・編集連携
        $this->CustomerListImport();

        // 一定時間待機
        $sec = 10;    // 秒
        // パラメータで指定した秒数だけ待つ
        if (isset(self::$_argv[1]) && is_numeric(self::$_argv[1])) {
            $sec = (int)self::$_argv[1];
        }
        usleep($sec * 1000000);

        // 顧客情報編集結果取得
        $this->GetCustomerListImportResult();
*/
    }

    /**
     * 与信審査依頼
     */
    private function CreditInspection() {

        $start = microtime(true);
$this->logger->info('CreditInspection start');

        // 移行対象注文取得
        $sql = <<<EOQ
SELECT o.OrderSeq
  FROM T_Order o
       LEFT OUTER JOIN T_CjOrderIdControl cj ON o.OrderSeq = cj.OrderSeq
 WHERE o.DataStatus <= 61
   AND IFNULL(o.OutOfAmends, 0) <> 1
   AND cj.OrderSeq IS NULL
 ORDER BY o.OrderSeq
EOQ;
        $ri = $this->dbAdapter->query($sql)->execute (null);
        $orders = ResultInterfaceToArray($ri);

        $this->connector = new LogicCreditJudgeConnectorIlu($this->configs['cj_api']);

        // ILUシステムへ注文を登録
        foreach ($orders as $order) {
            $retry = 0;
            $retry_max = 2;
            while(++$retry <= $retry_max) {
                try {
                    // ILUシステム連携
                    $this->sendTo($order['OrderSeq']);
                    break;  // エラーなしなら処理終了
                } catch(LogicCreditJudgeSystemConnectException $connError) {
                    // 接続絡みの例外時は既定回数リトライ
                    $this->logger->debug((sprintf('[%s] SystemConnect::sentTo exception(%s times). -> %s', $order['OrderSeq'], $retry, $connError->getMessage())));
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

$this->logger->info('CreditInspection end');
$this->logger->info(sprintf('CreditInspection elapsed time = %s', (microtime(true) - $start)));
    }


    /**
     * 審査システムへ注文情報を送信し、結果を永続化する
     *
     * @param $oseq 注文SEQ
     */
    private function sendTo($oseq){

        // 送信処理実行
        $this->sendToIlu($oseq);

        // 受信結果を保存
        $this->saveResult($oseq);

        // 送受信データをログとして永続化
        $this->saveLog($oseq);
    }

    /**
     * 指定注文の情報をILU審査システムへ送信する
     *
     * @access private
     * @param $oseq 注文SEQ
     */
    private function sendToIlu($oseq) {

        $connector = $this->connector;

        // 送信データ生成
        $params = $this->buildRequestParams($oseq);
        // 送信実行
        $result = $connector->connect($params);

        // 結果を退避(解析済みデータ)
        // ※：XML形式のデータは送受信ともILUコネクタにキャッシュされているので
        //    必要な場合はそこから取得する
        $this->setReceivedData($result);
    }

    /**
     * 審査システム送信向けのデータをDBから取得する
     *
     * @access private
     * @param int $oseq 注文SEQ
     * @return array 連携送信に必要な注文関連データ一式を格納した連想配列
     */
    private function buildRequestParams($oseq) {

        //----- system_id用データ取得
        $system_id = array('system_id' => $this->configs['cj_api'][LogicCreditJudgeOptions::ILU_ID]);

        //----- order_info用データ取得 --------

        // 注文情報取得
        $order_data = $this->getOrder($oseq);

        // 送料・手数料情報取得
        $order_items = $this->getFees($oseq);

        //----- customer_info用データ取得 --------

        //顧客情報取得
        $customer_data = $this->getCustomer($oseq);

        //取得した郵便番号は-を取り除く
        $customer_data['PostalCode'] = str_replace("-", "", $customer_data['PostalCode']);

        //----- destination_info用データ取得 --------

        //データ取得前に初期化
        $destination_data = array();

        //別配送先フラグが立っていれば届先情報取得
        if(intval($order_data['AnotherDeliFlg'])){
            $destination_data = $this->getDestination($oseq);

            //-をに変換
            $destination_data['PostalCode'] = str_replace("-", "", $destination_data['PostalCode']);
        }

        //----- order_detail用データ取得 --------

        //明細情報取得
        $order_detail_data = $this->getOrderDetail($oseq);

        // すべてのデータをキーに関連付けて返す
        return array(
                'system_id' => $system_id,
                'order_data' => $order_data,
                'order_items' => $order_items,
                'customer_data' => $customer_data,
                'destination_data' => $destination_data,
                'order_detail_data' => $order_detail_data
        );
    }

    /**
     * 注文情報取得
     * @access private
     * @param $oseq 注文Seq
     * @return array　注文情報
     */
    private function getOrder($oseq) {

        //注文情報を取得するSQL作成
        $q = <<<EOQ
SELECT EnterpriseId,OrderId,ReceiptOrderDate,
SiteId,Ent_OrderId,UseAmount,AnotherDeliFlg,Ent_Note
FROM T_Order
WHERE %s
EOQ;
        $query = sprintf($q, " OrderSeq = :OrderSeq ");

        //注文情報取得
        $ri = $this->dbAdapter->query($query)->execute(array(':OrderSeq' => $oseq));

        //取得できなかった場合例外
        if (!($ri->count() > 0)) {
            throw new \Exception(sprintf('order data not found, seq = %s', $oseq));
        }

        $rs = new ResultSet();
        $rs->initialize($ri);
        return $rs->toArray()[0];
    }

    /**
     * 送料・手数料取得
     * @access private
     * @param $oseq 注文Seq
     * @return array　注文情報
     */
    private function getFees($oseq) {

        //送料取得SQL作成
        $q = <<<EOQ
SELECT SumMoney FROM T_OrderItems
WHERE %s
EOQ;
        $query = sprintf($q, " DataClass = 2 AND OrderSeq = :OrderSeq ");

        //送料取得
        $ri = $this->dbAdapter->query($query)->execute(array(':OrderSeq' => $oseq));

        //取得できなかった場合例外
        if (!($ri->count() > 0)) {
            throw new \Exception(sprintf('carriage fee not found, seq = %s', $oseq));
        }
        $postage_data = $ri->current();

        //手数料SQL作成
        $query = sprintf($q, " DataClass = 3 AND OrderSeq = :OrderSeq ");

        //注文情報取得
        $ri = $this->dbAdapter->query($query)->execute(array(':OrderSeq' => $oseq));

        //取得できなかった場合例外
        if (!($ri->count() > 0)) {
            throw new \Exception(sprintf('charge fee not found, seq = %s', $oseq));
        }
        $commission_data = $ri->current();

        return array(
                "postage" => $postage_data['SumMoney'],
                "commission" => $commission_data['SumMoney'] );
    }

    /**
     * 顧客情報取得
     * @access private
     * @param $oseq 注文Seq
     * @return array　顧客情報
     */
    private function getCustomer($oseq) {

        //顧客情報取得
        $mdlmc = new TableManagementCustomer($this->dbAdapter);
        $ri = $mdlmc->findByOrderSeq($oseq);

        //取得できなかった場合例外
        if (!($ri->count() > 0)) {
            throw new \Exception(sprintf('customer data not found, seq = %s', $oseq));
        }

        $rs = new ResultSet();
        $rs->initialize($ri);
        return $rs->toArray()[0];
    }

    /**
     * 別配送先取得
     * @access private
     * @param $oseq 注文Seq
     * @return array　別配送先取得
     */
    private function getDestination($oseq) {

        //別配送先SQL作成
        $q = <<<EOQ
SELECT PostalCode,UnitingAddress,DestNameKj,DestNameKn,Phone
FROM V_Delivery
WHERE %s
EOQ;
        $query = sprintf($q, " DataClass = 1 AND OrderSeq = :OrderSeq ");

        //別配送先取得
        $ri = $this->dbAdapter->query($query)->execute(array(':OrderSeq' => $oseq));

        //取得できなかった場合例外
        if (!($ri->count() > 0)) {
            throw new \Exception(sprintf('destination data not found, seq = %s', $oseq));
        }

        $rs = new ResultSet();
        $rs->initialize($ri);
        return $rs->toArray()[0];
    }

    /**
     * 注文詳細(送料・手数料は含まない)
     * @access private
     * @param $oseq 注文Seq
     * @return array　注文詳細取得
     */
    private function getOrderDetail($oseq) {

        //注文詳細SQL作成
        $q = <<<EOQ
SELECT ItemNameKj,UnitPrice,ItemNum
FROM T_OrderItems
WHERE %s
EOQ;
        $query = sprintf($q, " DataClass = 1 AND ValidFlg = 1 AND OrderSeq = :OrderSeq ");

        //注文詳細取得
        $ri = $this->dbAdapter->query($query)->execute(array(':OrderSeq' => $oseq));

        //取得できなかった場合例外
        if (!($ri->count() > 0)) {
            throw new \Exception(sprintf('orderitems data not found, seq = %s', $oseq));
        }

        $rs = new ResultSet();
        $rs->initialize($ri);
        return $rs->toArray();
    }

    /**
     * 受信結果をDBに保存
     *
     * @access private
     * @param $oseq 注文SEQ
     */
    private function saveResult($oseq) {

        // 受信データ取得
        $rcv_data = $this->getReceivedData();

        try{
            // XML受信結果がOKだったら
            if($rcv_data['result'] == "OK"){
                // 管理顧客に審査システム顧客IDを保存
                $this->saveIluCustomerId($rcv_data);

                // 取得した内容を与信注文ID管理に格納
                $this->saveCjOrderIdControl($oseq, $rcv_data);
            }else{
                //OK以外は処理なし
                $this->logger->debug('ILU Connect Result NG.');
            }
        }catch(\Exception $err){
            //エラーメッセージ取得
            $error_msg = $err->getMessage();
            throw new \Exception("Error saveResult : " . $error_msg);
        }
    }

    /**
     * 審査システム顧客ID保存
     *
     * @access protected
     * @param $resp_data 受信データ
     */
    protected function saveIluCustomerId($resp_data) {

        //モデル
        $mngCust = new TableManagementCustomer($this->dbAdapter);

        //名寄せ情報がある場合のみ
        if (!is_null($resp_data['aggregation_list'])) {
            //与信審査した注文の顧客ID
            $orderManCustId = $resp_data['aggregation_list']->customer_info->system_customer_id;
            $orderIluCustomerId = $resp_data['aggregation_list']->customer_info->customer_id;

            if (!empty($orderManCustId)) {
                // 統合先の管理顧客
                $mc = $mngCust->find($orderManCustId)->current();
                if ($mc === false) {
                    // 統合先の管理顧客が存在しない場合、更新しない
                    return;
                }

                if ($mc != false && empty($mc['IluCustomerId']) && !empty($orderIluCustomerId)) {
                    // 管理顧客の審査システム－顧客ＩＤが未設定の場合、設定
                    $mngCust->saveUpdate(array(
                            'IluCustomerId' => $orderIluCustomerId,
                            'UpdateId' => $this->userId,
                    )
                    , $orderManCustId
                    );
                }
            }
        }
    }

    /**
     * 与信注文ID管理書き込み
     *
     * @access private
     * @param $order_seq 注文SEQ
     * @param $rcv_data 受信データ
     * @return int 結果値
     */
    private function saveCjOrderIdControl($order_seq, $rcv_data) {

        //与信注文ID管理
        $cjoc = new TableCjOrderIdControl($this->dbAdapter);

        // 注文Seqで該当するデータ取得
        $cjocData = $cjoc->find($order_seq);

        // データが存在しない場合、登録する
        if ($cjocData->count() == 0) {
            //注文ID
            $iluOrderId = $rcv_data['inspect_result']->order_info->order_id;

            //登録
            $data = array(
                    'OrderSeq' => $order_seq,
                    'IluOrderId' => $iluOrderId,
                    'RegistId' => $this->userId,
            );
            $cjoc->saveNew($data);
        }
    }

    /**
     * XML送信データ・受信データをログファイルとして保存する
     *
     * @access private
     * @param int $oseq 注文番号
     */
    private function saveLog($oseq) {

        //保存先取得
        $save_dir = $this->configs['cj_api'][LogicCreditJudgeOptions::SAVE_DIR];

        //送信データ保存
        $sent_data = $this->getSentXml();
        $sent_path = f_path($save_dir, 'iko_creditinspection_' . $oseq . '.xml', DIRECTORY_SEPARATOR);
        $sent_data_saved = @file_put_contents($sent_path, $sent_data);

        //受信データ保存
        $received_data = $this->getReceivedXml();
        $received_path =
        f_path($save_dir, sprintf('iko_creditinspection_%s_%s.xml', $oseq, date('YmdHis')), DIRECTORY_SEPARATOR);
        $received_data_saved = @file_put_contents($received_path, $received_data);

        // どちらかの保存に失敗した場合は例外
        if(!$sent_data_saved || !$received_data_saved) {
            $files = array();
            if(!$sent_data_saved) $files[] = $sent_path;
            if(!$received_data_saved) $files[] = $received_path;
            $msg = sprintf('cannot save log file: %s', join(' and ', $files));
            throw new \Exception($msg);
        }
    }

    /**
     * 支払情報連携
     */

    // 20160208-sode 変更 T_CjOrderIdControlの結合を[外部結合]⇒[内部結合]化
    /* 20160213-sode 変更　	支払額0で審査システムにデータを渡すと、審査システムでは入金日で日付で請求期限日が更新されてしまう
     支払額0の場合は入金日ではなく、請求期限日をdate句にセットする
    　　１－１）入金された注文（一部入金含む）通常の注文
    変更前：MAX(p.ReceiptDate)           AS ReceiptDate
    変更後： (CASE WHEN MAX(c.ReceiptAmountTotal) > 0 THEN MAX(p.ReceiptDate) ELSE c.F_LimitDate END)   AS ReceiptDate
    */


    private function SetPaymentData() {

        $start = microtime(true);
$this->logger->info('SetPaymentData start');
	
	 $mdlcc = new TableClaimControl($this->dbAdapter); // 請求管理 20160219-sode

        // 支払い情報編集URL
        $setPaymentDataUrl = $this->configs['cj_api']['SetPaymentData'];

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
   AND o.DataStatus = 61
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
   AND o.DataStatus = 61
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
     , MAX(cc.F_LimitDate)          AS ReceiptDate
     , NULL                         AS P_OrderSeq
     , MAX(co.IluOrderId)           AS IluOrderId
     , 4                            AS type
  FROM T_Order o
       INNER JOIN T_ClaimControl cc ON cc.OrderSeq = o.P_OrderSeq
       INNER JOIN T_ClaimHistory ch ON ch.OrderSeq = o.P_OrderSeq
       INNER JOIN T_CjOrderIdControl co ON co.OrderSeq = o.OrderSeq
 WHERE 1 = 1
   AND o.DataStatus = 51
   AND ch.ClaimPattern = 1
   AND ch.PrintedFlg = 1
   AND ch.ValidFlg = 1
   AND IFNULL(o.CloseReason, 0) <> 2
   AND IFNULL(o.OutOfAmends, 0) <> 1
   AND o.T_OrderClass = 0
 GROUP BY o.OrderSeq

ORDER BY type
       , OrderId
EOQ;
        $ri = $this->dbAdapter->query($sql)->execute ($prm);
        $orders = ResultInterfaceToArray($ri);

        if (count($orders) > 0) {
            // 対象がある場合のみ処理を実行

            // リクエストパラメータ用にデータ整形
            $paramDatas = array();
            $oldPOrderSeq = 0;
            $paymentBalance = 0;
            foreach ($orders as $order) {
                $paramData = array();

                if ($order['type'] == 4) {
                    // ２）初回請求書（初回請求書再発行含む）が発行された注文

                    // 注文ID
                    $paramData['order_id'] = $order['IluOrderId'];

                    // システム注文ID
                    $paramData['system_order_id'] = $order['OrderId'];

                    // 注文ステータス
                    // 未払い
                    $status = 10;
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
                    // 一部支払い
                    $status = 20;
                    $paramData['status'] = $status;

                    // 連番
                    $paramData['sequence'] = 1;

                    // 支払額
                   // $paramData['paid_amount'] = $order['ReceiptAmountTotal'];
                    // 支払額
                    // 20160208-sode 変更 過剰の場合はUseAmountとする
                    $paramData['paid_amount'] = ($order['ReceiptAmountTotal'] > $order['UseAmount']) ? $order['UseAmount'] : $order['ReceiptAmountTotal'];

                    // 支払い期日 or 最終入金日
                    $paramData['date'] = $order['ReceiptDate'];
                }
                elseif ($order['type'] == 6) {
                    // １－２）入金された注文（一部入金含む）請求取りまとめ対象の注文

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

                        // 入金残は0
                        $paymentBalance = 0;
                    }

                    // 注文ID
                    $paramData['order_id'] = $order['IluOrderId'];

                    // システム注文ID
                    $paramData['system_order_id'] = $order['OrderId'];

                    // 注文ステータス
                    $status = '';
                    if ($paid_amount == $order['UseAmount']) {
                        // 支払済
                        $status = 30;
                    }
                    elseif ($paid_amount > 0) {
                        // 一部支払い
                        $status = 20;
                    }
                    else {
                        // 未払い
                        $status = 10;
                    }
                    $paramData['status'] = $status;

                    // 連番
                    $paramData['sequence'] = 1;

                    // 支払額
                    $paramData['paid_amount'] = $paid_amount;

                    // 支払い期日 or 最終入金日
                    $paramData['date'] = $order['ReceiptDate'];
                }

                if (count($paramData) > 0) {
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

//sode-temp	
//	echo $setPaymentDataUrl;


// 20160221 sode -送信失敗時に再送できるように送信データを連携前に保存
            // WebAPI連携実行
//            $response = $this->apiConnect($setPaymentDataUrl, $reqParams);

//            // レスポンス解析
//            $resData = $this->getResponseInfoPayment($response);

            //保存先取得
            $save_dir = $this->configs['cj_api'][LogicCreditJudgeOptions::SAVE_DIR];

            //送信データ保存
            $sent_data = $reqParams;
            $sent_path = f_path($save_dir, 'iko_setpaymentdata.xml', DIRECTORY_SEPARATOR);
            $sent_data_saved = @file_put_contents($sent_path, $sent_data);


// 20160221 sode -送信失敗時に再送できるように送信データを連携前に保存
// 20160223 sode api連携はしない
/*
          // WebAPI連携実行
         $response = $this->apiConnect($setPaymentDataUrl, $reqParams);

            //受信データ保存
            $received_data = $response;
            $received_path = f_path($save_dir, sprintf('iko_setpaymentdata_%s.xml', date('YmdHis')), DIRECTORY_SEPARATOR);
            $received_data_saved = @file_put_contents($received_path, $received_data);
*/

            // どちらかの保存に失敗した場合は例外
//           if(!$sent_data_saved || !$received_data_saved) {
	if(!$sent_data_saved) {
                $files = array();
                if(!$sent_data_saved) $files[] = $sent_path;
                if(!$received_data_saved) $files[] = $received_path;
                $msg = sprintf('cannot save log file: %s', join(' and ', $files));
                throw new \Exception($msg);
            }
        }

$this->logger->info('SetPaymentData end');
$this->logger->info(sprintf('SetPaymentData elapsed time = %s', (microtime(true) - $start)));
    }

    /**
     * 支払い情報編集APIリクエストパラメータ作成
     *
     * @param array $data
     * @return string リクエストパラメータ（XML文字列）
     */
    private function createRequestParamsPayment($data) {

        // システムID
        $system_id = $this->configs['cj_api'][LogicCreditJudgeOptions::ILU_ID];

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
                $system_idElement->appendChild($doc->createTextNode(strval($system_id)));

                // システム注文ID
                $system_order_idElement = $paymentinfoElement->appendChild($doc->createElement("system_order_id"));
                $system_order_idElement->appendChild($doc->createTextNode(strval($order['system_order_id'])));
            }

            // 注文ステータス
        //    $statusElement = $paymentinfoElement->appendChild($doc->createElement("status"));
            // 20160211-sode タグ名はstatus ではなくorder_statusがただしい
            $statusElement = $paymentinfoElement->appendChild($doc->createElement("order_status"));
            $statusElement->appendChild($doc->createTextNode(strval($order['status'])));


            //　20160211-sode 支払済み額もステータス強制変更時は不要
            if ($order['status'] != 99) {// 20160208- 変更 ステータス強制変更（order_status=99を更新）する際はpayment_unit_info句は不要

            	//20160206-sode 支払済み額（支払額と同じ値が入る）
            	$total_paidElement = $paymentinfoElement->appendChild($doc->createElement("total_paid"));
            	$total_paidElement->appendChild($doc->createTextNode(strval($order['paid_amount'])));

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
        $time_out = $this->configs['cj_api']['timeout_time'];

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
     * 顧客情報編集API連携
     */
    private function CustomerListImport() {

        $start = microtime(true);
$this->logger->info('CustomerListImport start');

        $mdlmc = new TableManagementCustomer($this->dbAdapter);      // 管理顧客

        // 顧客情報取得API連携URL取得
        $getCustomerInformationUrl = $this->configs['cj_api']['GetCustomerInformation'];

        // 顧客情報編集API連携URL取得
        $customerListImportUrl = $this->configs['cj_api']['CustomerListImport'];

        // 顧客情報編集API連携が必要なデータを取得
        $sql = <<<EOQ
SELECT (CASE
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
     , MailAddress                                  /* メールアドレス */
     , (CASE
          WHEN BlackFlg = 1 THEN 'B'
          WHEN GoodFlg = 1 THEN  'W'
          ELSE                   'N'
        END) AS status                              /* ステータス */
     , 0 AS version                                 /* レコードバージョン */
  FROM T_ManagementCustomer
 WHERE GoodFlg = 1
    OR BlackFlg = 1
 ORDER BY ManCustId
EOQ;
        $ri = $this->dbAdapter->query ( $sql )->execute (null);
        $custDatas = ResultInterfaceToArray($ri);

        if (count($custDatas)) {
            // 対象がある場合のみ処理を実行

            $udcusts = array();
            $manCustIdList = array();
            foreach ($custDatas as $custData) {
                // 更新・削除顧客のIDを取得
                if (in_array($custData['mode'], array('U', 'D'))) {
                    $udcusts[$custData['ManCustId']] = $custData['IluCustomerId'];
                }
            }

            // 更新・削除顧客が存在する場合
            if (count($udcusts) > 0) {
                // 顧客情報取得API連携

                // リクエストパラメータ作成
                $reqParams = $this->createRequestParamsInfo($udcusts);

                // WebAPI連携実行
                $response = $this->apiConnect($getCustomerInformationUrl, $reqParams);

                // レスポンス解析
                $resData = $this->getResponseInfoInfo($response);

                //保存先取得
                $save_dir = $this->configs['cj_api'][LogicCreditJudgeOptions::SAVE_DIR];

                //送信データ保存
                $sent_data = $reqParams;
                $sent_path = f_path($save_dir, 'iko_customerinformation.xml', DIRECTORY_SEPARATOR);
                $sent_data_saved = @file_put_contents($sent_path, $sent_data);

                //受信データ保存
                $received_data = $response;
                $received_path = f_path($save_dir, sprintf('iko_customerinformation_%s.xml', date('YmdHis')), DIRECTORY_SEPARATOR);
                $received_data_saved = @file_put_contents($received_path, $received_data);

                // どちらかの保存に失敗した場合は例外
                if(!$sent_data_saved || !$received_data_saved) {
                    $files = array();
                    if(!$sent_data_saved) $files[] = $sent_path;
                    if(!$received_data_saved) $files[] = $received_path;
                    $msg = sprintf('cannot save log file: %s', join(' and ', $files));
                    throw new \Exception($msg);
                }

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

            // WebAPI連携実行
            $response = $this->apiConnect($customerListImportUrl, $reqParams);

            // レスポンス解析
            $resData = $this->getResponseInfoImport($response);

            //保存先取得
            $save_dir = $this->configs['cj_api'][LogicCreditJudgeOptions::SAVE_DIR];

            //送信データ保存
            $sent_data = $reqParams;
            $sent_path = f_path($save_dir, 'iko_customerlistimport.xml', DIRECTORY_SEPARATOR);
            $sent_data_saved = @file_put_contents($sent_path, $sent_data);

            //受信データ保存
            $received_data = $response;
            $received_path = f_path($save_dir, sprintf('iko_customerlistimport_%s.xml', date('YmdHis')), DIRECTORY_SEPARATOR);
            $received_data_saved = @file_put_contents($received_path, $received_data);

            // どちらかの保存に失敗した場合は例外
            if(!$sent_data_saved || !$received_data_saved) {
                $files = array();
                if(!$sent_data_saved) $files[] = $sent_path;
                if(!$received_data_saved) $files[] = $received_path;
                $msg = sprintf('cannot save log file: %s', join(' and ', $files));
                throw new \Exception($msg);
            }

            // DBに結果反映
            if ($resData['result'] == 'OK') {
                // 連携結果：OK

                // 受付番号
                $this->receipt_no = $resData['receipt_no'];
            }
            else {
                // 連携結果：NG

                // 受付番号
                $this->receipt_no = null;
            }
        }

$this->logger->info('CustomerListImport end');
$this->logger->info(sprintf('CustomerListImport elapsed time = %s', (microtime(true) - $start)));
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
        // 20160210-sode 修正 想定とレスポンスXMLが異なるため
        //20160213-sode 修正 変数名
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
        if (isset($resp_data['customer_info'])) {
            // 複数ではない場合は直接フィールド名が入ってくるため整形
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
            }*/
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

        // システムID
        $system_id = $this->configs['cj_api'][LogicCreditJudgeOptions::ILU_ID];

        // XMLデータ作成
        $doc = new \DOMDocument("1.0", "utf-8");

        // 顧客情報連携
//        $rootElement = $doc->appendChild($doc->createElement("customer_list"));

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
//            $modeElement->appendChild($doc->createTextNode(strval($customer['mode'])));
            $modeElement->appendChild($doc->createTextNode($customer['mode']));

            // 与信顧客ID
            $idElement = $customerinfoElement->appendChild($doc->createElement("id"));
            $idElement->appendChild($doc->createTextNode(strval($customer['IluCustomerId'])));

            // 親顧客ID
            $parent_idElement = $customerinfoElement->appendChild($doc->createElement("parent_id"));
//            $parent_idElement->appendChild($doc->createTextNode(strval(0)));
            $parent_idElement->appendChild($doc->createTextNode(0));

            // システム情報
            $system_infoElement = $customerinfoElement->appendChild($doc->createElement("system_info"));

            // システムID
            $system_idElement = $system_infoElement->appendChild($doc->createElement("system_id"));
            $system_idElement->appendChild($doc->createTextNode(strval($system_id)));

            // システム顧客ID
            $system_customer_idElement = $system_infoElement->appendChild($doc->createElement("system_customer_id"));
            $system_customer_idElement->appendChild($doc->createTextNode(strval($customer['ManCustId'])));

            // 氏名
            $nameElement = $customerinfoElement->appendChild($doc->createElement("name"));
         //   $nameElement->appendChild($doc->createTextNode(strval($customer['NameKj'])));
            //20160203-sode-add
            $nameElement->appendChild($doc->createCDATASection($customer['NameKj']));

            // 氏名かな
            $name_kanaElement = $customerinfoElement->appendChild($doc->createElement("name_kana"));
        //    $name_kanaElement->appendChild($doc->createTextNode(strval($customer['NameKn'])));
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
//            $postal_codeElement->appendChild($doc->createTextNode($customer['PostalCode']));
            //20160203-sode-add
            $postal_codeElement->appendChild($doc->createCDATASection($customer['PostalCode']));

            // 住所
            $addressChildElement = $addressElement->appendChild($doc->createElement("address"));
//            $addressChildElement->appendChild($doc->createTextNode($customer['UnitingAddress']));
            //20160203-sode-add
            $addressChildElement->appendChild($doc->createCDATASection($customer['UnitingAddress']));


            // 電話番号
            $telnoElement = $customerinfoElement->appendChild($doc->createElement("telno"));
//            $telnoElement->appendChild($doc->createTextNode(strval($customer['Phone'])));
            //20160203-sode-add
            $telnoElement->appendChild($doc->createCDATASection($customer['Phone']));


            // メールアドレス
            $mailaddressElement = $customerinfoElement->appendChild($doc->createElement("mailaddress"));
//            $mailaddressElement->appendChild($doc->createTextNode(strval($customer['MailAddress'])));
            //20160203-sode-add
            $mailaddressElement->appendChild($doc->createCDATASection($customer['MailAddress']));


            // 職業
            $jobElement = $customerinfoElement->appendChild($doc->createElement("job"));
//            $jobElement->appendChild($doc->createTextNode(''));
            //20160203-sode-add
            $jobElement->appendChild($doc->createCDATASection(''));

            // ステータス
            $statusElement = $customerinfoElement->appendChild($doc->createElement("status"));
//            $statusElement->appendChild($doc->createTextNode(strval($customer['status'])));
            //20160203-sode-add
            $statusElement->appendChild($doc->createCDATASection($customer['status']));


            // 誕生日
            $birthElement = $customerinfoElement->appendChild($doc->createElement("birth"));
//            $birthElement->appendChild($doc->createTextNode(''));
            //20160203-sode-add
            $birthElement->appendChild($doc->createCDATASection(''));

            // 性別
            $sexElement = $customerinfoElement->appendChild($doc->createElement("sex"));
//            $sexElement->appendChild($doc->createTextNode(''));
            //20160203-sode-add
            $sexElement->appendChild($doc->createCDATASection(''));

            // 備考
            $noteElement = $customerinfoElement->appendChild($doc->createElement("note"));
 //           $noteElement->appendChild($doc->createTextNode(''));
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

    /**
     * 顧客情報編集結果取得API連携
     */
    private function GetCustomerListImportResult() {

        if (is_null($this->receipt_no)) {
            // 顧客情報編集をしていない場合終了
$this->logger->info('GetCustomerListImportResult skip');
            return;
        }

        $start = microtime(true);
$this->logger->info('GetCustomerListImportResult start');

        $mdlmc = new TableManagementCustomer($this->dbAdapter);      // 管理顧客

        // 顧客情報編集結果取得API連携URL取得
        $getCustomerListImportResultUrl = $this->configs['cj_api']['GetCustomerListImportResult'];

        // 顧客情報編集結果取得API連携

        // リクエストパラメータ作成
        $reqParams = $this->createRequestParamsResult($this->receipt_no);

        // WebAPI連携実行
        $response = $this->apiConnect($getCustomerListImportResultUrl, $reqParams);

        // レスポンス解析
        $resData = $this->getResponseInfoResult($response);

        //保存先取得
        $save_dir = $this->configs['cj_api'][LogicCreditJudgeOptions::SAVE_DIR];

        //送信データ保存
        $sent_data = $reqParams;
        $sent_path = f_path($save_dir, 'iko_getcustomerlistimportresult.xml', DIRECTORY_SEPARATOR);
        $sent_data_saved = @file_put_contents($sent_path, $sent_data);

        //受信データ保存
        $received_data = $response;
        $received_path = f_path($save_dir, sprintf('iko_getcustomerlistimportresult_%s.xml', date('YmdHis')), DIRECTORY_SEPARATOR);
        $received_data_saved = @file_put_contents($received_path, $received_data);

        // どちらかの保存に失敗した場合は例外
        if(!$sent_data_saved || !$received_data_saved) {
            $files = array();
            if(!$sent_data_saved) $files[] = $sent_path;
            if(!$received_data_saved) $files[] = $received_path;
            $msg = sprintf('cannot save log file: %s', join(' and ', $files));
            throw new \Exception($msg);
        }

        // DBに結果反映
        try {
            // トランザクション開始
            $this->dbAdapter->getDriver()->getConnection()->beginTransaction();

            if ($resData['result'] == 'OK') {
                // 連携結果：OK

                // 管理顧客の更新
                // 成功顧客
                foreach ($resData['success_customers'] as $info) {
                    $mc = $mdlmc->find($info['system_customer_id'])->current();

                    // 該当顧客が存在し、審査システム顧客IDが未設定の場合更新
                    if ($mc !== false && is_null($mc['IluCustomerId'])) {
                        $mdlmc->saveUpdate(array(
                                'IluCustomerId' => $info['customer_id'],    // 審査システム－顧客ＩＤ
                                'UpdateId' => $this->userId,                // 更新者
                        )
                        , $info['system_customer_id']
                        );
                    }
                }
            }

            // コミット
            $this->dbAdapter->getDriver()->getConnection()->commit();
        }
        catch (\Exception $ex) {
            // ロールバック
            $this->dbAdapter->getDriver()->getConnection()->rollBack();
            throw $ex;
        }

$this->logger->info('GetCustomerListImportResult end');
$this->logger->info(sprintf('GetCustomerListImportResult elapsed time = %s', (microtime(true) - $start)));
    }

    /**
     * 顧客情報編集結果APIリクエストパラメータ作成
     *
     * @param array $data
     * @return string リクエストパラメータ（XML文字列）
     */
    private function createRequestParamsResult($receipt_no) {
        // XMLデータ作成
        $doc = new \DOMDocument("1.0", "utf-8");

        // 受付
        $rootElement = $doc->appendChild($doc->createElement("receipt"));

        // 受付番号
        $receiptnoElement = $doc->createElement("receipt_no");
        $receiptnoElement->appendChild($doc->createTextNode(strval($receipt_no)));
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
}

Application::setArgv($argv);
Application::getInstance()->run();
