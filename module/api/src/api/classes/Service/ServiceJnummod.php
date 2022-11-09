<?php
namespace api\classes\Service;

use api\Application;
use api\classes\Service\Jnummod\ServiceJnummodConst;
use api\classes\Service\Response\ServiceResponseJnummod;
use models\Table\TableEnterprise;
use models\Logic\LogicDeliveryMethod;
use models\Table\TableDeliMethod;
use models\Table\TableOrderItems;
use models\Table\TableOrderSummary;
use models\Table\TableOrder;

/**
 * 伝票番号修正サービスクラス
 */
class ServiceJnummod extends ServiceAbstract {
    /**
     * 与信状況問い合わせAPIのサービスID
     * @var string
     */
    protected $_serviceId = "08";


     /**
     * 検証対象の入力パラメータとラベルのペアを格納したスキーマを取得する
     *
     * @static
     * @access protected
     * @return array
     */
    protected static function __getParamsSchema() {
        return array(
            array('key' => ServiceJnummodConst::ORDER_ID, 'label' => '注文ID'),
            array('key' => ServiceJnummodConst::JOURNAL_NUM, 'label' => '伝票番号')
        );
    }

	/**
	 * 初期化処理
	 *
	 * @access protected
	 */
	protected function init() {
		// サイトIDチェックは行わない
		$this->_checkSiteId = false;

		// レスポンスを初期化
        $this->_response = new ServiceResponseJnummod();

		// 認証用
		$this->_apiUserId    = $this->_data[ServiceJnummodConst::API_USER_ID];
		$this->_enterpriseId = $this->_data[ServiceJnummodConst::ENTERPRISE_ID];

		// 要求OrderId
		$this->_orderId = $this->_data[ServiceJnummodConst::ORDER_ID];
        // 要求配送会社ID
        $this->_delivId = trim($this->_data[ServiceJnummodConst::DELIV_ID]);
        // 要求伝票番号
        $this->_journalNum = $this->_data[ServiceJnummodConst::JOURNAL_NUM];

        //レスポンスの初期化

        //注文ID
        $this->_response->_orderId = $this->_orderId;
        //配送会社ID
        $this->_response->_delivId = $this->_delivId;
        //配送会社名
        $this->_response->_delivName = "";
        //伝票番号
        $this->_response->_journalNum = $this->_journalNum;

		// ログ出力
		Application::getInstance()->logger->info(
			get_class($this) . '#init() ' .
			join(', ', array(
				sprintf('%s: %s', ServiceJnummodConst::ENTERPRISE_ID, $this->_enterpriseId),
				sprintf('%s: %s', ServiceJnummodConst::API_USER_ID, $this->_apiUserId),
				sprintf('RemoteAddr: %s', f_get_client_address())       // 2015/09/23 Y.Suzuki Mod f_get_client_address をｺｰﾙするように変更
			)) );
	}

	/**
	 * 入力に対する検証を行う
	 *
	 * @access protected
	 * @return boolean 検証結果
	 */
	protected function check() {
		$result = true;

        try {
            //必須チェック 注文ID,伝票番号のみ
    		$this->checkRequireParams($this->_data);

            // 注文データの存在確認
            //DataStatus 41 51のみ
            $orderData = $this->getOrderData($this->_orderId);

            //配送会社が設定されていない場合注文情報から配送会社取得
            if( empty($this->_delivId) ){

                $orderItemData = $this->getOrderItems($orderData['OrderId']);

                //伝票番号取得
                $this->_delivId = $orderItemData['Deli_DeliveryMethod'];

            }

            //配送会社と伝票番号チェック
            $this->checkJournalNumber($this->_delivId,$this->_journalNum);

        } catch(ServiceException $svcErr) {
            // 検証エラー
            $this->_response->addMessage($svcErr->getFormattedErrorCode(),$svcErr->getMessage() );
        } catch(\Exception $err) {
            // その他のエラー
            Application::getInstance()->logger->info(sprintf('%s#check() ERROR: %s', get_class($this), $err->getMessage()));
            $this->errorHandle($err);
        }

		return !empty($this->_response->messages) ? false : true;
	}

	/**
	 * サービスを実行する
	 *
	 * @access protected
	 * @return boolean サービス実行結果
	 */
	protected function exec() {

        $db = $this->_db;
        $orderItemTable = new TableOrderItems($db);
        $orderSummaryTable = new TableOrderSummary($db);

        $db->getDriver()->getConnection()->beginTransaction();
        try {

            // APIユーザーID
            $mdluser = new \models\Table\TableUser($this->_db);
            $userid = $mdluser->getUserId(3, $this->_apiUserId);

            //パラメータの配送会社IDが指定されていない場合
            if(empty($this->_delivId)){

                //既に伝票番号登録されていてパラメータの伝票番号が省略されている場合は登録されている配送会社を利用する
                $orderItems = $this->getOrderItems($this->_orderId);

                //該当の配送会社も取得出来なかった場合はエラー
                if(!$orderItems){
                    throw new ServiceException('配送会社 : 入力データが不正です',$this->_serviceId, '202' );
                }

                //配送会社をthis->delivIdに代入
                $this->_delivId = $orderItems['Deli_DeliveryMethod'];
                $this->_response->_delivId = $this->_delivId;
            }

            //配送会社取得
            $this->_response->_delivName = $this->getDelivName($this->_delivId);

            // 対象の注文データを取得
            $orderData = $this->getOrderData($this->_orderId);

            $orderItemTable->updateJournal($orderData['OrderSeq'], $this->_delivId, $this->_journalNum, $userid, true);

            $orderSummaryTable->updateSummary($orderData['OrderSeq'], $userid);

            $db->getDriver()->getConnection()->commit();

        } catch(\Exception $err) {
            $db->getDriver()->getConnection()->rollBack();
            throw $err;
        }

        return true;
	}

	/**
	 * 処理結果を文字列として返却する
	 *
	 * @access protected
	 * @return string 処理結果
	 */
	protected function returnResponse() {

		return $this->_response->serialize();
	}

    /* 入力パラメータの必須検証を行う。
     * 検証エラーが発生した場合は{@link ServiceException}がスローされる
     *
     * @access protected
     * @return bool
     */
    protected function checkRequireParams(array $data) {

        $schema = self::__getParamsSchema();
        foreach($schema as $param) {
            $val = trim((string)$data[$param['key']]);
            if(!strlen($val)) {
                throw new ServiceException(sprintf('%s : データを0または空にすることはできません', $param['label']), $this->_serviceId, '201' );
            }
        }
        return true;
    }

    /**
     * 指定IDに一致する配送方法データを取得する。
     * 対象のマスターデータが存在しない場合は{@link ServiceException}がスローされる
     *
     * @access protected
     * @param int $delivId 配送方法ID
     * @return array | false
     */
    protected function getDelivMaster($delivId) {

        $enterprise = new TableEnterprise($this->_db);
        $row_ent = $enterprise->findEnterprise($this->_enterpriseId)->current();
        $delilogic = new LogicDeliveryMethod($this->_db);
        return $delilogic->getDeliMethod($row_ent['OemId'], $delivId)->current();
    }

    /**
     *  指定の配送会社名取得
     *
     * @access protected
     * @param int $delivId 配送方法ID
     * @return string
     */
    protected function getDelivName($delivId) {

        $deliMethod = new TableDeliMethod($this->_db);
        $row = $deliMethod->getValidDeliMethod($delivId)->current();
        if (!$row) {
            return "";
        }
        return $row['DeliMethodName'];
    }

     /**
     * 指定注文IDの注文データを取得する。
     * 対象のデータが未キャンセルで伝票番号登録待ちの場合のみデータを返し
     * それ以外は{@link ServiceException}がスローされる
     *
     * @access protected
     * @param string $orderId 注文ID
     * @return 注文情報
     */
    protected function getOrderData($orderId) {

        $sql = " SELECT * FROM T_Order WHERE OrderId = :OrderId AND EnterpriseId = :EnterpriseId AND DataStatus IN (41,51) AND Cnl_Status = 0 ";
        $ri = $this->_db->query($sql)->execute(array(':OrderId' => $orderId, ':EnterpriseId' => $this->_enterpriseId));
        if ($ri->count() != 1) {
            throw new ServiceException('指定の注文は登録されていないか伝票番号修正可能ではありません', $this->_serviceId, '301');
        }
        return $ri->current();
    }

    /**
     * 指定注文に関連する注文商品データを取得する
     *
     * @param string $orderId 注文ID
     * @return 注文情報
     */
    protected function getOrderItems($orderId) {

        $sql = " SELECT * FROM T_Order o INNER JOIN T_OrderItems oi ON (o.OrderSeq = oi.OrderSeq) WHERE o.OrderId = :OrderId AND DataClass = 1 LIMIT 0,1 ";
        return $this->_db->query($sql)->execute(array(':OrderId' => $orderId))->current();
    }

    /**
     * 指定の伝票番号を指定の配送方法のパターンで検証する。
     * 配送方法指定の不備や検証エラーが発生した場合は{@link ServiceException}がスローされる
     *
     * @access protected
     * @param mixed $delivId 配送方法ID
     * @param string $journalNumber 伝票番号
     * @return bool
     */
    protected function checkJournalNumber($delivId, $journalNumber) {

        $delivData = $this->getDelivMaster($delivId);
        if (!$delivData) {
            throw new ServiceException('配送会社 : 入力データが不正です', $this->_serviceId, '202' );
        }

        $reg = $delivData['ValidateRegex'];
        if(!strlen($reg)) $reg = '^.{1,255}$';

        mb_regex_encoding('UTF-8');
        if(strlen($journalNumber) > 255 || !mb_ereg($reg, $journalNumber)) {
            throw new ServiceException('伝票番号 : 入力データが不正です', $this->_serviceId, '202' );
        }

        return true;
    }
}