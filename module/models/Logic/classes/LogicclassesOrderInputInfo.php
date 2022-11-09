<?php
namespace models\Logic\classes;

/**
 * 注文情報の入力を既定するユーティリティクラス。
 * の定数部分のみ分離した定数ホルダクラス（コピー元：member/classes/OrderInputInfo.php）
 *
 */
class LogicclassesOrderInputInfo {
	/**
	 * T_Orderに関するフォームフィールドグループを指定するキー
	 *
	 * @var string
	 */
	const ARRKEY_ORDER = 'order';

	/**
	 * T_Customerに関するフォームフィールドグループを指定するキー
	 *
	 * @var string
	 */
	const ARRKEY_CUSTO = 'customer';

	/**
	 * T_DeliveryDestinationに関するフォームフィールドグループを指定するキー
	 */
	const ARRKEY_DELIV = 'delivery';

	/**
	 * T_OrderItemsに関するフォームフィールドグループを指定するキー
	 */
	const ARRKEY_ITEMS = 'order_items';

	const REGEXP_DATETIME = '/^\d{4}\/\d{2}\/\d{2}$/';

}
