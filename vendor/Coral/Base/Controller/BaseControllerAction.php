<?php
namespace Coral\Base\Controller;

require_once 'Zend/Controller/Action.php';
require_once 'NetB/Application/Abstract.php';
require_once 'NetB/Controller/Utility.php';

class BaseControllerAction extends AbstractActionController {
	/**
	 * デフォルトビューにアサインされるベースURLへのキー
	 * 
	 * @var string
	 */
	const VIEW_KEY_BASE_URL = 'baseUrl';
	
	/**
	 * デフォルトビューにアサインされるアクションパスへのキー
	 * 
	 * @var string
	 */
	const VIEW_KEY_CURRENT_ACTION = 'currentAction';
	
	/**
	 * デフォルトビューにアサインされるページタイトルへのキー
	 * 
	 * @var string
	 */
	const VIEW_KEY_PAGE_TITLE = 'pageTitle';
	
	/**
	 * デフォルトビューにアサインされるスタイルシートリストへのキー
	 * 
	 * @var string
	 */
	const VIEW_KEY_STYLE_SHEETS = 'styleSheets';
	
	/**
	 * デフォルトビューにアサインされるJavaScriptリストへのキー
	 * 
	 * @var string
	 */
	const VIEW_KEY_JAVA_SCRIPTS = 'javaScripts';
	
	/**
	 * ビューコンポーネントスクリプトのルートパス。
	 * 実際のルートパスの設定は派生クラスでこの変数をオーバーライドする必要がある
	 *
	 * @var string
	 */
	protected $_componentRoot = './';
	
	/**
	 * 現在のアクションパス
	 *
	 * @var string
	 */
	protected $_currentAction;
	
	/**
	 * ページタイトル
	 * 
	 * @var string
	 */
	protected $_pageTitle;
	
	/**
	 * 初期化処理
	 */
	public function init() {
		$req = $this->getRequest();
		
		// 現在のアクションパスを確定
		$this->_currentAction = $req->getControllerName() . '/' . $req->getActionName();
		
		// コンポーネントのルートパスと、その下のコントローラサブディレクトリを
		// ビューのスクリプト探索パスに追加する
		$this->view
			->addScriptPath( $this->_componentRoot )
			->addScriptPath( rtrim($this->_componentRoot, '/') . '/' . $req->getControllerName() );

		// ビューへ共通パラメータをアサイン
		$this->view
			->assign( NetB_Controller_Action::VIEW_KEY_BASE_URL, NetB_Controller_Utility::getApplicationUrl( $req ) )
			->assign( NetB_Controller_Action::VIEW_KEY_CURRENT_ACTION, $this->_currentAction );
		
		$this
			->_setStyleSheets( array() )
			->_setJavaScripts( array() );
		
		// Last-Modified ヘッダを現在日時で出力する
		// [IEのDL問題対策の影響でページコンテンツが更新されない問題への対応（07.11.15）]
/*
		require_once 'Zend/Date.php';
		$date = new Zend_Date();
		$date->setTimezone('GMT');
		$mod_date = str_replace(
			$date->get( Zend_Date::GMT_DIFF ),
			$date->get( Zend_Date::TIMEZONE_NAME ),
			$date->get( Zend_Date::RFC_1123 )
		);
		$this->getResponse()->setHeader( 'Last-Modified', $mod_date, true );
 */

		// 派生クラスの初期化処理を行う
		$this->_init();
	}
	
	/**
	 * 派生コントローラ固有の初期化処理を実行する
	 */
	protected function _init() {
		// TODO: 派生クラス固有の初期化処理はこのメソッドをオーバーライドする
	}
	
	/**
	 * デフォルトビューにアサインした、アプリケーションのベースURLを取得する。
	 * ビューからは$this->baseUrlでアクセスできる
	 * 
	 * @return string
	 */
	public function getBaseUrl() {
		$vars = $this->view->getVars();
		return $vars[ NetB_Controller_Action::VIEW_KEY_BASE_URL ];
	}
	
	/**
	 * デフォルトビューにアサインした、現在のアクションパスを取得する。
	 * ビューからは$this->currentActionでアクセスできる
	 * 
	 * @return string
	 */
	public function getCurrentAction() {
		$vars = $this->view->getVars();
		return $vars[ NetB_Controller_Action::VIEW_KEY_CURRENT_ACTION ];
	}
	
	/**
	 * デフォルトビューにアサインしたページタイトルを取得する。
	 * ビューからは$this->pageTitleでアクセスできる
	 * 
	 * @return string
	 */
	public function getPageTitle() {
		$vars = $this->view->getVars();
		return $vars[ NetB_Controller_Action::VIEW_KEY_PAGE_TITLE ];
	}
	
	/**
	 * ページタイトルをデフォルトビューへアサインする。
	 * ビューからは$this->pageTitleでアクセスできる
	 * 
	 * @return string
	 */
	public function setPageTitle($title = null) {
		if( $title == null ) $title = '';
		
		$this->view->assign( NetB_Controller_Action::VIEW_KEY_PAGE_TITLE, $title );
		return $this;
	}

	/**
	 * デフォルトビューにアサインされているCSSのURLの配列を取得する。
	 * アサインされている配列は、ビューから$this->styleSheetsでアクセスできる
	 * 
	 * @return array
	 */
	public function getStyleSheets() {
		$vars = $this->view->getVars();
		return $vars[ NetB_Controller_Action::VIEW_KEY_STYLE_SHEETS ];
	}
	
	/**
	 * CSSのURLが格納された配列をデフォルトビューにアサインする。
	 * アサインされている配列は、ビューから$this->styleSheetsでアクセスできる
	 * 
	 * @param array $styleSheets CSSのURLが格納された配列
	 * @return NetB_Controller_Action
	 */
	public function setStyleSheets(array $styleSheets) {
		if( $styleSheets == null ) $styleSheets = array();
		
		foreach( $styleSheets as $item ) $this->addStyleSheet( $item );
		return $this;
	}

	/**
	 * @protected
	 *
	 * 指定のCSSのURL情報を格納した配列をビューへアサインする。
	 * このメソッドは$styleSheetsを無条件にアサインし、各項目の精査は行われない。
	 *
	 * @param array $styleSheets ビューへアサインするJavaScriptのURL情報を格納した配列
	 * @return NetB_Controller_Action
	 */
	protected function _setStyleSheets(array $styleSheets) {
		if( $styleSheets == null ) $styleSheets = array();
		
		$this->view->assign( NetB_Controller_Action::VIEW_KEY_STYLE_SHEETS, $styleSheets );
		return $this;
	}
	
	/**
	 * CSSのURLをデフォルトビューに追加する。
	 * アサインされている配列は、ビューから$this->styleSheetsでアクセスできる。
	 * $res_infoには単独のURLを指定するか、キー'charset'に文字コード、キー'location'に
	 * URLを割り当てた連想配列を指定できる。
	 * 
	 * @param array|string $res_info 追加するCSSのURLまたはエンコード・URLを格納した配列
	 * @return NetB_Controller_Action
	 */
	public function addStyleSheet($res_info = null) {
		if( ! empty( $res_info ) ) {
			if( ! is_array( $res_info ) ) $res_info = array( 'location' => "$res_info" );
			$sheets = $this->getStyleSheets();
			$sheets[] = $res_info;
			$this->_setStyleSheets( $sheets );
		}
		
		return $this;
	}
	
	/**
	 * デフォルトビューにアサインされているJavaScriptのURLの配列を取得する。
	 * アサインされている配列は、ビューから$this->javaScriptsでアクセスできる
	 * 
	 * @return array
	 */
	public function getJavaScripts() {
		$vars = $this->view->getVars();
		return $vars[ NetB_Controller_Action::VIEW_KEY_JAVA_SCRIPTS ];
	}
	
	/**
	 * JavaScriptのURLが格納された配列をデフォルトビューにアサインする。
	 * アサインされている配列は、ビューから$this->javaScriptsでアクセスできる
	 * 
	 * @param array $javaScripts JavaScriptのURLが格納された配列
	 * @return NetB_Controller_Action
	 */
	public function setJavaScripts(array $javaScripts) {
		foreach( $javaScripts as $item ) $this->addJavaScript( $item );
		return $this;
	}

	/**
	 * @protected
	 *
	 * 指定のJavaScriptURL情報を格納した配列をビューへアサインする。
	 * このメソッドは$javaScriptsを無条件にアサインし、各項目の精査は行われない。
	 *
	 * @param array $javaScripts ビューへアサインするJavaScriptのURL情報を格納した配列
	 * @return NetB_Controller_Action
	 */
	protected function _setJavaScripts(array $javaScripts) {
		$this->view->assign( NetB_Controller_Action::VIEW_KEY_JAVA_SCRIPTS, $javaScripts );
		
		return $this;
	}
	
	/**
	 * JavaScriptのURLをデフォルトビューに追加する。
	 * アサインされている配列は、ビューから$this->javaScriptsでアクセスできる
	 * $res_infoには単独のURLを指定するか、キー'charset'に文字コード、キー'location'に
	 * URLを割り当てた連想配列を指定できる。
	 * 
	 * @param array|string $res_info 追加するJavaScriptのURLまたはエンコード・URLを格納した配列
	 * @return NetB_Controller_Action
	 */
	public function addJavaScript($res_info = null) {
		if( ! empty( $res_info ) ) {
			if( ! is_array( $res_info ) ) $res_info = array( 'location' => "$res_info" );
			$scripts = $this->getJavaScripts();
			$scripts[] = $res_info;
			$this->_setJavaScripts( $scripts );
		}
		
		return $this;
	}
}
