<?php
namespace models\Logic\CreditJudge;

use Zend\Db\Adapter\Adapter;
use models\View\ViewOrderCustomer;
use models\View\ViewDelivery;
use models\Table\TableEnterprise;

/**
 * 与信処理関連で参照頻度が高いデータアクセスを効率よく行うための
 * キャッシュ付きデータアクセスユーティリティ
 */
class LogicCreditJudgeDbCache {
	// DBキャッシュ種別定数：ViewOrderCustomer
	const CACHE_KEY_ORDERCUSTOMER = 'order-customer';

	// DBキャッシュ種別定数：ViewDelivery
	const CACHE_KEY_ITEMDELI = 'item-deli';

	// DBキャッシュ種別定数：TableEnterprise
	const CACHE_KEY_ENTERPRISE = 'enterprise';

    /**
     * DBアダプタ
     *
     * @access protected
     * @var Adapter
     */
    protected $_adapter = null;

    /**
     * データキャッシュ
     *
     * @access protected
     * @var array
     */
    protected $_cache;

	/**
	 * LogicCreditJudgeDbCacheの新しいインスタンスを初期化する
	 *
	 * @param Adapter $adapter アダプタ
	 */
    public function __construct(Adapter $adapter) {
		$this
			->setAdapter($adapter)
			->prepareCache();
    }

	/**
	 * DBアダプタを取得する
	 *
	 * @return Adapter
	 */
    public function getAdapter() {
        return $this->_adapter;
    }
	/**
	 * DBアダプタを設定する
	 *
	 * @param Adapter $adapter アダプタ
	 * @return LogicCreditJudgeDbCache
	 */
    public function setAdapter(Adapter $adapter) {
        $this->_adapter = $adapter;
        return $this;
    }

	/**
	 * データキャッシュを初期化する
	 *
	 * @return LogicCreditJudgeDbCache
	 */
	public function prepareCache() {
		$this->_dbCache = array(
			self::CACHE_KEY_ORDERCUSTOMER => array(),
			self::CACHE_KEY_ITEMDELI => array(),
			self::CACHE_KEY_ENTERPRISE => array()
		);
        return $this;
	}

	/**
	 * ViewOrderCustomerを新規に開く
	 *
	 * @return ViewOrderCustomer
	 */
	public function openViewOrderCustomer() {
		return new ViewOrderCustomer($this->getAdapter());
	}

	/**
	 * ViewDeliveryを新規に開く
	 *
	 * @return ViewDelivery
	 */
	public function openViewDelivery() {
		return new ViewDelivery($this->getAdapter());
	}

	/**
	 * TableEnterpriseを新規に開く
	 *
	 * @return TableEnterprise
	 */
	public function openTableEnterprise() {
		return new TableEnterprise($this->getAdapter());
	}

	/**
	 * データキャッシュを経由してV_OrderCustomerから指定注文のデータを取得する
	 *
	 * @param int $oseq 注文SEQ
	 * @return ResultInterface V_OrderCustomerから取得した行データ
	 */
	public function fetchOrderCustomer($oseq) {
		// ViewOrderCustomer向けのバッファを参照しておく
		$cache = &$this->_dbCache[self::CACHE_KEY_ORDERCUSTOMER];

		// キャッシュにデータがない場合のみDBへアクセス
		if(!isset($cache[$oseq])) {
			$cache[$oseq] =
				$this->openViewOrderCustomer()->findOrderCustomerByOrderSeq($oseq);
		}
		// キャッシュの内容を返す
		return $cache[$oseq];
	}

	/**
	 * データキャッシュを経由してV_Deliveryから指定注文に関連付けられたデータを取得する
	 *
	 * @param int $oseq 注文SEQ
	 * @return ResultInterface V_Deliveryから取得した行セット
	 */
	public function fetchItemDelivery($oseq) {
		// ViewDelivery向けのバッファを参照しておく
		$cache = &$this->_dbCache[self::CACHE_KEY_ITEMDELI];

		// キャッシュにデータがない場合のみDBへアクセス
		if(!isset($cache[$oseq])) {
			$cache[$oseq] =
				ResultInterfaceToArray($this->openViewDelivery()->findByOrderSeq($oseq));
		}
		// キャッシュの内容を返す
		return $cache[$oseq];
	}

	/**
	 * データキャッシュを経由してT_Enterpriseから指定事業者のデータを取得する
	 *
	 * @param int $entId 事業者ID
	 * @return ResultInterface T_Enterpriseから取得した行データ
	 */
	public function fetchEnterprise($entId) {
		// TableEnterprise向けのバッファを参照しておく
		$cache = &$this->_dbCache[self::CACHE_KEY_ENTERPRISE];

		// キャッシュにデータがない場合のみDBへアクセス
		if(!isset($cache[$entId])) {
			$cache[$entId] =
				$this->openTableEnterprise()->findEnterprise($entId);
		}
		// キャッシュの内容を返す
		return $cache[$entId];
	}

}
