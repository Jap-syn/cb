<?php
namespace models\Logic\MergeOrder;

use Zend\Db\Adapter\Adapter;

/**
 * 注文状態を変更することで現在の状況によって請求取りまとめステータスを更新する
 *
 */
class LogicMergeOrderHelper {
   /**
	 * アダプタ
	 *
	 * @var Adapter
	 */
	private $_adapter = null;

	/**
	 * 現在の注文情報にかかわる情報(ResultInterface->current)
	 *
	 * @var array
	 */
	private $nowdata;

	/**
	 * コンストラクタ
	 *
	 * @param Adapter $adapter アダプタ
	 * @param int $orderseq 注文Seq
	 */
	public function __construct(Adapter $adapter, $orderseq)
	{
		$this->_adapter = $adapter;

		$query = "
			SELECT
			        ORD.OrderSeq
			        ,ORD.OutOfAmends
			        ,ORD.CombinedClaimTargetStatus
			        ,ITM.Deli_DeliveryMethod
			        ,ENT.CombinedClaimMode
			        ,SIT.CombinedClaimFlg
			    FROM
			        T_Order ORD INNER JOIN T_OrderItems ITM
			            ON(ITM.OrderSeq = ORD.OrderSeq ) INNER JOIN
			 		T_Enterprise ENT ON(ORD.Enterpriseid = ENT.EnterpriseId) INNER JOIN
			 		T_Site SIT ON(ORD.SiteId = SIT.SiteId)
			    WHERE
			    	ORD.OrderSeq = :OrderSeq
		";

		$this->nowdata = $this->_adapter->query($query)->execute(array(':OrderSeq' => $orderseq))->current();
	}

	/**
	 * 補償外案件の変更時に呼び出され、現在の状態と比較することによって請求取りまとめステータスを返却する
	 * @param $outofamends 変更対象の補償外案件状態
	 * @return 0:請求取りまとめをしない  1:事業者毎に取りまとめ 2:サイト毎に請求取りまとめ  9：変更しない
	 */
	public function chkCcTargetStatusByOutOfAmends($outofamends = null) {
		if($outofamends == 1) {
			// 補償外案件の場合には常に請求取りまとめをしない
			return 0;
		}
		else {
			if($this->nowdata['OutOfAmends'] == 1) {
				// 現在補償外案件
				return $this->chkCcTargetStatus();
			}
			else {
				// 現在補償外案件ではない
				return 9;
			}
		}
	}

	/**
	 * 配送方法の変更時に呼び出され、現在の状態と比較することによって請求取りまとめステータスを返却する
	 * @param $deliverymethod 変更対象の補償外案件状態
	 *
	 */
	public function chkCcTargetStatusByDelivery($deliverymethod) {
		if($deliverymethod == 13) {
			// メール便の場合には常に請求取りまとめをしない
			return 0;
		}
		else {
			if($this->nowdata['Deli_DeliveryMethod'] == 13) {
				// 現在メール便
				return $this->chkCcTargetStatus();
			}
			else {
				// 現在メール便ではない
				return 9;
			}
		}
	}

	/**
	 * 請求取りまとめモードによってステータスを決定する
	 *
	 */
	public function chkCcTargetStatus() {
		if($this->nowdata['CombinedClaimMode'] == 2 && $this->nowdata['CombinedClaimFlg'] == 1)  {
			// サイト毎
			return 2;
		}
		else if($this->nowdata['CombinedClaimMode'] == 1)  {
			// 事業者毎
			return 1;
		}
		else {
			return 0;
		}
	}

}
