<?php
namespace Coral\Coral\Form;

/**
 * {@link CoralFormItem}をグループ管理するためのクラス
 */
class CoralFormGroup {
	/**
	 * 項目グループの見出し文言
	 *
	 * @var string
	 */
	protected $_label;

	/**
	 * 項目グループの説明文
	 *
	 * @var string
	 */
	protected $_description;

	/**
	 * 項目設定配列
	 *
	 * @var array
	 */
	protected $_items;

	/**
	 * CoralFormGroupの新しいインスタンスを初期化する
	 *
	 * @param string|null $label
	 * @param string|null $description
	 * @param array|null $items
	 */
	public function __construct($label, $description, $items = array()) {
		$this->setLabel( $label )
			->setDescription( $description )
			->setItems( $items );
	}

	/**
	 * 項目グループの見出し文言を取得する
	 *
	 * @return string
	 */
	public function getLabel() {
		return $this->_label;
	}

	/**
	 * 項目グループの見出し文言を設定する
	 *
	 * @param string|null $label
	 * @return CoralFormGroup
	 */
	public function setLabel($label = null) {
		$label = (string)$label;

		if( $label == null ) $label = '';
		$this->_label = $label;

		return $this;
	}

	/**
	 * 項目グループの説明文を取得する
	 *
	 * @return string
	 */
	public function getDescription() {
		return $this->_description;
	}

	/**
	 * 項目グループの説明文を設定する
	 *
	 * @param string|null $description
	 * @return CoralFormGroup
	 */
	public function setDescription($description = null) {
		$description = (string)$description;

		if( $description == null ) $description = '';
		$this->_description = $description;

		return $this;
	}

	/**
	 * この項目グループに属するCoralFormItemの配列を取得する
	 *
	 * @return array
	 */
	public function getItems() {
		return $this->_items;
	}

	/**
	 * この項目グループに属するCoralFormItemの配列を設定する
	 *
	 * @param array|null $items
	 * @return CoralFormGroup
	 */
	public function setItems($items = array()) {
		$this->_items = array();
		return $this->addItems( $items );
	}

	/**
	 * 指定のインデックス位置にある{@link CoralFormItem}を取得する
	 *
	 * @param int $index
	 * @return CoralFormItem
	 */
	public function getItem($index) {
		return $this->_items[ $index ];
	}

	/**
	 * 指定の配列に格納された{@link CoralFormItem}をコレクションに追加する
	 *
	 * @param array $items
	 * @return CoralFormGroup
	 */
	public function addItems($items = array()) {
		if( ! is_array( $items ) ) {
			$items = array();
		}
		foreach($items as $item) {
			if( $item instanceof CoralFormItem ) $this->_items[] = $item;
		}

		return $this;
	}

	/**
	 * 指定の{@link CoralFormItem}をコレクションに追加する
	 *
	 * @param CoralFormItem $item
	 * @return CoralFormGroup
	 */
	public function addItem(CoralFormItem $item) {
		$this->_items[] = $item;

		return $this;
	}

	/**
	 * 指定の{@link CoralFormItem}をコレクションから除外する
	 *
	 * @param CoralFormItem $item
	 * @return CoralFormGroup
	 */
	public function removeItem(CoralFormItem $item) {
		$items = array();
		foreach($items as $oldItem) {
			if( $oldItem != $item ) $items[] = $oldItem;
		}
		$this->_items = $items;

		return $this;
	}

}