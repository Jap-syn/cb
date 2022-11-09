<?php
namespace models\Logic\CreditJudge;

use DOMDocument;
use Zend\Config\Reader\Ini;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Log\Logger;
use models\Table\TableCjResultError;
use models\Table\TableCjResult;
use models\Table\TableCjResultDetail;
use models\Logic\CreditJudge\Connector\LogicCreditJudgeConnectorIlu;
use models\Table\TableCombinedList;
use models\Sequence\SequenceGeneral;
use models\Table\TableCreditPoint;
use models\Table\TableManagementCustomer;
use models\Table\TableSystemStatus;
use models\Table\TableCjOrderIdControl;
use Zend\Json\Json;
use models\Table\TableSite;
use models\Table\TableOrder;
use models\Table\TableSystemProperty;
use models\Table\TableCustomer;

/**
 * 審査システムとの連携に使用されます
 */
class LogicCreditJudgeSystemConnect  {
	/**
	 * デフォルトで読み込む設定ファイルのパス
	 * @static
	 * @access protected
	 * @var string
	 */
	protected static $__config_path;

	/**
	 * デフォルトで読み込む設定ファイルのパスを取得する
	 * @static
	 * @return string
	 */
	public static function getDefaultConfigPath() {
		return self::$__config_path;
	}
	/**
	 * デフォルトの設定情報を取得する。
	 * 事前にsetDefaultConfigPath()で設定ファイルのパスを指定している場合は、
	 * 既定の初期設定値に設定ファイルの内容をマージした値が返る。
	 * このメソッドが返す設定情報は、コンストラクタで第二引数を省略した場合に暗黙的に採用される
	 * @static
	 * @return array
	 */
	public static function getDefaultConfig() {
// ↓↓↓常にfalseになるはずなので、コメントアウトする(20150803_2134_suzuki_h)
// 		$path = self::getDefaultConfigPath();
// 		$config = array();
// 		if(is_file($path)) {
// 			// ファイルの存在を確認し、読み込んでセクション「cj_api」の内容の
// 			// 取得を試みる
// 			try {
// 			    $reader = new Ini();
// 			    $config = $reader->fromFile($path)['cj_api'];
// 			} catch(\Exception $err) {
// 			}
// 		}
// ↑↑↑常にfalseになるはずなので、コメントアウトする(20150803_2134_suzuki_h)
	    $config = array();
	    // 既定の設定情報とマージして返す
		return array_merge(
			array(
				'system_judge_debug_mode' => 0,
				'save_dir' => '.',
				'send_url' => 'http://202.94.134.244:8044/ILUYoshin/Inspect.asmx/CreditInspection',
				'system_judge_master_url' => 'http://202.94.134.244:8044/ILUYoshin/Inspect.asmx/GetServiceStatus',
				'default_average_unit_price_rate' => 2.5,
				'system_judge_debug_mode_type' => 1,
				'dummy_data_dir' => '.',
			),
			$config
		);
	}

	/**
	 * 審査システムから受信した展開済みデータ
	 *
	 * @access protected
	 * @param array
	 */
	protected $received_data;

	/**
	 * ILU審査システムパターンマスターデータ
	 *
	 * @access protected
	 * @var string XMLソース
	 */
	protected $pattern_master;

	/**
	 * ILU審査システムパターンマスターデータを最後に取得した日時
	 *
	 * @access protected
	 * @var int パターンマスター最終取得日時
	 */
	protected $pattern_master_last_updated;

	/**
	 * CjResultのシーケンス値
	 *
	 * @access protected
	 * @var int
	 */
	protected $cjresult_seq;

	/**
	 * DBアダプタ
	 *
	 * @access protected
	 * @var Adapter
	 */
	protected $_adapter = null;

	/**
	 * 設定データ
	 *
	 * @access protected
	 * @var array
	 */
	public $configs;

	/**
	 * ILUコネクタ
	 *
	 * @access protected
	 * @var LogicCreditJudgeConnectorIlu
	 */
	protected $_connector;

	/**
	 * 与信判定基準ID
	 *
	 * @access protected
	 * @var int
	 */
	protected $_creditCriterionId;

	/**
	 * ユーザーID
	 *
	 * @access protected
	 * @var int
	 */
	protected $_userId;

	/**
	 * LogicCreditJudgeSystemConnect の新しいインスタンスを初期化します。
	 *
	 * @param Adapter $adapter DBアダプタ
	 * @param null | array $configs 設定情報。省略時はgetDefaultConfig()静的メソッドで取得できる初期設定が採用される
	 */
	public function __construct(Adapter $adapter, array $configs = null) {
		 $this->_adapter = $adapter;

		//XML送信データ・受信データ・パターンマスタ初期化
		$this->pattern_master = null;

		if($configs === null) $configs = self::getDefaultConfig();
		if(!is_array($configs)) $configs = array();
		$this->configs = $configs;
		$this->_connector = new LogicCreditJudgeConnectorIlu($this->configs);
	}

	/**
	 * 審査システムから受信したデータを取得する
	 *
	 * @return array
	 */
	public function getReceivedData() {
		return $this->received_data;
	}
	/**
	 * 審査システムから受信したXMLデータをセットする
	 *
	 * @param array $data 受信したXMLを展開したデータ
	 */
	public function setReceivedData($data) {
		$this->received_data = $data;
	}

	/**
	 * 審査システムのパターンマスターデータを取得する
	 *
	 * @return string
	 */
	public function getPatternMaster() {
		return $this->pattern_master;
	}
	/**
	 * 審査システムのパターンマスターデータを設定する
	 *
	 * @param string $xml パターンマスターデータ
	 */
	public function setPatternMaster($xml) {
		$this->pattern_master = $xml;
	}

	/**
	 * CjResuktのシーケンス値を取得する
	 *
	 * @return int
	 */
	public function getCjResultSeq() {
		return $this->cjresult_seq;
	}
	/**
	 * CjResuktのシーケンス値を設定する
	 *
	 * @param int $seq
	 */
	public function setCjResultSeq($seq) {
		$this->cjresult_seq = $seq;
	}

	/**
	 * 送信に使用したXMLデータを取得する
	 *
	 * @return string
	 */
	public function getSentXml($format = false) {
		if(!$format) return $this->_connector->getSentData();
		$dom = new DOMDocument('1.0', 'utf-8');
		$dom->loadXML($this->_connector->getSentData());
		$dom->formatOutput = true;
		return $dom->saveXML();
	}

	/**
	 * 受信したXMLデータを取得する
	 *
	 * @return string
	 */
	public function getReceivedXml() {
		return $this->_connector->getReceivedData();
	}

	/**
	 * 与信判定基準IDを取得する
	 *
	 * @return number
	 */
	public function getCreditCriterionId() {
	    return $this->_creditCriterionId;
	}

	/**
	 * 与信判定基準IDを設定する
	 *
	 * @param int $creditCriterionId 与信判定基準ID
	 */
	public function setCreditCriterionId($creditCriterionId) {
	    $this->_creditCriterionId = $creditCriterionId;
	}

	/**
	 * ユーザーIDを取得する
	 *
	 * @return int
	 */
	public function getUserId() {
	    return $this->_userId;
	}

	/**
	 * ユーザーIDを設定する
	 *
	 * @param int $userId ユーザーID
	 */
	public function setUserId($userId) {
	    $this->_userId = $userId;
	}

	/**
	 * 審査システムへ注文情報を送信し、結果を永続化する
	 *
	 * @param $oseq 注文SEQ
	 */
	public function sendTo($oseq){
        // サービス情報取得APIの結果をシステムプロパティから取得
        $sysSt = new TableSystemStatus($this->_adapter);
        $CjServiceStatus = $sysSt->getCjServiceStatus();
        if ($CjServiceStatus == false || empty($CjServiceStatus['CjServiceStatus'])) {
            // サービス情報取得APIの結果がNG/応答なしの場合、処理を行わずに終了
            throw new \Exception(sprintf('[%s] Judge System Offline.', $oseq));
        }
        // パターンマスターを設定
        $CjServiceStatusAry = Json::decode($CjServiceStatus['CjServiceStatus'], Json::TYPE_ARRAY);
        $this->setPatternMaster($CjServiceStatusAry);

		// 送信処理実行
		$this->sendToIlu($oseq);

		// 送受信データをログとして永続化
		$this->saveLog();

		// 受信結果を保存
		$this->saveResult($oseq);
	}

	/**
	 * 指定注文の情報をILU審査システムへ送信する
	 *
	 * @access protected
	 * @param $oseq 注文SEQ
	 */
	protected function sendToIlu($oseq) {

	    $connector = $this->_connector;

		// 送信データ生成
		$params = $this->buildRequestParams($oseq);
        // 送信実行
		$result = $connector->connect($params);

		// 結果を退避
		// ※：XML形式のデータは送受信ともILUコネクタにキャッシュされているので
		//    必要な場合はそこから取得する
		$this->setReceivedData($result);

		//T_CjResultのステータスを更新する
		$this->updateCjResultStatus($oseq);
	}

	/**
	 * 受信データを永続化する
	 *
	 * @access protected
	 * @param $oseq 注文SEQ
	 */
	protected function saveResult($oseq) {

	    // 受信データ取得
	    $rcv_data = $this->getReceivedData();

	    $cjResult = new TableCjResult($this->_adapter);

	    //与信結果取得
        $ri = $cjResult->findCjResult(array('OrderSeq' => $oseq));

        //データがない場合エラー
        if (!($ri->count() > 0)) {
            throw new \Exception("CjResult data not found");
        }

        $cjdata = $ri->current();
        $GLOBALS['CreditLog']['CjrSeq'] = $cjdata['Seq'];

        try{
            //CjResult更新
            $method = 'updateCjResult';
            $this->updateCjResult($oseq, $rcv_data);

            //XML受信結果がOKだったら
            if($rcv_data['result'] == "OK"){

                //取得した内容を与信審査結果詳細DBに格納
                $method = 'saveCjResultDetail';
                $this->saveCjResultDetail($cjdata['Seq'], $oseq, $rcv_data);

                //取得した内容を与信注文ID管理に格納
                $method = 'saveCjOrderIdControl';
                $this->saveCjOrderIdControl($oseq, $rcv_data);

                //取得した内容を名寄せリストDBに格納
                $method = 'saveCombinedList';
                $this->saveCombinedList($rcv_data);

                // 連続注文判定処理
                $method = 'multiOrderProc';
                $this->multiOrderProc($oseq, $rcv_data);

                //与信審査結果の重みづけ
                $method = 'updateScoreWeighting';
                $this->updateScoreWeighting($cjdata['Seq'], $this->getCreditCriterionId(), $oseq);

            }else{
                //OK以外はエラーとする
                //エラー内容をDBに格納
                $method = 'saveCjResultError';
                $this->saveCjResultError($cjdata['Seq'], $oseq, $rcv_data);

            }

        }catch(\Exception $err){
            //エラーメッセージ取得
            $error_msg = $err->getMessage();
            throw new \Exception("Error Processing Request (" . $method . " : " . $error_msg . ")");

        }
	}

	/**
	 * 審査システム送信向けのデータをDBから取得する
	 *
	 * @access protected
	 * @param int $oseq 注文SEQ
	 * @return array 連携送信に必要な注文関連データ一式を格納した連想配列
	 */
	protected function buildRequestParams($oseq) {

	    //----- system_id用データ取得
	    $system_id = array('system_id' => $this->configs[LogicCreditJudgeOptions::ILU_ID]);

		//----- order_info用データ取得 --------

		// 注文情報取得
		$order_data = $this->getOrder($oseq);

		// 送料・手数料情報取得
		$order_items = $this->getFees($oseq);

		//----- customer_info用データ取得 --------

		//顧客情報取得
		$customer_data = $this->getCustomer($oseq);

		//顧客データ取得
		$mdlc = new TableCustomer($this->_adapter);
		$cust = $mdlc->findCustomer(array('OrderSeq' => $oseq))->current();
		$customer_data['MailAddress'] = $cust['MailAddress'];

		//取得した郵便番号は-を取り除く
		$customer_data['PostalCode'] = str_replace("-", "", $customer_data['PostalCode']);

		// ダミーアドレスと判定された場合は、空白とする。
		$sql = ' SELECT COUNT(1) cnt FROM M_DummyMailAddressList WHERE MailAddress = :MailAddress ';
		$prm = array(
		      ':MailAddress' => $customer_data['MailAddress'],
		);
		$cnt = $this->_adapter->query($sql)->execute($prm)->current()['cnt'];
		if ( $cnt > 0 ) {
		    // ダミードレスに該当する場合は、空白にする
		    $customer_data['MailAddress'] = '';
		}

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
	 * @access protected
	 * @param $oseq 注文Seq
	 * @return array　注文情報
	 */
	protected function getOrder($oseq) {

		//注文情報を取得するSQL作成
        $q = <<<EOQ
SELECT EnterpriseId,OrderId,ReceiptOrderDate,
SiteId,Ent_OrderId,UseAmount,AnotherDeliFlg,Ent_Note,
DATE(RegistDate) AS RegistDate
FROM T_Order
WHERE %s
EOQ;
        $query = sprintf($q, " OrderSeq = :OrderSeq ");

        //注文情報取得
        $ri = $this->_adapter->query($query)->execute(array(':OrderSeq' => $oseq));

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
	 * @access protected
	 * @param $oseq 注文Seq
	 * @return array　注文情報
	 */
	protected function getFees($oseq) {

		//送料取得SQL作成
		$q = <<<EOQ
SELECT SumMoney FROM T_OrderItems
WHERE %s
EOQ;
		$query = sprintf($q, " DataClass = 2 AND OrderSeq = :OrderSeq ");

		//送料取得
		$ri = $this->_adapter->query($query)->execute(array(':OrderSeq' => $oseq));

		//取得できなかった場合例外
		if (!($ri->count() > 0)) {
		    throw new \Exception(sprintf('carriage fee not found, seq = %s', $oseq));
		}
		$postage_data = $ri->current();

		//手数料SQL作成
		$query = sprintf($q, " DataClass = 3 AND OrderSeq = :OrderSeq ");

		//注文情報取得
		$ri = $this->_adapter->query($query)->execute(array(':OrderSeq' => $oseq));

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
	 * @access protected
	 * @param $oseq 注文Seq
	 * @return array　顧客情報
	 */
	protected function getCustomer($oseq) {

		//顧客情報取得
		$mdlmc = new TableManagementCustomer($this->_adapter);
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
	 * @access protected
	 * @param $oseq 注文Seq
	 * @return array　別配送先取得
	 */
	protected function getDestination($oseq) {

		//別配送先SQL作成
		$q = <<<EOQ
SELECT PostalCode,UnitingAddress,DestNameKj,DestNameKn,Phone
FROM V_Delivery
WHERE %s
EOQ;
		$query = sprintf($q, " DataClass = 1 AND OrderSeq = :OrderSeq ");

		//別配送先取得
		$ri = $this->_adapter->query($query)->execute(array(':OrderSeq' => $oseq));

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
	 * @access protected
	 * @param $oseq 注文Seq
	 * @return array　注文詳細取得
	 */
	protected function getOrderDetail($oseq) {

 		//注文詳細SQL作成
		$q = <<<EOQ
SELECT ItemNameKj,UnitPrice,ItemNum
FROM T_OrderItems
WHERE %s
EOQ;
        $query = sprintf($q, " DataClass = 1 AND ValidFlg = 1 AND OrderSeq = :OrderSeq ");

		//別配送先取得
		$ri = $this->_adapter->query($query)->execute(array(':OrderSeq' => $oseq));

		//取得できなかった場合例外
		if (!($ri->count() > 0)) {
		    throw new \Exception("T_OrderItems Not Found");
		}

		$rs = new ResultSet();
		$rs->initialize($ri);
		return $rs->toArray();
	}

	/**
	 * XML送信データ・受信データをログファイルとして保存する
	 *
	 * @access protected
	 */
	protected function saveLog() {

	    //保存先取得
		$save_dir = $this->configs[LogicCreditJudgeOptions::SAVE_DIR];

		// 送信データのファイル形式 cjresultのSeq.xml
		$seq = $this->getCjResultSeq();

//		//送信データ保存
//		$sent_data = $this->getSentXml();
//		$sent_path = f_path($save_dir, $seq . '.xml', DIRECTORY_SEPARATOR);
//		$sent_data_saved = @file_put_contents($sent_path, $sent_data);

		//受信データ保存
		$received_data = $this->getReceivedXml();
		$received_path =
			f_path($save_dir, sprintf('%s_%s.xml', $seq, date('YmdHis')), DIRECTORY_SEPARATOR);
		$received_data_saved = @file_put_contents($received_path, $received_data);

//		// どちらかの保存に失敗した場合は例外
////        if(!$sent_data_saved || !$received_data_saved) {
//		if(!$received_data_saved) {
//			$files = array();
////			if(!$sent_data_saved) $files[] = $sent_path;
//			if(!$received_data_saved) $files[] = $received_path;
//			$msg = sprintf('cannot save log file: %s', join(' and ', $files));
//			throw new \Exception($msg);
//		}
	}

	/**
	 * 与信審査結果を送信済みに更新
	 *
	 * @access protected
	 * @param $order_seq 注文SEQ
	 */
	protected function updateCjResultStatus($order_seq) {

		//T_CjResult
		$cjResult = new TableCjResult($this->_adapter);

		//更新
		$cjResult->setStatusUpdateSend($order_seq, 1, $this->getUserId());

		//Seqはログ保存時に使用するためプロパティへ
		//与信結果取得
		$ri = $cjResult->findCjResult(array('OrderSeq' => $order_seq));
		//データがない場合エラー
		if (!($ri->count() > 0)) {
		    throw new \Exception("CjResult data not found");
		}
		$cjdata = $ri->current();
		$this->setCjResultSeq($cjdata['Seq']);

	}

	/**
	 * 与信審査結果を受信済みに更新
	 *
	 * @access protected
	 * @param $order_seq 注文SEQ
	 * @param $rcv_data 受信データ
	 * @return int 結果値
	 */
	protected function updateCjResult($order_seq, $rcv_data) {

	    //T_CjResult
		$cjResult = new TableCjResult($this->_adapter);

		$score = null;
		$aggregationLevel0Cnt = 0;
		$aggregationLevel1Cnt = 0;

		//ResultによってResult値変化
		if($rcv_data['result'] == "OK"){
			//score取得
		    $score = $rcv_data['inspect_result']->order_info->total_score;

			//リザルト取得
			$result = 1;
		}else{
			//score取得
			$score = null;
			//リザルト取得
			$result = 2;
		}

		//類似顧客数、同一顧客数の計算
		//名寄せ情報がある場合のみ
		if (!is_null($rcv_data['aggregation_list'])) {
		    //与信審査した注文の顧客ID
		    $orderManCustId = $rcv_data['aggregation_list']->customer_info->system_customer_id;

		    if (!empty($orderManCustId)) {

		        //複数ではない場合は直接フィールド名が入ってくるため整形
		        if(is_null($rcv_data['aggregation_list']->aggregation_customer_info[1])){
		            $aggregation_customer_info = new \stdClass();
		            $aggregation_customer_info->aggregation_customer_id = intval($rcv_data['aggregation_list']->aggregation_customer_info->aggregation_customer_id);
		            $aggregation_customer_info->aggregation_level = intval($rcv_data['aggregation_list']->aggregation_customer_info->aggregation_level);
		            $aggregation_array['aggregation_list']->aggregation_customer_info[0] = $aggregation_customer_info;
		        }else{
		            $aggregation_array = $rcv_data;
		        }

		        // 審査システムによる名寄せ候補
		        foreach($aggregation_array['aggregation_list']->aggregation_customer_info as $key=>$param){
		            $aggregation_level = $param->aggregation_level;
		            if ($aggregation_level == 0) {
		                // 同一顧客
		                $aggregationLevel0Cnt++;
		            } elseif ($aggregation_level == 1) {
		                // 類似顧客
		                $aggregationLevel1Cnt++;
		            }
		        }
		    }
		}

		//更新
		$cjResult->setStatusUpdateReception($order_seq, $result, $this->getUserId(), $aggregationLevel0Cnt, $aggregationLevel1Cnt, $score);

		// 保存した受信ステータスを返す
		return $result;
	}

	/**
	 * 与信審査結果詳細書き込み
	 *
	 * @access protected
	 * @param $sql 与信結果ID
	 * @param $order_seq 注文No
	 * @param $resp_data 受信データ
	 */
	protected function saveCjResultDetail($seq, $order_seq, $resp_data) {

	    // パターンマスター取得
		$pattern_data = $this->getPatternMaster();

		//XML整形
		$cjResult_detail = new TableCjResultDetail($this->_adapter);
		//複数ではない場合は直接フィールド名が入ってくるため整形
		if(is_null($resp_data['inspect_result']->detailed_score_info[1])){
		    $detailed_score_info = new \stdClass();
			$detailed_score_info->detection_pattern = intval($resp_data['inspect_result']->detailed_score_info->detection_pattern);
			$detailed_score_info->detailed_score = intval($resp_data['inspect_result']->detailed_score_info->detailed_score);
			$inspect_array['inspect_result']->detailed_score_info[0] = $detailed_score_info;

		}else{
			$inspect_array = $resp_data;
		}

		foreach($inspect_array['inspect_result']->detailed_score_info as $key=>$param){
			//条件が複数あるかによって構成が違うため配列キーが数値かどうか判定
			$new_data = array("no" => intval($param->detection_pattern),"score"=>intval($param->detailed_score));

			//更新データ整形
			$new_param = array(
			        "CjrSeq" => $seq,
			        "OrderSeq" => $order_seq,
			        "DetectionPatternNo" => $new_data["no"],
			        "DetectionPatternName" => $pattern_data[intval($new_data['no'])]['name'],
			        "DetectionPatternScore" => $new_data["score"],
			        "DetectionPatternScoreWeighting" => null,
			        "RegistId" => $this->getUserId(),
			        "UpdateId" => $this->getUserId(),
			        "ValidFlg" => 1,
			);

			$cjResult_detail->saveNew($new_param);
		}
	}

	/**
	 * 与信審査結果エラー情報を保存
	 *
	 * @access protected
	 * @param $seq 与信結果ID
	 * @param $order_seq 注文SEQ
	 * @param $resp_data 受信データ
	 */
	protected function saveCjResultError($seq, $order_seq, $resp_data) {

	    //更新データ整形
		$new_param = array(
		        "CjrSeq" => $seq,
		        "OrderSeq" => $order_seq,
		        "ErrorCode" => $resp_data['error_code'],
		        "ErrorMsg" => $resp_data['error_message'],
		        "RegistId" => $this->getUserId(),
		        "UpdateId" => $this->getUserId(),
		        "ValidFlg" => 1,
		);

		$cjResult_error = new TableCjResultError($this->_adapter);
		//データベース格納
		$cjResult_error->saveNew($new_param);

	}

	/**
	 * 与信注文ID管理書き込み
	 *
	 * @access protected
	 * @param $order_seq 注文SEQ
	 * @param $rcv_data 受信データ
	 * @return int 結果値
	 */
	protected function saveCjOrderIdControl($order_seq, $rcv_data) {

	    //与信注文ID管理
	    $cjoc = new TableCjOrderIdControl($this->_adapter);

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
	            'RegistId' => $this->getUserId(),
	        );
	        $cjoc->saveNew($data);
	    }
	}

    /**
     * 名寄せリスト書き込み
     *
     * @access protected
     * @param $resp_data 受信データ
     */
    protected function saveCombinedList($resp_data) {

        //モデル
        $combiledList = new TableCombinedList($this->_adapter);
        $sequence = new SequenceGeneral($this->_adapter);
        $mngCust = new TableManagementCustomer($this->_adapter);

        $insCnt = 0;

        //名寄せ情報がある場合のみ
        if (!is_null($resp_data['aggregation_list'])) {
            //与信審査した注文の顧客ID
            $orderManCustId = $resp_data['aggregation_list']->customer_info->system_customer_id;
            $orderIluCustomerId = $resp_data['aggregation_list']->customer_info->customer_id;

            if (!empty($orderManCustId)) {
                // 統合先の管理顧客
                $mc = $mngCust->find($orderManCustId)->current();
                if ($mc === false) {
                    // 統合先の管理顧客が存在しない場合、名寄せデータは作成しない
                    return;
                }

                if ($mc != false && empty($mc['IluCustomerId']) && !empty($orderIluCustomerId)) {
                    // 管理顧客の審査システム－顧客ＩＤが未設定の場合、設定
                    $mngCust->saveUpdate(array(
                            'IluCustomerId' => $orderIluCustomerId,
                            'UpdateId' => $this->getUserId(),
                        )
                        , $orderManCustId
                    );
                }

                //複数ではない場合は直接フィールド名が入ってくるため整形
                if(is_null($resp_data['aggregation_list']->aggregation_customer_info[1])){
                    $aggregation_customer_info = new \stdClass();
                    $aggregation_customer_info->aggregation_customer_id = intval($resp_data['aggregation_list']->aggregation_customer_info->aggregation_customer_id);
                    $aggregation_customer_info->aggregation_level = intval($resp_data['aggregation_list']->aggregation_customer_info->aggregation_level);
                    $aggregation_array['aggregation_list']->aggregation_customer_info[0] = $aggregation_customer_info;

                }else{
                    $aggregation_array = $resp_data;
                }

                // 審査システムによる名寄せ候補
                $manCustIdList = array();   // 重複チェックリスト
                $manCustIdList[intval($orderManCustId)] = 1;
                foreach($aggregation_array['aggregation_list']->aggregation_customer_info as $key=>$param){
                    //条件が複数あるかによって構成が違うため配列キーが数値かどうか判定
                    $customer_id = intval($param->aggregation_customer_id);
                    $aggregation_level = $param->aggregation_level;

                    //審査システムの名寄せ候補顧客IDで、管理顧客から管理顧客番号を取得
                    $manCustId = $this->getManCustId($customer_id);

                    if ($manCustId > 0) {
                        //管理顧客が存在する場合のみ登録

                        // 親顧客や他の顧客と管理顧客番号が重複する場合スキップ
                        if (isset($manCustIdList[$manCustId])) {
                            continue;
                        }

                        // 登録対象を重複チェックリストに保存
                        $manCustIdList[$manCustId] = 1;

                        //1回目のみ
                        if ($insCnt == 0) {
                            // 与信審査した注文の顧客

                            //名寄せリストID取得
                            $combinedListId = $sequence->nextValue('CombinedListId');

                            //登録用情報
                            $new_data = array(
                                    "CombinedListId" => $combinedListId,
                                    "ManCustId" => $orderManCustId,
                            );

                            //登録データ整形
                            $new_param = array(
                                    'CombinedListId' => $new_data['CombinedListId'],
                                    'ManCustId' => $new_data['ManCustId'],
                                    'LikenessFlg' => 0,
                                    'CombinedDictateFlg' => 0,
                                    'CombinedDictateDate' => null,
                                    'CombinedDate' => null,
                                    'AggregationLevel' => null,
                                    'RegistId' => $this->getUserId(),
                                    'UpdateId' => $this->getUserId(),
                                    'ValidFlg' => 1,
                            );

                            $combiledList->saveNew($new_param);

                            $insCnt++;
                        }

                        //登録用情報
                        $new_data = array(
                                "CombinedListId" => $combinedListId,
                                "ManCustId" => $manCustId,
                        );

                        //登録データ整形
                        $new_param = array(
                                'CombinedListId' => $new_data['CombinedListId'],
                                'ManCustId' => $new_data['ManCustId'],
                                'LikenessFlg' => 1,
                                'CombinedDictateFlg' => 0,
                                'CombinedDictateDate' => null,
                                'CombinedDate' => null,
                                'AggregationLevel' => strlen($aggregation_level) > 0 ? $aggregation_level : null,
                                'RegistId' => $this->getUserId(),
                                'UpdateId' => $this->getUserId(),
                                'ValidFlg' => 1,
                        );

                        $combiledList->saveNew($new_param);

                        $insCnt++;
                    }
                }
            }
        }
    }

    /**
     * 管理顧客番号取得
     * @access protected
     * @param int $iluCustomerId 審査システム－顧客ＩＤ
     * @return int 管理顧客番号
     */
    protected function getManCustId($iluCustomerId) {

        //管理顧客番号取得SQL作成
        $q = <<<EOQ
SELECT ManCustId
FROM T_ManagementCustomer
WHERE %s
EOQ;
        $query = sprintf($q, " ValidFlg = 1 AND IluCustomerId = :IluCustomerId ");

        //管理顧客番号取得
        $ri = $this->_adapter->query($query)->execute(array(':IluCustomerId' => $iluCustomerId));

        //取得できなかった場合、-1を返す
        if (!($ri->count() > 0)) {
            return -1;
        }
        $customer_data = $ri->current();

        return intval($customer_data['ManCustId']);
    }

    /**
     * 与信審査結果の重みづけを行う
     *
     * @access protected
     * @param int $seq
     * @param int $creditCriterionId
     */
    protected function updateScoreWeighting($seq, $creditCriterionId, $oseq) {
        $cjResult = new TableCjResult($this->_adapter);
        $cjResult_detail = new TableCjResultDetail($this->_adapter);
        $creditPoint = new TableCreditPoint($this->_adapter);
        $mdls = new TableSite($this->_adapter);
        $mdlo = new TableOrder($this->_adapter);

        $order = $mdlo->find($oseq)->current();
        $site = $mdls->findSite($order['SiteId'])->current();
        $multiOrderCount = (int)$site['MultiOrderCount'];

        // 今回登録した与信審査結果詳細を取得
        $ri = $cjResult_detail->findCjResult(array('CjrSeq' => $seq, 'ValidFlg' => 1), true);
        $ar = ResultInterfaceToArray($ri);

        // 審査システム重みづけ
        $cpRow = $creditPoint->findCreditPoint($creditCriterionId, 501)->current();
        $cpList = Json::decode( (isset($cpRow['Description']) ? $cpRow['Description'] : '[]'), Json::TYPE_ARRAY);

        $totalScoreWeighting = 0;
        foreach($ar as $detail) {
            // 与信審査結果検出パターンに該当する審査システム重みづけを取得
            $rate = 1;
            foreach ($cpList as $rateRow) {
                if (intval($detail['DetectionPatternNo']) == intval($rateRow['Key'])) {
                    $rate = floatval($rateRow['Value']);
                    break;
                }
            }

            // 連続注文設定が有効な場合、501(連続注文)は無効(0)とする
            if ( $multiOrderCount > 0 && intval($detail['DetectionPatternNo']) == 501 ) {
                $rate = 0;
            }

            // 請求金額０円でも注文受付するため、503(注文：商品代金無し)は無効(0)とする
            $execflg = false;
            $sql = ' SELECT e.CreditTransferFlg,e.AppFormIssueCond,o.UseAmount,ao.CreditTransferRequestFlg FROM T_Order o LEFT JOIN T_Enterprise e ON o.EnterpriseId=e.EnterpriseId LEFT JOIN AT_Order ao ON o.OrderSeq=ao.OrderSeq WHERE o.OrderSeq = :OrderSeq ';
            $ent = $this->_adapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();
            if ((($ent['CreditTransferFlg'] == 1) || ($ent['CreditTransferFlg'] == 2) || ($ent['CreditTransferFlg'] == 3)) && (($ent['AppFormIssueCond'] == 2) || ($ent['AppFormIssueCond'] == 0)) && ($ent['UseAmount'] == 0) && (($ent['CreditTransferRequestFlg'] == 1) || ($ent['CreditTransferRequestFlg'] == 2))) {
                $execflg = true;
            }
            if ($execflg) {
                if ( intval($detail['DetectionPatternNo']) == 503 ) {
                    $rate = 0;
                }
            }

            // 検出パターンスコア（重みづけ）を計算
            $detectionPatternScoreWeighting = $detail['DetectionPatternScore'] * $rate;

            // 更新データ整形
            $new_param = array(
                    "DetectionPatternScoreWeighting" => $detectionPatternScoreWeighting,
                    "UpdateId" => $this->getUserId(),
            );

            //与信審査結果詳細を更新
            $cjResult_detail->saveUpdate($new_param, $detail['Seq']);

            // スコアリング結果(重みづけ)に検出パターンスコア（重みづけ）を加算
            $totalScoreWeighting += $detectionPatternScoreWeighting;
        }

        // 与信審査結果を更新
        // 更新データ整形
        $new_param = array(
                "TotalScoreWeighting" => $totalScoreWeighting,
                "UpdateId" => $this->getUserId(),
        );

        $cjResult->saveUpdate($new_param, $seq);
    }

    /**
     * 連続注文の判定を行う
     *
     * @param int $oseq 注文SEQ
     * @param unknown $rcv_data 受信データ
     */
    protected function multiOrderProc($oseq, $rcv_data) {

        $mdls = new TableSite($this->_adapter);
        $mdlo = new TableOrder($this->_adapter);
        $mdlsys = new TableSystemProperty($this->_adapter);

        // -------------------------------------------------
        // サイト情報の取得
        // -------------------------------------------------
        $order = $mdlo->find($oseq)->current();
        $site = $mdls->findSite($order['SiteId'])->current();
        $multiOrderCount = (int)$site['MultiOrderCount'];
        $multiOrderScore = (int)$site['MultiOrderScore'];


        // -------------------------------------------------
        // 設定が無効な場合は処理終了
        // -------------------------------------------------
        if ( $multiOrderCount <= 0 ) {
            return;
        }

        // -------------------------------------------------
        // 審査システムの顧客IDを取得
        // -------------------------------------------------
        $customerid = array();
        $customerid[] = -1; // SQL構築時にエラーにならない為の保護

        // 顧客情報/顧客ID
        $customerid[] = $rcv_data['aggregation_list']->customer_info->customer_id;

        //受け取ったcustomer_idが無効な場合は処理終了
        if(intval($rcv_data['aggregation_list']->customer_info->customer_id) === 0){
            return;
        }

        // 名寄せ候補顧客情報/名寄せ候補顧客
        if(is_null($rcv_data['aggregation_list']->aggregation_customer_info[1])){
            $aggregation_customer_info = new \stdClass();
            $aggregation_customer_info->aggregation_customer_id = intval($rcv_data['aggregation_list']->aggregation_customer_info->aggregation_customer_id);
            $aggregation_array['aggregation_list']->aggregation_customer_info[0] = $aggregation_customer_info;

        }else{
            $aggregation_array = $rcv_data;
        }
        foreach($aggregation_array['aggregation_list']->aggregation_customer_info as $key => $param){
            $work = intval($param->aggregation_customer_id);
            if ($work == 0) {
                // nullや空白を後続の処理から除外する（名寄せ先のliu顧客ID=0も基本ないので）
                $customerid[] = -1;
            } else {
                //条件が複数あるかによって構成が違うため配列キーが数値かどうか判定
                $customerid[] = intval($param->aggregation_customer_id);
            }
        }

        // -------------------------------------------------
        // 注文件数のカウント
        // -------------------------------------------------
        // 連続注文期間設定を取得
        $multiOrderDays = (int)$mdlsys->getValue(TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'MultiOrderDays');
        $multiOrderDays = $multiOrderDays * -1;
        $dateFrom = date('Y-m-d' , strtotime($multiOrderDays . ' day') );

        // 連続注文期間内（3日前 0:00 ～現在）の注文数
        $sql = <<<EOQ
SELECT COUNT(o.OrderSeq) AS CNT
  FROM T_Order o
       INNER JOIN T_Customer c
               ON o.OrderSeq = c.OrderSeq
       INNER JOIN T_EnterpriseCustomer ec
               ON c.EntCustSeq = ec.EntCustSeq
       INNER JOIN T_ManagementCustomer mc
               ON ec.ManCustId = mc.ManCustId
       INNER JOIN T_Site s
               ON o.SiteId = s.SiteId
  WHERE mc.IluCustomerId IN (%s)
    AND DATE(o.RegistDate) >= :RegistDate
    AND s.ChatBotFlg = 0
EOQ;
        $query = sprintf($sql, implode(',', $customerid));

        //管理顧客番号取得
        $ri = $this->_adapter->query($query)->execute(array(':RegistDate' => $dateFrom));
        $count = $ri->current()['CNT'];

        // -------------------------------------------------
        // 注文件数とサイト設定を確認
        // -------------------------------------------------
        if ( $count >= $multiOrderCount) {
            // 設定したグローバル変数は LogicCreditJudgeModuleCoralExtra->judge() にて使用する
            $GLOBALS['MultiOrderScore'] = $multiOrderScore;

            // Incre_Noteに値を設定
            $udata['Incre_Note'] = $order['Incre_Note'] . "サイト別連続注文:$multiOrderScore\n----\n";
            $udata['UpdateId'] = $this->getUserId();
            $mdlo->saveUpdate($udata, $oseq);

            $GLOBALS['CreditLog']['Jud_MultiOrderScore'] = $multiOrderScore;
            $GLOBALS['CreditLog']['Jud_MultiOrderYN'] = 1;
        }

    }
}
