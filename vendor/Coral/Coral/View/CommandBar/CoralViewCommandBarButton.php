<?php
namespace Coral\Coral\View\CommandBar;

/**
 * CoralViewCommandBarButtonに追加されるコマンドボタンクラス。
 * 単独でのレンダリングも可能
 */
class CoralViewCommandBarButton {
	/**
	 * id属性を指定するコンストラクタオプション定数
	 */
	const OPTION_ID = 'id';

	/**
	 * href属性を指定するコンストラクタオプション定数
	 */
	const OPTION_HREF = 'href';

	/**
	 * 表示文字列を指定するコンストラクタオプション定数
	 */
	const OPTION_TEXT = 'text';

	/**
	 * title属性を指定するコンストラクタオプション定数
	 */
	const OPTION_TITLE = 'title';

	/**
	 * CSSクラス名を指定するコンストラクタオプション定数
	 */
	const OPTION_CLASSNAME = 'className';

	/**
	 * id属性のデフォルト値
	 */
	const DEFAULT_ID = '';

	/**
	 * href属性のデフォルト値
	 */
	const DEFAULT_HREF = '#';

	/**
	 * 表示文字列のデフォルト値
	 */
	const DEFAULT_TEXT = '&nbsp;';

	/**
	 * title属性のデフォルト値
	 */
	const DEFAULT_TITLE = '';

	/**
	 * CSSクラス名のデフォルト値
	 */
	const DEFAULT_CLASSNAME = 'command';

	/**
	 * id属性
	 *
	 * @var string
	 */
	protected $_id;

	/**
	 * href属性
	 *
	 * @var string
	 */
	protected $_href;

	/**
	 * 表示文字列
	 *
	 * @var string
	 */
	protected $_text;

	/**
	 * title属性
	 *
	 * @var string
	 */
	protected $_title;

	/**
	 * CSSクラス名
	 */
	protected $_className;

	/**
	 * オプションリストを指定して、CoralViewCommandBarButtonの
	 * 新しいインスタンスを初期化する
	 *
	 * @param array|null $options オプションリスト
	 */
	public function __construct($options = array()) {
		if( ! is_array($options) ) $options = array();

		$options = array_merge( array(
			self::OPTION_ID => self::DEFAULT_ID,
			self::OPTION_HREF => self::DEFAULT_HREF,
			self::OPTION_TEXT => self::DEFAULT_TEXT,
			self::OPTION_TITLE => self::DEFAULT_TITLE,
			self::OPTION_CLASSNAME => self::DEFAULT_CLASSNAME
		), $options );

		foreach($options as $key => $value) {
			switch($key) {
				case self::OPTION_CLASSNAME:
					$this->setClassName( $value );
					break;
				case self::OPTION_HREF:
					$this->setHref( $value );
					break;
				case self::OPTION_ID:
					$this->setId( $value );
					break;
				case self::OPTION_TEXT:
					$this->setText( $value );
					break;
				case self::OPTION_TITLE:
					$this->setTitle( $value );
					break;
			}
		}
	}

	/**
	 * id属性値を取得する
	 *
	 * @return string id属性値
	 */
	public function getId() {
		return $this->_id;
	}

	/**
	 * id属性値を設定する
	 *
	 * @param string $id 設定するid属性値
	 * @return CoralViewCommandBarButton
	 */
	public function setId($id) {
		$this->_id = "$id";

		return $this;
	}

	/**
	 * href属性値を取得する
	 *
	 * @return string href属性値
	 */
	public function getHref() {
		return $this->_href;
	}

	/**
	 * href属性値を設定する
	 *
	 * @param string $href 設定するhref属性値
	 * @return CoralViewCommandBarButton
	 */
	public function setHref($href) {
		$href = htmlspecialchars("$href");
		$this->_href = empty($href) ? self::DEFAULT_HREF : $href;

		return $this;
	}

	/**
	 * 表示文字列を取得する
	 *
	 * @return string 表示文字列
	 */
	public function getText() {
		return $this->_text;
	}

	/**
	 * 表示文字列を設定する。設定値はエスケープされないので、
	 * 入れ子で他のHTML要素を当てはめることも可能
	 *
	 * @param string $text 設定する表示文字列
	 * @return CoralViewCommandBarButton
	 */
	public function setText($text) {
		$text = "$text";
		$this->_text = empty($text) ? self::DEFAULT_TEXT : $text;

		return $this;
	}

	/**
	 * title属性値を取得する
	 *
	 * @return string title属性値
	 */
	public function getTitle() {
		return $this->_title;
	}

	/**
	 * title属性値を設定する
	 *
	 * @param string $title 設定するtitle属性値
	 * @return CoralViewCommandBarButton
	 */
	public function setTitle($title) {
		$title = htmlspecialchars( "$title" );
		$this->_title = $title;

		return $this;
	}

	/**
	 * CSSクラス名を取得する
	 *
	 * @return string CSSクラス名
	 */
	public function getClassName() {
		return $this->_className;
	}

	/**
	 * CSSクラス名を設定する
	 *
	 * @param string $className 設定するCSSクラス名
	 * @return CoralViewCommandBarButton
	 */
	public function setClassName($className) {
		$className = htmlspecialchars("$className");
		$this->_className = empty($className) ? self::DEFAULT_CLASSNAME : $className;

		return $this;
	}

	/**
	 * コマンドボタンを描画するHTMLソース文字列を生成する
	 *
	 * @return string
	 */
	public function render() {
		$attrs = array();

		$id = $this->getId();
		if( ! empty($id) ) $attrs[] = 'id="' . $id . '"';

		$attrs[] = 'class="' . $this->getClassName() . '"';

		$attrs[] = 'href="' . $this->getHref() . '"';

		$title = $this->getTitle();
		if( ! empty($title) ) $attrs[] = 'title="' . $title . '"';

		$attrs = join( ' ', $attrs );

		$text = $this->getText();
		return "<a $attrs>$text</a>";
	}

}
