<?php
namespace member\classes;

use Zend\Json\Json;

/**
 * 注文登録/配送伝票登録時の重複登録に関する設定
 */
class DuplicateRegistrationConfig {
	/**
	 * cookieに保存する際のキー
	 *
	 * @var string
	 */
	const COOKIE_KEY = 'member_dup_register_config';

	/**
	 * cookie有効期限（相対値）
	 *
	 * @access protected
	 * @var int
	 */
	protected $_expire;

	/**
	 * デフォルトの設定データ
	 *
	 * @access protected
	 * @var array
	 */
	protected $_default_config = array(
		'order' => false,
		'ordercsv' => false,
		'shipping' => false,
		'shippingcsv' => false
	);

	/**
	 * 設定データ
	 *
	 * @access protected
	 * @var array
	 */
	protected $_config = array();

	/**
	 * DuplicateRegistrationConfigの新しいインスタンスを初期化する
	 */
	public function __construct() {
		// cookieの有効期限は最終保存時から30日間
		$this->_expire = 60 * 60 * 24 * 30;

		// cookieからデータをロード
		$this->load();
	}

	/**
	 * cookieに保存されているデータを復元する
	 *
	 * @return DuplicateRegistractionConfig このインスタンス自身
	 */
	public function load() {
		try {
			// cookieから復元を試みる
			$value = nvl($_COOKIE[self::COOKIE_KEY], '');
			$config = Json::decode($value, Json::TYPE_ARRAY);

			if($this->checkKeys($config)) {
				// 正常に復元されたらデータ確定
				$this->_config = $config;
			} else {
				// 復元された値に問題があったらデフォルト値を採用
				$this->clear();
			}
		} catch(\Exception $err) {
			// 復元に問題があったらデフォルト値採用
			$this->clear();
		}
		return $this;
	}

	/**
	 * 現在の設定をcookieに保存する
	 *
	 * @return DuplicateRegistractionConfig このインスタンス自身
	 */
	public function store() {
		// 未設定のキーがあったら初期化
		if(!$this->checkKeys($this->_config)) {
			$this->clear();
		}

		// 不要キーの除去
		$config = array();
		foreach(array_keys($this->_default_config) as $key) {
			$config[$key] = $this->_config[$key] ? true : false;
		}
		$this->_config = $config;

		// cookieへ保存
		setcookie(self::COOKIE_KEY, Json::encode($this->_config), time() + $this->_expire, '/', '', 1);

		return $this;
	}

	/**
	 * すべての設定を初期化する
	 *
	 * @return DuplicateRegistractionConfig このインスタンス自身
	 */
	public function clear() {
		$this->_config = array_merge(array(), $this->_default_config);
		$this->store();
		return $this;
	}

	/**
	 * 現在の設定値すべてをJSON形式でエクスポートする
	 *
	 * @param null|boolean $use_array trueを指定した場合は配列を返す。デフォルトはfalse
	 * @return string|array
	 */
	public function export($use_array = false) {
		return $use_array ?
			array_merge(array(), $this->_config) :
			Json::encode($this->_config);
	}

	/**
	 * 個別注文登録向けの設定値を取得する
	 *
	 * @return boolean
	 */
	public function getOrderConfig() {
		return $this->order ? true : false;
	}
	/**
	 * 個別注文登録向けの設定値を更新する
	 *
	 * @param boolean $value trueが指定された場合は重複登録を許可する設定で更新、それ以外は禁止で更新
	 * @return DuplicateRegistractionConfig このインスタンス自身
	 */
	public function setOrderConfig($value = false) {
		$this->order = $value ? true : false;
		return $this;
	}

	/**
	 * 一括注文登録向けの設定値を取得する
	 *
	 * @return boolean
	 */
	public function getOrderCsvConfig() {
		return $this->ordercsv ? true : false;
	}
	/**
	 * 一括注文登録向けの設定値を更新する
	 *
	 * @param boolean $value trueが指定された場合は重複登録を許可する設定で更新、それ以外は禁止で更新
	 * @return DuplicateRegistractionConfig このインスタンス自身
	 */
	public function setOrderCsvConfig($value = false) {
		$this->ordercsv = $value ? true : false;
		return $this;
	}

	/**
	 * 個別伝票登録向けの設定値を取得する
	 *
	 * @return boolean
	 */
	public function getShippingConfig() {
		return $this->shipping ? true : false;
	}
	/**
	 * 個別伝票登録向けの設定値を更新する
	 *
	 * @param boolean $value trueが指定された場合は重複登録を許可する設定で更新、それ以外は禁止で更新
	 * @return DuplicateRegistractionConfig このインスタンス自身
	 */
	public function setShippingConfig($value = false) {
		$this->shipping = $value ? true : false;
		return $this;
	}

	/**
	 * 一括伝票登録向けの設定値を取得する
	 *
	 * @return boolean
	 */
	public function getShippingCsvConfig() {
		return $this->shippingcsv ? true : false;
	}
	/**
	 * 一括伝票登録向けの設定値を更新する
	 *
	 * @param boolean $value trueが指定された場合は重複登録を許可する設定で更新、それ以外は禁止で更新
	 * @return DuplicateRegistractionConfig このインスタンス自身
	 */
	public function setShippingCsvConfig($value = false) {
		$this->shippingcsv = $value ? true : false;
		return $this;
	}

	/**
	 * (overload)
	 */
	public function __get($key) {
		if($this->isValidKey($key)) {
			return ($this->_config[$key] ? true : false);
		} else {
			return null;
		}
	}

	/**
	 * (overload)
	 */
	public function __set($key, $value) {
		if($this->isValidKey($key)) {
			$this->_config[$key] = $value ? true : false;
			// 有効キーへ値が設定されるたびに保存する
			$this->store();
		}
	}

	/**
	 * (overload)
	 */
	public function __isset($key) {
		return $this->isValidKey($key) ? isset($this->_config[$key]) : false;
	}

	/**
	 * (overload)
	 */
	public function __unset($key) {
		if($this->isValidKey($key)) unset($this->_config[$key]);
	}

	/**
	 * 指定の配列に、必須キーがすべて含まれているかをチェックする
	 *
	 * @access protected
	 * @return boolean
	 */
	protected function checkKeys($config) {
		if(!is_array($config)) return false;
		foreach(array_keys($this->_default_config) as $key) {
			if(!isset($config[$key])) return false;
		}
		return true;
	}

	/**
	 * 指定のキーが適切なプロパティ名かを判断する
	 *
	 * @access protected
	 * @return boolean
	 */
	protected function isValidKey($key) {
		return in_array($key, array_keys($this->_default_config));
	}
}
