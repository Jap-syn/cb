<?php
namespace oemmember\classes;

use Coral\Base\Reflection\BaseReflectionUtility;

/**
 * 注文データを表すCSVからの配列とデータベースとのマッピングを定義するための
 * エンティティクラスで、OrderDataBuilder内で使用される。
 *
 */
class OrderColumnInfo {
	/**
	 * ラベルを示す設定キー
	 *
	 * @var string
	 */
	const KEY_LABEL = 'label';

	/**
	 * テーブル名を示す設定キー
	 *
	 * @var string
	 */
	const KEY_TABLE_NAME = 'table_name';

	/**
	 * カラム名を示す設定キー
	 *
	 * @var string
	 */
	const KEY_COLUMN_NAME = 'column_name';

	/**
	 * 検証情報を示す設定キー
	 *
	 * @var string
	 */
	const KEY_VALIDATOR = 'validator';

	/**
	 * フィルタ情報を示す設定キー
	 *
	 * @var string
	 */
	const KEY_FILTER = 'filter';

	/**
	 * すべての値を許可するデフォルトの検証情報
	 *
	 * @var string
	 */
	const VALIDATOR_DEFALUT = '/^.*$/m';

	/**
	 * ラベル
	 *
	 * @var string
	 */
	protected $_label;

	/**
	 * マッピングされるテーブルの名前
	 *
	 * @var string
	 */
	protected $_table;

	/**
	 * マッピングされるカラムの名前
	 *
	 * @var string
	 */
	protected $_column;

	/**
	 * 入力値の検証に使用するPerl互換正規表現
	 *
	 * @var string
	 */
	protected $_validator;

	/**
	 * 入力値に適用するフィルタコールバック
	 *
	 * @var string|array
	 */
	protected $_filter;

	/**
	 * OrderColumnInfoの新しいインスタンスを初期化する
	 *
	 * @param array $config 初期設定の連想配列
	 */
	public function __construct($config = array()) {
		$this->setValidator();

		foreach($config as $key => $value) {
			switch( $key ) {
				case self::KEY_LABEL:
					$this->setLabel($value);
					break;
				case self::KEY_TABLE_NAME:
					$this->setTableName($value);
					break;
				case self::KEY_COLUMN_NAME:
					$this->setColumnName($value);
					break;
				case self::KEY_VALIDATOR:
					$this->setValidator($value);
					break;
				case self::KEY_FILTER:
					$this->setFilter($value);
					break;
			}
		}
	}

	/**
	 * ラベルを取得する
	 *
	 * @return string
	 */
	public function getLabel() {
		return $this->_label;
	}

	/**
	 * ラベルを設定する
	 *
	 * @param string $label
	 * @return OrderColumnInfo
	 */
	public function setLabel($label) {
		$this->_label = $label;

		return $this;
	}

	/**
	 * マッピングされるデータベースのテーブル名を取得する
	 *
	 * @return string
	 */
	public function getTableName() {
		return $this->_table;
	}

	/**
	 * マッピングされるデータベースのテーブル名を設定する
	 *
	 * @param string $tableName
	 * @return OrderColumnInfo
	 */
	public function setTableName($tableName) {
		$this->_table = $tableName;

		return $this;
	}

	/**
	 * マッピングされるデータベースのカラム名を取得する
	 *
	 * @return string
	 */
	public function getColumnName() {
		return $this->_column;
	}

	/**
	 * マッピングされるデータベースのカラム名を設定する
	 *
	 * @param string $columnName
	 * @return OrderColumnInfo
	 */
	public function setColumnName($columnName) {
		$this->_column = $columnName;

		return $this;
	}

	/**
	 * 入力値を検証するPerl互換の正規表現文字列を取得する
	 *
	 * @return string
	 */
	public function getValidator() {
		return $this->_validator;
	}

	/**
	 * 入力値を検証するPerl互換の正規表現文字列を設定する
	 *
	 * @param string $validator
	 * @return OrderColumnInfo
	 */
	public function setValidator($validator) {
		if( empty($validator) ) $validator = self::VALIDATOR_DEFALUT;

		$this->_validator = $validator;

		return $this;
	}

	/**
	 * 入力値に適用するフィルタコールバックを取得する
	 *
	 * @return string|array
	 */
	public function getFilter() {
		return $this->_filter;
	}

	/**
	 * 入力値に適用するフィルタコールバックを設定する。
	 * $callbackはcall_user_func関数に使用できる適切な形式であるかを検査され、
	 * 適合しない場合は例外がスローされる
	 *
	 * @param string|array $filter フィルタコールバック。コールバック関数名かコールバックメソッド情報を指定する
	 * @return OrderColumnInfo
	 */
	public function setFilter($filter) {
		if( $filter != null ) {
			if( ! BaseReflectionUtility::isCallback($filter) ) {
				throw 'arguments error: invalid callback.';
			}
		}
		$this->_filter = $filter;

		return $this;
	}

	/**
	 * 現在の設定で入力値を検証する。
	 * 検証はpreg_matchによって行われるため、$valueは文字列にキャストされることに注意
	 *
	 * @param mixed $value 入力値
	 * @return bool
	 */
	public function validate($value) {
		$reg = $this->getValidator();
		if( empty($reg) ) $reg = self::VALIDATOR_DEFALUT;

		return preg_match( $reg, (string)$value ) ? true : false;
	}

	/**
	 * 現在の設定で入力値にフィルタを適用し、結果の値を返す
	 *
	 * @param mixed $value 入力値
	 * @return mixed
	 */
	public function applyFilter($value) {
		$filter = $this->getFilter();

		if( empty( $filter ) ) $filter = array( $this, 'nullFilter' );

		return call_user_func( $filter, $value );
	}

	/**
	 * フィルタが未設定の場合に適用されるnullフィルタ。入力値をそのまま返す。
	 *
	 * @param mixed $value 入力値
	 * @return mixed
	 */
	public function nullFilter($value) {
		return $value;
	}

}