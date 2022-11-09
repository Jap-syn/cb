<?php
namespace Coral\Coral\Form;

/**
 * HTMLのフォーム要素とバックエンドのデータベースを1対1にマッピングして
 * 統合管理するためのクラス。どのHTMLフォーム要素を使用するかや表示時の見出し、
 * 値を検証するためのPerl互換正規表現などを関連付けて管理する
 */
class CoralFormItem {
	/**
	 * 要素タイプ input type="text"
	 *
	 * @var string
	 */
	const TYPE_TEXT = 'text';

	/**
	 * 要素タイプ input type="hidden"
	 *
	 * @var string
	 */
	const TYPE_HIDDEN = 'hidden';

	/**
	 * 要素タイプ input type="checkbox"
	 *
	 * @var string
	 */
	const TYPE_CHECKBOX = 'checkbox';

	/**
	 * 要素タイプ input type="radio"
	 *
	 * @var string
	 */
	const TYPE_RADIO = 'radio';

	/**
	 * 要素タイプ textarea
	 *
	 * @var string
	 */
	const TYPE_TEXTAREA = 'textarea';

	/**
	 * 要素タイプ select
	 *
	 * @var string
	 */
	const TYPE_SELECT = 'select';

	/**
	 * 要素タイプ button type="submit"
	 *
	 * @var string
	 */
	const TYPE_SUBMIT = 'submit';

	/**
	 * 要素タイプ button type="reset"
	 *
	 * @var string
	 */
	const TYPE_RESET = 'reset';

	/**
	 * 要素タイプ button type="button"
	 *
	 * @var string
	 */
	const TYPE_BUTTON = 'button';

	/**
	 * フォーム要素のタイプを指定するためのキー
	 *
	 * @var string
	 */
	const TYPE = 'type';

	/**
	 * フォーム要素のname属性を指定するためのキー
	 *
	 * @var string
	 */
	const NAME = 'name';

	/**
	 * フォーム要素のid属性を指定するためのキー
	 *
	 * @var string
	 */
	const ID = 'id';

	/**
	 * フォーム要素へマッピングするデータベースカラム情報を指定するためのキー
	 *
	 * @var string
	 */
	const COLUMN_MAP = 'column_map';

	/**
	 * フォーム要素に関連付けるキャプションを指定するためのキー
	 *
	 * @var string
	 */
	const CAPTION = 'caption';

	/**
	 * フォーム要素に関連付ける説明文章を指定するためのキー
	 *
	 * @var string
	 */
	const DESCRIPTION = 'description';

	/**
	 * フォーム要素の値を検証するためのPerl互換正規表現を指定するためのキー
	 *
	 * @var string
	 */
	const VALIDATION = 'validation';

	/**
	 * フォーム要素の属性リストを指定するためのキー
	 *
	 * @var string
	 */
	const ATTRIBUTES = 'attributes';

	/**
	 * フォーム要素がレンダリング時に使用するサブ要素のリストを指定するためのキー
	 * {@link #getType}が{@link ::TYPE_SELECT}の場合のみ有効
	 *
	 * @var string
	 */
	const OPTIONS = 'options';

	/**
	 * 指定のパラメータに基づいたフォーム要素のXHTMLソースを作成する
	 *
	 * @param mixed $value フォーム要素の値または表示文字列
	 * @param string $type フォーム要素のタイプ。CoralFormItemのTYPE_*定数に一致するもののみ有効
	 * @param string $name フォーム要素のname属性
	 * @param string $id フォーム要素のid属性
	 * @param array|null $attributes id, name以外の属性を示す連想配列
	 * @param array|null $options オプションの連想配列。$typeがCoralFormItem::TYPE_SELECTの場合にOPTION要素の生成に使用される
	 * @return 引数に基づいて作成されたフォーム要素を示すXHTMLソース文字列
	 */
	public static function createFieldSource($value, $type, $name, $id, $attributes = array(), $options = array()) {
		if( ! is_array( $attributes ) ) $attributes = array();
		$result = '';
		$attr = array();

		if( ! empty( $id ) ) {
            $attr[] = sprintf('id="%s"', f_e($id));
        }
		if( ! empty( $name ) ) {
            $attr[] = sprintf('name="%s"', f_e($name));
        }
		foreach($attributes as $key => $val) {
			if( preg_match( '/((id)|(name))$/i', $key ) ) continue;
            $attr[] = sprintf('%s="%s"', f_e($key), f_e($val));
		}
		$attr = count($attr) > 0 ?
			( ' ' . join( ' ', $attr ) ) : '';

		switch($type) {
			case self::TYPE_TEXT:
                $result = sprintf('<input type="text"%s value="%s" />', $attr, f_e($value));
				break;
			case self::TYPE_HIDDEN:
                $result = sprintf('<input type="hidden"%s value="%s" />', $attr, f_e($value));
				break;
			case self::TYPE_CHECKBOX:
                $result = sprintf('<input type="checkbox"%s value="%s" />', $attr, f_e($value));
                break;
			case self::TYPE_RADIO:
                $result = sprintf('<input type="radio"%s value="%s" />', $attr, f_e($value));
				break;
			case self::TYPE_TEXTAREA:
                $result = sprintf('<textarea%s>%s</textarea>', $attr, f_e($value));
				break;
			case self::TYPE_SELECT:
				$value = (string)$value;
				if( ! empty($value) ) {
                    $attr .= sprintf(' value="%s"', f_e($value));
                }
				if( ! is_array($options) ) {
					$options = array();
				}
				$options_list = array();
				foreach($options as $key => $val) {
                    $options_list[] = ( ($key == $value) ?
                        sprintf('<option value="%s" label="%s" selected="selected">%s</option>', f_e($key), f_e($val), f_e($val)) :
                        sprintf('<option value="%s" label="%s">%s</option>', f_e($key), f_e($val), f_e($val)) );
				}
				$options_list = join( "\n", $options_list );
                $result = sprintf("<select%s>\n%s\n</select>", $attr, $options_list);
				break;
			case self::TYPE_SUBMIT:
				if( empty($value) ) $value = "POST";
                $result = sprintf('<button type="submit"%s">%s</button>', $attr, f_e($value));
				break;
			case self::TYPE_RESET:
				if( empty($value) ) $value = "RESET";
                $result = sprintf('<button type="reset"%s>%s</button>', $attr, f_e($value));
				break;
			case self::TYPE_BUTTON:
				if( empty($value) ) $value = "BUTTON";
                $result = sprintf('<button type="button"%s>%s</button>', $attr, f_e($value));
				break;
		}

		return $result;
	}

	/**
	 * フォーム要素のキャプション文字列
	 *
	 * @var string
	 */
	protected $_caption;

	/**
	 * データベースのマッピング情報。<テーブル名>.<カラム名>形式として解釈される
	 *
	 * @var string
	 */
	protected $_column_map;

	/**
	 * フォーム要素のid属性
	 *
	 * @var string
	 */
	protected $_id;

	/**
	 * フォーム要素のname属性
	 *
	 * @var string
	 */
	protected $_name;

	/**
	 * フォーム要素タイプ。定数でTYPE_*として定義されている文字列を用いる
	 *
	 * @var string
	 */
	protected $_type;

	/**
	 * 入力検証用のPerl互換正規表現文字列
	 *
	 * @var string
	 */
	protected $_validation;

	/**
	 * フォーム要素の説明文章
	 *
	 * @var string
	 */
	protected $_description;

	/**
	 * フォーム要素に設定するHTML属性の連想配列
	 *
	 * @var array
	 */
	protected $_attributes = array();

	/**
	 * フォーム要素が使用するサブ要素の配列。{@link $_type}が{@link CoralFormItem::TYPE_SELECT}の
	 * 場合のみ使用される。
	 *
	 * @var array
	 */
	protected $_options = array();

	/**
	 * CoralFormItemの新しいインスタンスを初期化する
	 *
	 * @param array $config 初期設定の連想配列
	 */
	public function __construct(array $config = array()) {
		foreach( $config as $key => $value ) {
			switch($key) {
				case self::TYPE:
					$this->setType( $value );
					break;
				case self::NAME:
					$this->setName( $value );
					break;
				case self::ID:
					$this->setId( $value );
					break;
				case self::COLUMN_MAP:
					$this->setColumnMap( $value );
					break;
				case self::CAPTION:
					$this->setCaption( $value );
					break;
				case self::DESCRIPTION:
					$this->setDescription( $value );
					break;
				case self::VALIDATION:
					$this->setValidation( $value );
					break;
				case self::ATTRIBUTES:
					$this->setAttributes( $value );
					break;
				case self::OPTIONS:
					$this->setOptions( $value );
					break;
			}
		}

	}

	/**
	 * 指定の値を文字列として整理する
	 *
	 * @param mixed $value 整理する値
	 * @return string
	 */
	protected function getStringValue($value) {
		return empty( $value ) ? '' : (string)$value;
	}

	/**
	 * フォーム要素のタイプを取得する
	 *
	 * @return string
	 */
	public function getType() {
		return $this->_type;
	}

	/**
	 * フォーム要素のタイプを設定する。
	 * 設定可能な値はCoralFormItemでTYPE_*で定義されている定数に一致する値のみに制限される
	 *
	 * @param string $type フォーム要素タイプを示す文字列
	 * @return CoralFormItem
	 */
	public function setType($type) {
		switch($type) {
			case self::TYPE_BUTTON:
			case self::TYPE_CHECKBOX:
			case self::TYPE_HIDDEN:
			case self::TYPE_RADIO:
			case self::TYPE_RESET:
			case self::TYPE_SELECT:
			case self::TYPE_SUBMIT:
			case self::TYPE_TEXT:
			case self::TYPE_TEXTAREA:
				$this->_type = $type;
				break;
		}

		return $this;
	}

	/**
	 * フォーム要素に割り当てるname属性を取得する
	 *
	 * @return string
	 */
	public function getName() {
		return $this->_name;
	}

	/**
	 * フォーム要素に割り当てるname属性を設定する
	 *
	 * @param string $name フォーム要素に割り当てるname属性値
	 * @return CoralFormItem
	 */
	public function setName($name) {
		$this->_name = $this->getStringValue($name);

		return $this;
	}

	/**
	 * フォーム要素に割り当てるid属性を取得する
	 *
	 * @return string
	 */
	public function getId() {
		return $this->_id;
	}

	/**
	 * フォーム要素に割り当てるid属性を設定する
	 *
	 * @param string $id フォーム要素に割り当てるid属性値
	 * @return CoralFormItem
	 */
	public function setId($id) {
		$this->_id = $this->getStringValue($id);

		return $this;
	}

	/**
	 * フォーム要素にマッピングされるデータベースカラム情報を取得する。
	 *
	 * @return string
	 */
	public function getColumnMap() {
		return $this->_column_map;
	}

	/**
	 * フォーム要素にマッピングされるデータベースカラム情報を設定する。
	 * 値は<テーブル名>.<カラム名>の形式である必要がある。
	 *
	 * @param string $columnMap フォーム要素にマッピングされるデータベースカラム情報
	 * @return CoralFormItem
	 */
	public function setColumnMap($columnMap) {
		$this->_column_map = $this->getStringValue($columnMap);

		return $this;
	}

	/**
	 * フォーム要素に関連付けるキャプション文字列を取得する
	 *
	 * @return string
	 */
	public function getCaption() {
		return $this->_caption;
	}

	/**
	 * フォーム要素に関連付けるキャプション文字列を設定する
	 *
	 * @param string $caption フォーム要素に関連付けるキャプション文字列
	 * @return CoralFormItem
	 */
	public function setCaption($caption) {
		$this->_caption = $this->getStringValue($caption);

		return $this;
	}

	/**
	 * フォーム要素に関連付ける説明文章を取得する
	 *
	 * @return string
	 */
	public function getDescription() {
		return $this->_description;
	}

	/**
	 * フォーム要素に関連付ける説明文章を設定する
	 *
	 * @param string $description フォーム要素に関連付ける説明文章
	 * @return CoralFormItem
	 */
	public function setDescription($description) {
		$this->_description = $this->getStringValue($description);

		return $this;
	}

	/**
	 * フォーム要素の値を検証するためのPerl互換正規表現文字列を取得する
	 *
	 * @return string
	 */
	public function getValidation() {
		return $this->_validation;
	}

	/**
	 * ereg(), mb_ereg()向けの検証用正規表現文字列を取得する
	 *
	 * @return string
	 */
	public function getEregValidation() {
		return preg_replace( '/^\/(.+)\/[img]*$/', '$1', $this->_validation );
	}

	/**
	 * フォーム要素の値を検証するためのPerl互換正規表現文字列を設定する。
	 * この値は、ブラウザ側のJavaScriptコードとの共用を想定しているため、
	 * デリミタはスラッシュ（/）、指定可能なパターン修正子は「i」および「m」のみに制限される。
	 *
	 * @param string $validation 検証に使用されるPerl互換正規表現文字列
	 * @return CoralFormItem
	 */
	public function setValidation($validation) {
		$this->_validation = $this->getStringValue($validation);

		return $this;
	}

	/**
	 * フォーム要素に設定するHTML属性の連想配列を取得する
	 *
	 * @return array
	 */
	public function getAttributes() {
		return $this->_attributes;
	}

	/**
	 * フォーム要素に設定されたHTML属性を取得する
	 *
	 * @param string $attrName 取得するHTML属性の属性名
	 * @return string
	 */
	public function getAttribute($attrName) {
		return isset($this->_attributes[ $attrName ]) ? $this->_attributes[ $attrName ] : null;
	}

	/**
	 * フォーム要素に設定するHTML属性の連想配列を設定する
	 *
	 * @param array $attributes HTML属性を示す連想配列。キーが属性名、値が属性値になる
	 * @return CoralFormItem
	 */
	public function setAttributes($attributes) {
		if( ! is_array( $attributes ) ) $attributes = array();
		$this->_attributes = $attributes;

		return $this;
	}

	/**
	 * フォーム要素に設定するHTML属性を追加する。
	 * {@link $value}に空の文字列またはnullを指定した場合、対象の属性は削除される
	 *
	 * @param string $name 追加する属性の属性名
	 * @param string $value 追加する属性の属性値
	 * @return CoralFormItem
	 */
	public function addAttribute($name, $value) {
		if( empty( $value ) ) {
			return $this->removeAttribute( $name );
		}

		$this->_attributes[ $name ] = $value;

		return $this;
	}

	/**
	 * フォーム要素に設定するHTML属性から、指定の属性名の属性を削除する
	 *
	 * @param string $name 削除するHTML属性の属性名
	 * @return CoralFormItem
	 */
	public function removeAttribute($name) {
		$attr = array();
		foreach( $this->_attributes as $key => $value ) {
			if( $key != $name ) $attr[$key] = $value;
		}

		return $this->setAttributes( $attr );
	}

	/**
	 * フォーム要素が使用するサブ要素の連想配列を取得する
	 *
	 * @return array
	 */
	public function getOptions() {
		return $this->_options;
	}

	/**
	 * フォーム要素が使用するサブ要素の連想配列を設定する。
	 *
	 * @param array $options
	 * @return CoralFormItem
	 */
	public function setOptions($options) {
		if( ! is_array( $options ) ) $options = array( (string)$options );
		$this->_options = $options;

		return $this;
	}

	/**
	 * フォーム要素の値を指定して、XHTMLソースを作成する
	 *
	 * @param mixed $value フォーム要素に設定する値
	 * @return string
	 */
	public function createSource($value = null) {
		return self::createFieldSource(
			empty($value) ? '' : $value,
			$this->getType(),
			$this->getName(),
			$this->getId(),
			$this->getAttributes(),
			$this->getOptions()
		);
	}
}
