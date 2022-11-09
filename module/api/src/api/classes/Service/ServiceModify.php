<?php
namespace api\classes\Service;

use api\Application;
use api\classes\Service\ServiceAbstract;
use api\classes\Service\Modify\ServiceModifyConst;
use api\classes\Service\Response\ServiceResponseModify;
use models\Table\TableOrder;
use models\Table\TableCustomer;
use models\Table\TableDeliveryDestination;
use models\Table\TableOrderItems;
use models\Table\TableOrderSummary;
use models\Table\TableUser;

/**
 * 注文修正サービスクラス
 */
class ServiceModify extends ServiceAbstract {
	/**
	 * 注文修正APIのサービスID
	 * @var string
	 */
	protected $_serviceId = "06";

	/**
	 * 注文ID
	 *
	 * @var string
	 */
	public $orderId;

	/**
	 * 修正データ
	 *
	 * @var array
	 */
	public $params;

	/**
	 * 初期化処理
	 *
	 * @access protected
	 */
	protected function init() {
		// サイトIDチェックは行わない
		$this->_checkSiteId = false;

		// レスポンスを初期化
        $this->_response = new ServiceResponseModify();

		// 認証用
        $this->_apiUserId = $this->_data[ServiceModifyConst::API_USER_ID];
        $this->_enterpriseId = $this->_data[ServiceModifyConst::ENTERPRISE_ID];

		// 登録向けデータ
        $this->orderId = $this->_data[ServiceModifyConst::ORDER_ID];
		$this->params = $this->_data[ServiceModifyConst::REQ_PARAMS];

		// 登録データをレスポンスへ反映
		$this->_response->orderId = $this->orderId;

		// ログ出力
		Application::getInstance()->logger->info(
			get_class($this) . '#init() ' .
			join(', ', array(
				sprintf('%s: %s', ServiceModifyConst::ENTERPRISE_ID, $this->_enterpriseId),
				sprintf('%s: %s', ServiceModifyConst::API_USER_ID, $this->_apiUserId),
				sprintf('RemoteAddr: %s', f_get_client_address())       // 2015/09/23 Y.Suzuki Mod f_get_client_address をｺｰﾙするように変更
			)) );
	}

	/**
	 * リクエストパラメータのうち、注文情報のパラメータを取得する
	 *
	 * @return array
	 */
	public function getOrderParams() {
		return $this->params[ServiceModifyConst::GROUP_ORDER];
	}

	/**
	 * リクエストパラメータのうち、購入者情報のパラメータを取得する
	 *
	 * @return array
	 */
	public function getCustomerParams() {
		return $this->params[ServiceModifyConst::GROUP_CUSTOMER];
	}

	/**
	 * リクエストパラメータのうち、配送先情報のパラメータを取得する
	 *
	 * @return array
	 */
	public function getDestinationParams() {
		return $this->params[ServiceModifyConst::GROUP_DESTINATION];
	}

	/**
	 * リクエストパラメータのうち、配送伝票情報のパラメータを取得する
	 *
	 * @return array
	 */
	public function getJournalParams() {
		return $this->params[ServiceModifyConst::GROUP_JOURNAL];
	}

	/**
	 * リクエストパラメータのうち、商品明細情報のパラメータを取得する
	 *
	 * @return array
	 */
	public function getItemsParams() {
		return $this->params[ServiceModifyConst::GROUP_ITEMS];
	}

	/**
	 * 入力に対する検証を行う
	 *
	 * @access protected
	 * @return boolean 検証結果
	 */
	protected function check() {
		// 注文IDのnullチェックと対象注文の存在確認
		$orderData = $this->getSpecifiedOrder();
		if($orderData == null) {
			// IDがnullか注文が見つからない場合は検証エラー
			return false;
		}

		// 修正内容のチェック
		if(!$this->validateParams()) {
			return false;
		}

		// 論理的整合性チェック
		if(!$this->checkLogicalConsistency($orderData)) {
			return false;
		}

		return true;
	}

	/**
	 * 要求された注文情報を取得する
	 *
	 * @access protected
	 * @return Zend_Db_Table_Row_Abstract 対象注文の行データ
	 */
	protected function getSpecifiedOrder() {
		try {
			if($this->orderId === null || strlen(trim($this->orderId)) == 0) {
				throw new ServiceException(
					'注文ID : データを0または空にすることはできません', $this->_serviceId, '201' );
			}
			return $this->getOrderData($this->orderId);
		} catch(ServiceException $svcErr) {
			$this->_response->addMessage(
				$svcErr->getFormattedErrorCode(),
				$svcErr->getMessage() );

			return null;
		}

	}

	/**
	 * 要求パラメータの検証を実行する
	 *
	 * @access protected
	 * @return bool すべてのパラメータに問題がない場合はtrue、それ以外はfalse
	 */
	protected function validateParams() {
		$orderParams = $this->getOrderParams();
		$custParams = $this->getCustomerParams();
		$destParams = $this->getDestinationParams();
		$journalParams = $this->getJournalParams();
		$itemsParams = $this->getItemsParams();

		$is_valid = true;

		// 任意注文番号
		if(isset($orderParams['Ent_OrderId'])) {
			if(mb_strlen($orderParams['Ent_OrderId']) > 100) {
				$is_valid = false;
				$this->_response->addMessage(
					sprintf('E%s202', $this->_serviceId),
					'任意注文番号 : データサイズが不正です' );
			}
		}

		return $is_valid;
	}

	/**
	 * 要求パラメータの論理的整合性をチェックする
	 *
	 * @access protected
	 * @param Zend_Db_Table_Row_Abstract $orderData 処理対象の注文データ
	 * @return bool 論理的不整合がない場合はtrue、それ以外はfalse
	 */
	protected function checkLogicalConsistency($orderData) {
		$orderParams = $this->getOrderParams();
		$custParams = $this->getCustomerParams();
		$destParams = $this->getDestinationParams();
		$journalParams = $this->getJournalParams();
		$itemsParams = $this->getItemsParams();

		try {
			// パラメータ指定数のチェック → 1つでも修正指定があればOK
		    $orderParamsCount = 0;
		    if (!empty($orderParams)) {
		        $orderParamsCount = count(array_keys($orderParams));
		    }
		    $custParamsCount = 0;
		    if (!empty($custParams)) {
		        $custParamsCount = count(array_keys($custParams));
		    }
		    $destParamsCount = 0;
		    if (!empty($destParams)) {
		        $destParamsCount = count(array_keys($destParams));
		    }
		    $journalParamsCount = 0;
		    if (!empty($journalParams)) {
		        $journalParamsCount = count(array_keys($journalParams));
		    }
		    $itemsParamsCount = 0;
		    if (!empty($itemsParams)) {
		        $itemsParamsCount = count($itemsParams);
		    }
			$param_count =
				$orderParamsCount +
				$custParamsCount +
				$destParamsCount +
				$journalParamsCount +
				$itemsParamsCount;
			if(!$param_count) {
				throw new ServiceException(
					'有効な修正データが指定されていません', $this->_serviceId, '303' );
			}

			// サイトID変更指定時のメールアドレス必須チェック
			// TODO: 正式版で実装すべし

			// 商品明細の整合性チェック
			// TODO: 正式版で実装すべし
			// ・商品名・単価・数量がセットになっているか
			// ・商品明細が指定されている場合に利用額も指定されているか
			// ・商品明細の合計金額と利用額が一致しているか

			// DataStatusと修正内容の整合性をチェック
			// TODO: 正式版で実装すべし

		} catch(ServiceException $svcErr) {
			$this->_response->addMessage(
				$svcErr->getFormattedErrorCode(),
				$svcErr->getMessage() );

			return false;
		}

		return true;
	}

	/**
	 * サービスを実行する
	 *
	 * @access protected
	 * @return boolean サービス実行結果
	 */
	protected function exec() {
		$orderParams = $this->getOrderParams();
		$custParams = $this->getCustomerParams();
		$destParams = $this->getDestinationParams();
		$journalParams = $this->getJournalParams();
		$itemsParams = $this->getItemsParams();

		$this->_db->getDriver()->getConnection()->beginTransaction();

		try {
		    // APIユーザーID
		    $mdluser = new TableUser($this->_db);
		    $opId = $mdluser->getUserId(3, $this->_apiUserId);

			$orderRow = $this->getOrderData($this->orderId);

			// 注文情報の反映 ------------------------------------
			$orderTable = new TableOrder($this->_db);
			$modData = array();
			// 任意注文番号
			if(isset($orderParams['Ent_OrderId'])) {
				$modData['Ent_OrderId'] = $orderParams['Ent_OrderId'];
				$modData['UpdateId'] = $opId;
			}
			$orderTable->saveUpdate($modData, $orderRow['OrderSeq']);

			// 購入者情報の反映 ------------------------------------
			$custTable = new TableCustomer($this->_db);
			$custRow = $custTable->findCustomer(array('OrderSeq' => $orderRow['OrderSeq']))->current();
			$modData = array();
			// TODO: 正式版で実装すべし

			// 配送先情報の反映 ------------------------------------
			$destTable = new TableDeliveryDestination($this->_db);
			$modData = array();
			// TODO: 正式版で実装すべし

			// 商品明細情報の反映 ------------------------------------
			$itemsTable = new TableOrderItems($this->_db);
			$modData = array();
			// TODO: 正式版で実装すべし

			// 事後整合性関連処理
			// TODO: 正式版で実装すべし
			// ・別配送先指定の状態によっては、配送先の自動更新を実装する必要あり

			// 注文サマリを更新
			$this->updateOrderSummary($orderRow['OrderSeq'], $opId);

			$this->_db->getDriver()->getConnection()->commit();
		} catch(Exception $err) {
			// 例外発生時はロールバックだけ行って上位に再スロー
			$this->_db->getDriver()->getConnection()->rollBack();
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

	/**
	 * 指定注文IDの注文データを取得する。
	 * 対象のデータが未キャンセルでクローズされていない場合はデータを返し
	 * それ以外は{@link ServiceException}がスローされる
	 *
	 * @access protected
	 * @param string $orderId 注文ID
	 * @return Zend_Db_Table_Rowset
	 */
	protected function getOrderData($orderId) {
		$sql = <<<EOQ
SELECT * FROM T_Order
WHERE  DataStatus <> 91
AND    Cnl_Status = 0
AND    OrderId = :OrderId
AND    EnterpriseId = :EnterpriseId
EOQ;
		$order = $this->_db->query($sql)->execute(array(':OrderId' => $orderId, ':EnterpriseId' => $this->_enterpriseId));

		$orderCount = 0;
		if (!empty($order)) {
		    $orderCount = count($order);
		}
		if($orderCount != 1) {
			throw new ServiceException(
				'指定の注文は登録されていないか修正可能な状態ではありません',
				$this->_serviceId, '301');
		}
		return $order->current();
	}

	/**
	 * 指定注文に関連する注文商品データを取得する
	 *
	 * @param int $orderSeq 注文SEQ
	 * @return Zend_Db_Table_Rowset
	 */
	protected function getOrderItems($orderSeq) {
		require_once 'Table/OrderItems.php';
		$table = new Table_OrderItems($this->_db);
		return $table->fetchAll($this->_db->quoteInto('OrderSeq = ?', $orderSeq));
	}

	/**
	 * 指定注文のサマリデータを更新する
	 *
	 * @param int $orderSeq 注文SEQ
	 * @return Service_Modify このインスタンス
	 */
	protected function updateOrderSummary($orderSeq, $opId) {

	    $tbl = new TableOrderSummary($this->_db);
		$tbl->updateSummary($orderSeq, $opId);

		return $this;
	}
}
