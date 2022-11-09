<?php
namespace Coral\Coral\Form;

/**
 * {@link CoralFormGroup}により{@link CoralFormItem}をグループ分けして管理するクラス
 */
class CoralFormManager {
	/**
	 * {@link searchItem}メソッドの種別でフォーム要素型での検索を指定する定数
	 *
	 * @var string
	 */
	const SEARCH_BY_TYPE = 'type';

	/**
	 * {@link searchItem}メソッドの種別でフォーム要素のname属性での検索を指定する定数
	 *
	 * @var string
	 */
	const SEARCH_BY_NAME = 'name';

	/**
	 * {@link searchItem}メソッドの種別でフォーム要素のid属性での検索を指定する定数
	 *
	 * @var string
	 */
	const SEARCH_BY_ID = 'id';

	/**
	 * {@link searchItem}メソッドの種別でカラムマッピング情報での検索を指定する定数
	 *
	 * @var string
	 */
	const SEARCH_BY_COLUMN = 'column_map';

	/**
	 * 注文フォームの表示グループ配列
	 *
	 * @var array
	 */
	protected $_groups;

	/**
	 * {@link CoralFormManager}の新しいインスタンスを初期化する
	 */
	public function __construct() {
		$this->_groups = array();
	}

	/**
	 * 現在登録済みのすべての{@link CoralFormGroup}を配列で取得する
	 *
	 * @return array
	 */
	public function getGroups() {
		return $this->_groups;
	}

	/**
	 * 指定のグループ名に関連付けられている{@link CoralFormGroup}を取得する
	 *
	 * @param string $groupName 取得する{@link CoralFormGroup}に関連付けられているグループ名
	 * @return CoralFormGroup
	 */
	public function getGroup($groupName) {
		return $this->_groups[ $groupName ];
	}

	/**
	 * 登録済みのすべての{@link CoralFormItem}を配列で取得する
	 *
	 * @return array
	 */
	public function getAllItems() {
		$result = array();
		foreach($this->_groups as $groupName => $group) {
			 $result = array_merge( $result, $group->getItems() );
		}
		return $result;
	}

	/**
	 * グループ名を指定して、{@link CoralFormGroup}をコレクションに追加する
	 *
	 * @param string $groupName 追加する{@link CoralFormGroup}に関連付けるグループ名
	 * @param CoralFormGroup $group 追加する{@link CoralFormGroup}
	 * @return CoralFormManager
	 */
	public function addGroup($groupName, CoralFormGroup $group) {
		$this->_groups[ $groupName ] = $group;

		return $this;
	}

	/**
	 * 新しい{@link CoralFormGroup}を作成し、コレクションに追加する
	 *
	 * @param string $groupName 作成する{@link CoralFormGroup}に関連付けるグループ名
	 * @param string $label 作成する{@link CoralFormGroup}の見出しラベル
	 * @param string $description 作成する{@link CoralFormGroup}に関連付ける説明文章
	 * @param array $items 作成する{@link CoralFormGroup}に設定する{@link CoralFormItem}の配列
	 * @return CoralFormManager
	 */
	public function createGroup($groupName, $label, $description, $items = array()) {
		$group = new CoralFormGroup( $label, $description, $items );
		return $this->addGroup( $groupName, $group );
	}

	/**
	 * 指定の{@link CoralFormGroup}に、{@link CoralFormItem}を追加する
	 *
	 * @param string $groupName 操作対象の{@link CoralFormGroup}に関連付けられたグループ名
	 * @param CoralFormItem $item {@link $groupName}で指定される{@link CoralFormGroup}に追加する{@link CoralFormItem}
	 * @return CoralFormManager
	 */
	public function addItem($groupName, CoralFormItem $item) {
		$group = $this->getGroup( $groupName );
		if( $group ) {
			$group->addItem( $item );
		}

		return $this;
	}

	/**
	 * 指定のグループ名に含まれる{@link CoralFormItem}の配列を取得する
	 *
	 * @param string $groupName グループ名
	 * @return array
	 */
	public function findItemByGroupName($groupName) {
		$group = $this->getGroup( $groupName );
		if( $group == null ) return array();

		return $group->getItems();
	}

	/**
	 * 指定の検索方法で、指定のパターンに一致するプロパティを持つ{@link CoralFormItem}を検索し、
	 * 結果の配列を返す
	 *
	 * @param string $pattern 検索パターン。検索タイプがSEARCH_BY_COLUMN以外の場合は文字列の一致で比較される
	 * @param string $searchType 検索方法。CoralFormManagerで定義されているSEARCH_BY_*定数を指定する
	 * @param array|null $groupNames 対象とするグループ名の配列。空の配列やnullを指定した場合はすべてのグループが対象になる
	 * @return array
	 */
	public function searchItem($pattern, $searchType, $groupNames = array()) {

		// グループリストの整形
		if( ! is_array( $groupNames ) ) {
			if( $groupNames == null ) {
				$groupNames = array();
			} else {
				$groupNames = array( (string)$groupNames );
			}
		}

		// 検索対象リストを構築
		$list = array();
		if( ! empty( $groupNames ) ) {
			foreach( $groupNames as $groupName ) {
				$list = array_marge( $this->findItemByGroupName($groupName) );
			}
		} else {
			$list = $this->getAllItems();
		}

		// 検索実施
		$result = array();
		switch( $searchType ) {
			case self::SEARCH_BY_TYPE:
				$pattern = strtolower( $pattern );
				foreach( $list as $item ) {
					if( strtolower( $item->getType() ) == $pattern ) $result[] = $item;
				}
				break;
			case self::SEARCH_BY_NAME:
				$pattern = strtolower( $pattern );
				foreach( $list as $item ) {
					if( strtolower( $item->getName() ) == $pattern ) $result[] = $item;
				}
				break;
			case self::SEARCH_BY_ID:
				$pattern = strtolower( $pattern );
				foreach( $list as $item ) {
					if( strtolower( $item->getId() ) == $pattern ) $result[] = $item;
				}
				break;
			case self::SEARCH_BY_COLUMN:
				foreach( $list as $item ) {
					if( preg_match( $pattern, $item->getColumnMap() ) ) $result[] = $item;
				}
				break;
		}

		return $result;
	}

	/**
	 * 指定の検索方法で、指定のパターンに一致するプロパティを持つ{@link CoralFormItem}を検索し、
	 * 最初に一致した{@link CoralFormItems}を返す。
	 *
	 * @param string $pattern 検索パターン。検索タイプがSEARCH_BY_COLUMN以外の場合は文字列の一致で比較される
	 * @param string $searchType 検索方法。CoralFormManagerで定義されているSEARCH_BY_*定数を指定する
	 * @param array|null $groupNames 対象とするグループ名の配列。空の配列やnullを指定した場合はすべてのグループが対象になる
	 * @return CoralFormItem
	 */
	public function getItem($pattern, $searchType, $groupName = array()) {
		$results = $this->searchItem($pattern, $searchType, $groupName);
		return $results[0];
	}
}