<?php
namespace Coral\Coral\View;

use Coral\Coral\View\CommandBar\CoralViewCommandBarButton;

/**
 * ツールバー風のインターフェイスをレンダリングするためのクラスで、
 * CoralViewCommandBarButtonを複数追加できる。
 * レンダリングは同時にロードするCSSに依存する。
 */
class CoralViewCommandBar {
	/**
	 * コマンドバーのid属性を指定するコンストラクタオプション定数
	 */
	const OPTION_ID = 'id';

	/**
	 * コマンドバーのCSSクラス名を指定するコンストラクタオプション定数
	 */
	const OPTION_CLASSNAME = 'className';

	/**
	 * タイトル領域のCSSクラス名を指定するコンストラクタオプション定数
	 */
	const OPTION_TITLE_CLASSNAME = 'titleClassName';

	/**
	 * ボタンコンテナ要素のCSSクラス名を指定するコンストラクタオプション定数
	 */
	const OPTION_CONTAINER_CLASSNAME = 'containerClassName';

	/**
	 * コマンドボタンのCSSクラス名を指定するコンストラクタオプション
	 */
	const OPTION_BUTTON_CLASSNAME = 'buttonClassName';

	/**
	 * コマンドバーのデフォルトCSSクラス名
	 */
	const DEFAULT_CLASSNAME = 'command_bar';

	/**
	 * タイトル領域のデフォルトCSSクラス名
	 */
	const DEFAULT_TITLE_CLASSNAME = 'title';

	/**
	 * ボタンコンテナ要素のデフォルトCSSクラス名
	 */
	const DEFAULT_CONTAINER_CLASSNAME = 'button_container';

	/**
	 * コマンドバーのid属性
	 *
	 * @var string
	 */
	protected $_id;

	/**
	 * コマンドバーのCSSクラス名
	 *
	 * @var string
	 */
	protected $_className;

	/**
	 * タイトル領域のCSSクラス名
	 *
	 * @var string
	 */
	protected $_titleClassName;

	/**
	 * ボタンコンテナ要素のCSSクラス名
	 *
	 * @var string
	 */
	protected $_containerClassName;

	/**
	 * コマンドボタンのCSSクラス名
	 *
	 * @var string
	 */
	protected $_buttonClassName;

	/**
	 * タイトル
	 *
	 * @var string
	 */
	protected $_title;

	/**
	 * コマンドボタンリスト
	 *
	 * @var array
	 */
	protected $_buttons;

	/**
	 * タイトルとボタンコレクションおよびオプションリストを指定して、
	 * CoralViewCommandBarButtonの新しいインスタンスを初期化する
	 *
	 * @param string $title タイトル
	 * @param array|null $buttons CoralViewCommandBarButtonの配列
	 * @param array|null $options オプションパラメータ
	 */
	public function __construct($title, $buttons = array(), $options = array()) {
		// title
		$this->setTitle( $title );

		// buttons
		if( ! is_array($buttons) ) {
			$buttons = array( $buttons );
		}
		$this->setButtons( $buttons );

		// options
		if( ! is_array( $options ) ) $options = array();
		$options = array_merge( array(
			self::OPTION_CLASSNAME => self::DEFAULT_CLASSNAME,
			self::OPTION_TITLE_CLASSNAME => self::DEFAULT_TITLE_CLASSNAME,
			self::OPTION_CONTAINER_CLASSNAME => self::DEFAULT_CONTAINER_CLASSNAME,
			self::OPTION_BUTTON_CLASSNAME => CoralViewCommandBarButton::DEFAULT_CLASSNAME
		), $options );
		foreach( $options as $key => $value ) {
			switch( $key ) {
				case self::OPTION_CLASSNAME:
					$this->setClassName( $value );
					break;
				case self::OPTION_TITLE_CLASSNAME:
					$this->setTitleClassName( $value );
					break;
				case self::OPTION_CONTAINER_CLASSNAME:
					$this->setContainerClassName( $value );
					break;
				case self::OPTION_BUTTON_CLASSNAME:
					$this->setButtonClassName( $value );
					break;
			}
		}

	}

	/**
	 * コマンドバーの要素IDを取得する
	 *
	 * @return string
	 */
	public function getId() {
		return $this->_id;
	}

	/**
	 * コマンドバーの要素IDを設定する
	 *
	 * @param string $id
	 * @return CoralViewCommandBar
	 */
	public function setId($id) {
		$this->_id = htmlspecialchars("$id");
		return $this;
	}

	/**
	 * コマンドバーに表示するタイトルを取得する
	 *
	 * @return string コマンドバーに表示するタイトル
	 */
	public function getTitle() {
		return $this->_title;
	}

	/**
	 * コマンドバーに表示するタイトルを設定する
	 *
	 * @param string $title
	 * @return CoralViewCommandBar
	 */
	public function setTitle($title) {
		$this->_title = "$title";
		return $this;
	}

	/**
	 * コマンドバーのCSSクラス名を取得する
	 *
	 * @return string コマンドバーのCSSクラス名
	 */
	public function getClassName() {
		return $this->_className;
	}

	/**
	 * コマンドバーのCSSクラス名を設定する
	 *
	 * @param string $className コマンドバーに設定するCSSクラス名
	 * @return CoralViewCommandBar
	 */
	public function setClassName($className) {
		$this->_className = htmlspecialchars("$className");
		return $this;
	}

	/**
	 * タイトル領域のCSSクラス名を取得する
	 *
	 * @return string タイトル領域のCSSクラス名
	 */
	public function getTitleClassName() {
		return $this->_titleClassName;
	}

	/**
	 * タイトル領域のCSSクラス名を設定する
	 *
	 * @param string $className タイトル領域に設定するCSSクラス名
	 * @return CoralViewCommandBar
	 */
	public function setTitleClassName($className) {
		$this->_titleClassName = htmlspecialchars("$className");
		return $this;
	}

	/**
	 * ボタンコンテナ要素のCSSクラス名を取得する
	 *
	 * @return string ボタンコンテナ要素のCSSクラス名
	 */
	public function getContainerClassName() {
		return $this->_containerClassName;
	}

	/**
	 * ボタンコンテナ要素のCSSクラス名を設定する
	 *
	 * @param string $className ボタンコンテナ要素に設定するCSSクラス名
	 * @return CoralViewCommandBar
	 */
	public function setContainerClassName($className) {
		$this->_containerClassName = htmlspecialchars("$className");
		return $this;
	}

	/**
	 * コマンドボタンのCSSクラス名を取得する
	 *
	 * @return string コマンドボタンのCSSクラス名
	 */
	public function getButtonClassName() {
		return $this->_buttonClassName;
	}

	/**
	 * コマンドバーボタンのCSSクラス名を設定する
	 *
	 * @param string $className コマンドバーボタンに設定するCSSクラス名
	 * @return CoralViewCommandBar
	 */
	public function setButtonClassName($className) {
		$this->_buttonClassName = htmlspecialchars("$className");
		return $this;
	}

	/**
	 * コマンドボタンをすべて取得する
	 *
	 * @return array
	 */
	public function getButtons() {
		return $this->_buttons;
	}

	/**
	 * コマンドボタンのリストを設定する。
	 * 配列の要素のうち、CoralViewCommandBarButtonだけがボタンとして追加される
	 *
	 * @param array $button コマンドボタンのリスト
	 * @return CoralViewCommandBar
	 */
	public function setButtons($buttons) {
		$this->_buttons = array();
		foreach($buttons as $button) {
			$this->addButton( $button );
		}
		return $this;
	}

	/**
	 * コマンドボタンを追加する
	 *
	 * @param CoralViewCommandBarButton $button 追加するコマンドボタン
	 * @return CoralViewCommandBar
	 */
	public function addButton($button) {
		if( $button instanceof CoralViewCommandBarButton ) {
			$this->_buttons[] = $button;
		}
		return $this;
	}

	/**
	 * 指定のコマンドボタンをリストから削除する
	 *
	 * @param int|CoralViewCommandBarButton $button 削除するボタンまたはボタンの位置
	 * @return CoralViewCommandBar
	 */
	public function removeButton($button) {
		if( ! is_int($button) ) {
			return $this->removeButton( $this->indexOf($button) );
		}

		$index = $button;
		if( $index > 0 && $index < count($this->_buttons) ) {
			$buttons = array();
			for($i = 0; $i < count($this->_buttons); $i++) {
				if( $i != $index ) $buttons[] = $this->_buttons[$i];
			}
			$this->_buttons = $buttons;
		}
		return $this;
	}

	/**
	 * 指定のコマンドボタンをリストから検索し、インデックス位置を取得する
	 *
	 * @param CoralViewCommandBarButton $button 検索するコマンドボタン
	 * @return $buttonのインデックス位置。リストに含まれていないか、CoralViewCommandBarButton以外を指定した場合は-1
	 */
	public function indexOf($button) {
		if( ! ($button instanceof CoralViewCommandBarButton ) ) return -1;
		for($i = 0; $i < count($this->_buttons); $i++) {
			if( $button === $this->_buttons[$i] ) return $i;
		}
		return -1;
	}

	/**
	 * コマンドバーを描画するHTMLソース文字列を生成する
	 *
	 * @return string
	 */
	public function render() {
		$attrs = array();

		$id = $this->getId();
		if( ! empty( $id ) ) $attrs[] = 'id="' . $id . '"';

		$attrs[] = 'class="' . $this->getClassName() . '"';

		$attrs = join( ' ', $attrs );

		$buttons = array();
		foreach( $this->getButtons() as $button ) {
			$buttons[] = $button->setClassName( $this->getButtonClassName() )->render();
		}
		$container = join( '', array(
			'<div class="',
			$this->getContainerClassName(),
			'">' .
			join( '', $buttons ),
			'</div>'
		) );

		$title = '<div class="' . $this->getTitleClassName() . '">' . $this->getTitle() . '</div>';

		return "<div $attrs>$container$title</div>";
	}
}
