<?php
namespace api\classes\Service;

use api\Application;
use api\classes\Service\ServiceAbstract;
use api\classes\Service\Cancel\ServiceCancelConst;
use api\classes\Service\Response\ServiceResponseCancel;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use models\Logic\LogicCancel;
use models\Logic\OrderCancelException;
use models\Table\TableUser;
use Coral\Coral\Mail\CoralMail;

/**
 * 注文キャンセルサービスクラス
 */
class ServiceCancel extends ServiceAbstract {
	/**
	 * 注文キャンセルAPIのサービスID
	 * @var string
	 */
	protected $_serviceId = "05";

	/**
	 * 要求内容リスト
	 *
	 * @access protected
	 * @var array
	 */
	protected $_reqDataList = array();

	/**
	 * 初期化処理
	 *
	 * @access protected
	 */
	protected function init() {
		// サイトIDチェックは行わない
		$this->_checkSiteId = false;

		// レスポンスを初期化
		$this->_response = new ServiceResponseCancel();

		// 認証用
		$this->_apiUserId		= $this->_data[ServiceCancelConst::API_USER_ID];
		$this->_enterpriseId	= $this->_data[ServiceCancelConst::ENTERPRISE_ID];

		// 要求データリスト
		$this->_reqDataList		= $this->_data[ServiceCancelConst::REQUEST_DATA];

		// ログ出力
		Application::getInstance()->logger->info(
			get_class($this) . '#init() ' .
			join(', ', array(
				sprintf('%s: %s', ServiceCancelConst::ENTERPRISE_ID, $this->_enterpriseId),
				sprintf('%s: %s', ServiceCancelConst::API_USER_ID, $this->_apiUserId),
				sprintf('RemoteAddr: %s', f_get_client_address())      // 2015/09/23 Y.Suzuki Mod f_get_client_address をｺｰﾙするように変更
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

		// このサービスでは単独入力の検証は行わない

		return $result;
	}

	/**
	 * サービスを実行する
	 *
	 * @access protected
	 * @return boolean サービス実行結果
	 */
	protected function exec() {
		$result = false;
		// 要求データリストが0件の場合は問い合わせるまでもなくエラー
		if(!is_array($this->_reqDataList) || empty($this->_reqDataList)) {
            $this->_response->addMessage(sprintf('E%s201', $this->_serviceId), '注文IDの指定は必須です');
			return $result;
		}

		// 要求データリストから注文IDリストを生成
		$orderIds = array();
		foreach($this->_reqDataList as $reqData) {
			$orderIds[] = $reqData[ServiceCancelConst::ORDER_ID];
		}

		$db = $this->_db;

		// 要求注文IDリストに対応するT_Order行を取得 → OrderIdをキーとした連想配列に詰め替える
		$orderMap = array();
// 		$wheres = array(
// 			$db->quoteInto('EnterpriseId = ?', $this->_enterpriseId),
// 			$db->quoteInto('OrderId IN (?)', $orderIds)
// 		);

// 		$query = sprintf('SELECT * FROM T_Order WHERE %s', join(' AND ', $wheres));

		$aryoid = array();
		foreach($orderIds as $oid) {
		    $aryoid[] = $this->_db->getPlatform()->quoteValue($oid);
		}
		$odr = implode(' ,', $aryoid);
		$sql = " SELECT * FROM T_Order WHERE EnterpriseId = :EnterpriseId AND OrderId IN ($odr)";

		$prm = array(
		        ':EnterpriseId' => $this->_enterpriseId,
		);
		$stm = $db->query($sql);
		$rs = new ResultSet();
		$ri = $stm->execute($prm);
		$sites = $rs->initialize($ri)->toArray();
		Application::getInstance()->logger->debug(sprintf('query: %s', $sites));

		foreach($sites as $orderRow) {
			$orderId = $orderRow[ServiceCancelConst::ORDER_ID];

			if(isset($orderMap[$orderId]) && is_array($orderMap[$orderId])) {
				$orderMap[$orderId][] = $orderRow;
			} else {
				$orderMap[$orderId] = array($orderRow);
			}
		}

		// APIユーザーID
		$mdluser = new TableUser($this->_db);
		$opId = $mdluser->getUserId(3, $this->_apiUserId);

		/** @var Logic_Cancel */
		$logic = new LogicCancel($this->_db);
		$mail = new CoralMail($this->_db, Application::getInstance()->smtpServer);
		$rowCount = 0;
		$cantCancelCount = 0;
		foreach($this->_reqDataList as $reqData) {
			$orderId = $reqData[ServiceCancelConst::ORDER_ID];
			$reason = mb_substr(trim($reqData[ServiceCancelConst::CANCEL_REASON]), 0, 240);
			$reasonForRegister = sprintf("%s%s", ServiceCancelConst::CANCEL_REASON_PREFIX, $reason);
			if(isset($orderMap[$reqData[ServiceCancelConst::ORDER_ID]])) {
				$orderRows = $orderMap[$orderId];
				$orderRow = $orderRows[0];
				$oseq = $orderRow['OrderSeq'];
				
				// 口座振替0円請求に対するキャンセル不可 処理
				$sql  = "SELECT e.AppFormIssueCond,ato.CreditTransferRequestFlg,";
				$sql .= "       o.DataStatus,o.CloseReason,o.UseAmount ";
				$sql .= "FROM T_Order o ";
				$sql .= "INNER JOIN T_Enterprise e ON e.EnterpriseId = o.EnterpriseId ";
				$sql .= "INNER JOIN AT_Order ato ON ato.OrderSeq = o.OrderSeq ";
				$sql .= "WHERE o.OrderSeq = :OrderSeq ";
				$prm = array(':OrderSeq' => $oseq,);
				$data = $db->query($sql)->execute($prm)->current();
				if($data['AppFormIssueCond'] == 2
				&& $data['CreditTransferRequestFlg'] != 0
				&& $data['DataStatus'] == 91
				&& $data['CloseReason'] == 1
				&& $data['UseAmount'] == 0) {
					//エラーの場合
					// キャンセル不可なのでエラーとして追加
					$newReason = "この注文は、他の端末からキャンセル申請されているか、または、キャンセル不可の注文です。";
					$this->_response->addResult($orderId, $newReason, $orderRows, true);
					$cantCancelCount++;
					// ログ出力
					Application::getInstance()->logger->info(
						sprintf('%s#exec() - OrderCancelException OrderId = %s, error = %s', get_class($this), $orderId, $newReason)
					);
				} else {
					try {
        	            //add $isToDo
    	                $isToDo = 0;
	                    if ($logic->_usedTodo2Pay($oseq) == true) {
                    	    $isToDo = 1;
                	    }
						$r = $logic->applies($oseq, $reasonForRegister, 0, 0, true, $opId, $isToDo, Application::getInstance()->sbpsLogger);
        	            if (strlen( $r ) != 0) {
    	                    if (strpos($r, "__sbps") !== false) {
	                            $temp = explode('__sbps', $r);
                            	$msg = $logic->_SBPaymentMakeErrorInfoForAjax($temp[0]);
                        	    $this->_response->addResult($orderId, $reason, $orderRows, true, true, $temp[0], $msg);
                    	    } else {
                	            $msg = $r;
            	                $this->_response->addResult($orderId, $reason, $orderRows, true);
        	                }
    	                    Application::getInstance()->logger->info(
	                            sprintf('%s#exec() - OrderCancelException OrderId = %s, errorCode = %s, error = %s', get_class($this), $orderId, $r, $msg) );
                    	} else {
                	        if ( $isToDo == 1) {
            	                // キャンセル確定メール送信
        	                    $mail->SendCancelMail($oseq, $opId);
    	                    }
	                        $this->_response->addResult($orderId, $reason, $orderRows);
                	    }
						// 成功したのでカウント
						$rowCount++;
					} catch(OrderCancelException $cancelError) {
						// キャンセル不可なのでエラーとして追加
						$this->_response->addResult($orderId, $reason, $orderRows, true);
						$cantCancelCount++;
// ログ出力
Application::getInstance()->logger->info(
	sprintf('%s#exec() - OrderCancelException OrderId = %s, error = %s', get_class($this), $orderId, $cancelError->getMessage()) );
					} catch(\Exception $err) {
						// キャンセル不可以外のエラーなのでデータなしでその他エラーとして追加
						$this->_response->addResult($orderId, $reason, null, true);
// ログ出力
Application::getInstance()->logger->info(
	sprintf('%s#exec() - Exception OrderId = %s, error = %s', get_class($this), $orderId, $err->getMessage()) );
					}
				}
			} else {
				// データなしとして追加
				$this->_response->addResult($orderId, $reason, null);
			}
		}
		if($rowCount) {
			// 1件でも要求が成功すればOK
			$result = true;
		} else {
			// 成功0件の場合、キャンセル不可が1件でもあれば301そうでなければ202とする
			$result = false;
			if($cantCancelCount) {
				$this->_response->addMessage(sprintf('E%s301', $this->_serviceId), 'キャンセル可能な注文がありませんでした');
			} else {
				$this->_response->addMessage(sprintf('E%s202', $this->_serviceId), '有効な注文IDが指定されていません');
			}
		}
		return $result;
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

}
