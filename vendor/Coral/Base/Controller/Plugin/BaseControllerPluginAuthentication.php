<?php
namespace Coral\Base\Controller\Plugin;

require_once 'Zend/Session/Namespace.php';
require_once 'Zend/Controller/Request/Abstract.php';
require_once 'Zend/Controller/Plugin/Abstract.php';

/**
 * 認証マネージャを使用して、アクション単位で認証を管理する認証プラグイン
 * TODO: モジュールディレクトリ対応
 */
class BaseControllerPluginAuthentication extends Zend_Controller_Plugin_Abstract {
	const PREAUTH_REQUEST_MEMBER = 'preAuthRequest';
	
	/**
	 * 認証処理に使用する認証マネージャ
	 * 
	 * @var NetB_Auth_Manager
	 */
	private $_authManager;
	
	/**
	 * 認証フォームのアクションパス
	 * 
	 * @var string
	 */
	private $_authAction;
	
	/**
	 * 未認証のアクション要求を受け付けるかのデフォルトポリシー
	 * 
	 * @var bool
	 */
	private $_acceptNoAuth;
	
	/**
	 * 認証ポリシーから除外するアクションパスのマップ
	 * 
	 * @var array
	 */
	private $_excActions = array();
	
	/**
	 * NetB_Controller_Plugin_Authenticationの新しいインスタンスを初期化する
	 * 
	 * @param NetB_Auth_Manager $authManager 認証マネージャ
	 * @param string $authAction 認証フォームを表示するアクションのパス
	 * @param bool $acceptNoAuth デフォルトの認証ポリシー。trueを設定するとすべてのアクションを未認証で行える。省略時はfalse
	 * @param array $exclusiveActions 
	 * @param string $namespace 認証マネージャに設定するセッション名前空間。省略時は$authManagerの名前空間を引き継ぐ
	 */
	public function __construct($authManager, $authAction = '', $acceptNoAuth = false, array $exclusiveActions = array(), $namespace = null) {
		$this->_authManager = $authManager;
		
		if( empty($namespace) ) $namespace = $this->_authManager->getSessionNamespace();
		
		$this->setAuthActionPath( $authAction )
			->setAcceptNoAuth( $acceptNoAuth )
			->setExclusiveActions( $exclusiveActions )
			->setSessionNamespace( $namespace );
		
	}
	
	/**
	 * 認証マネージャを取得する
	 *
	 * @return NetB_Auth_Manager
	 */
	public function getAuthManager() {
		return $this->_authManager;
	}
	/**
	 * 認証に使用する認証マネージャを設定する
	 *
	 * @param NetB_Auth_Manager $authManager
	 * @return NetB_Controller_Plugin_Authentication
	 */
	public function setAuthManager(NetB_Auth_Manager $authManager) {
		$this->_authManager = $authManager;
		return $this;
	}
	
	/**
	 * 認証フォームのアクションパス（コントローラ名/アクション名形式のパス）を取得する
	 * 
	 * @return string
	 */
	public function getAuthActionPath() {
		return $this->_authAction;
	}
	/**
	 * 認証フォームのアクションパスを設定する
	 * 
	 * @param string $actionPath 認証フォームのアクションパス。[モジュール名/]コントローラ名/アクション名形式の必要がある
	 * @return NetB_Controller_Plugin_Authentication
	 */
	public function setAuthActionPath($actionPath) {
		$this->_authAction = $this->_fixActionPath( (string)$actionPath );
		
		return $this;
	}
	
	/**
	 * 未認証時にアクション要求を受け付けるかのデフォルトポリシーを取得する
	 * 
	 * @return bool
	 */
	public function getAcceptNoAuth() {
		return $this->_acceptNoAuth;
	}
	/**
	 * 未認証時にアクション要求を受け付けるかのデフォルトポリシーを設定する
	 *
	 * @param bool $accept 未認証時にアクション要求を受け付けるかを示すbool値
	 * @return NetB_Controller_Plugin_Authentication
	 */
	public function setAcceptNoAuth($accept = false) {
		$this->_acceptNoAuth = (bool)$accept;
		
		return $this;
	}
	
	/**
	 * 基本ポリシー適用外のアクションパスの配列を取得する
	 * 
	 * @return array
	 */
	public function getExclusiveActions() {
		return $this->_excActions;
	}
	/**
	 * 基本ポリシーを適用しないアクションパスの配列を設定する
	 * 
	 * @param string|array $action ポリシー適用外指定をするアクションのパスまたはパスの配列
	 * @return NetB_Controller_Plugin_Authentication
	 */
	public function setExclusiveActions(array $action) {
		$this->_excActions = array();
		
		return $this->addExclusiveAction( $action );
	}
	/**
	 * 基本ポリシー適用外のアクションパスを追加する
	 * 
	 * @param string|array $action ポリシー適用外指定をするアクションのパスまたはパスの配列
	 * @return NetB_Controller_Plugin_Authentication
	 */
	public function addExclusiveAction($action) {
		if( ! is_array($action) ) $action = array( $action );
		foreach($action as $item) {
			$this->_excActions[] = $this->_fixActionPath( (string)$item );
		}
		
		return $this;
	}
	/**
	 * 基本ポリシー適用外のアクションリストから指定のアクションパスを除去する
	 * 
	 * @param string|array $action ポリシー適用外リストから除外するアクションパスまたはパスの配列
	 * @return NetB_Controller_Plugin_Authentication
	 */
	public function removeExclusiveAction($action) {
		if( ! is_array($action) ) $action = array( $action );
		
		array_map( array( $this, '_fixActionPath' ), $action );
		$arr = array();
		foreach($this->_excActions as $oldAction) {
			if( ! in_array( $oldAction, $actino ) ) $arr[] = $oldAction;
		}
		
		$this->_excActions = $arr;
		return $this;
	}
	
	/**
	 * 指定のアクションパスがポリシー適用外リストに含まれるかを判断する
	 * 
	 * @param string $action 検査するアクションパス
	 * @return bool $actionがポリシー適用外リストに含まれている場合はtrue、それ以外はfalse
	 */
	private function _inList($action) {
		foreach($this->_excActions as $act) {
			$pattern = '/' . join('\/', explode( '/', $act ) ) . '/';
			if( preg_match( $pattern, $action ) ) return true;
		}
		return false;
	}
	
	/**
	 * 設定されている認証アクションへディスパッチする
	 * 
	 * @param Zend_Controller_Request_Abstract $request
	 */
	private function _dispatchToAuth(Zend_Controller_Request_Abstract $request) {
		// 認証アクションパス未設定の場合は例外発生
		if( empty( $this->_authAction ) ) {
			throw new Exception( '認証フォームが設定されていません。' );
		}
		
		// 認証アクションパスをリクエストに割り当ててディスパッチする
		$actionParts = explode( '/', $this->_authAction );
		$request->setControllerName( $actionParts[0] )
			->setActionName( $actionParts[1] );
	}
	
	/**
	 * 指定のパス文字列を :controller/:action の形式に整形する
	 * 
	 * @param string $path 整形するパス文字列
	 * @return string 整形されたパス文字列。必ず2つのパートがスラッシュで区切られた形式になる
	 */
	private function _fixActionPath($path) {
		$front = Zend_Controller_Front::getInstance();
		$result = array(
			strtolower( $front->getDefaultControllerName() ),
			strtolower( $front->getDefaultAction() )
		);
		
		$cnt = 0;
		foreach( explode( '/', (string)$path ) as $part ) {
			$part = trim($part);
			if( ! empty( $part ) ) $result[] = strtolower( $part );
			if( ++$cnt > 2 ) break;
		}
		$result = join( '/', array_slice( $result, -2 ) );
		return $result;
	}
	
	/**
	 * フロントコントローラでルーティングが開始される際に
	 * 呼び出されます
	 *
	 * @param Zend_Controller_Request_Abstract $request
	 * @return void
	 */
	public function routeStartup(Zend_Controller_Request_Abstract $request) {
	}

	/**
	 * ルーティング処理が終了する際に呼び出されます
	 *
	 * @param  Zend_Controller_Request_Abstract $request
	 * @return void
	 */
	public function routeShutdown(Zend_Controller_Request_Abstract $request) {
	}

	/**
	 * フロントコントローラでディスパッチループが開始される前に
	 * 呼び出されます
	 *
	 * @param  Zend_Controller_Request_Abstract $request
	 * @return void
	 */
	public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request) {
		// 認証済みの場合は何も処理しない
		if( $this->_authManager->isAuthenticated() ) return;
		
		// リクエスト情報から要求アクションパスを生成
		$action = $this->_fixActionPath(
			join(
				'/',
				array(
					$request->getModuleName(),
					$request->getControllerName(),
					$request->getActionName()
				)
			)
		);
		
		// 認証フォームへの指定の場合は処理しない
		if( ! empty( $this->_authAction ) && $action == $this->_authAction ) return;
		
		// 基本ポリシー適用外のアクションか
		$exclusive = $this->_inList( $action );
		
		if(
			// 基本ポリシーが拒否 且つ ポリシー適用対象
			( ! $this->_acceptNoAuth && ! $exclusive ) ||
			// 基本ポリシーが許可 且つ ポリシー適用外
			( $this->_acceptNoAuth && $exclusive )
		) {
			// ディスパッチ前にリクエストをセッションに保存
//			$session = new Zend_Session_Namespace('PreAuth');
//			if( ! isset( $session->requestInfo ) ) {
//				$session->requestInfo = $request->getParams();
//			}
			$this->setPreAuthRequest( $request->getParams() );
			
			// 認証アクションへディスパッチ
			$this->_dispatchToAuth($request);
			return;
		}
	}

	/**
	 * アクションへのディスパッチが発生する直前に呼び出されます。このメソッドは
	 * 1つのリクエスト内でディスパッチが発生するたびに呼び出されます
	 *
	 * @param  Zend_Controller_Request_Abstract $request
	 * @return void
	 */
	public function preDispatch(Zend_Controller_Request_Abstract $request) {
	}

	/**
	 * ディスパッチされたアクションが完了した直後に呼び出されます。このメソッドは
	 * 1つのリクエスト内でディスパッチが発生するたびに呼び出されます
	 *
	 * @param  Zend_Controller_Request_Abstract $request
	 * @return void
	 */
	public function postDispatch(Zend_Controller_Request_Abstract $request) {
	}

	/**
	 * フロントコントローラでディスパッチループが終了した後に
	 * 呼び出されます
	 *
	 * @return void
	 */
	public function dispatchLoopShutdown() {
	}

	/**
	 * 認証情報を書き込むセッションストレージの名前空間を取得する
	 * 
	 * @return string
	 */
	public function getSessionNamespace() {
		return $this->getAuthManager()->getSessionNamespace();
	}
	
	/**
	 * 認証情報を書き込むセッションストレージの名前空間を設定する
	 * 
	 * @param string $namespace セッションストレージの名前空間。省略時はZend_Auth_Strage_Sessionのデフォルト名前空間が採用される
	 * @return NetB_Controller_Plugin_Authentication
	 */
	public function setSessionNamespace($namespace = null) {
		$this->getAuthManager()->setSessionNamespace( $namespace );
		
		return $this;
	}
	
	/**
	 * 認証前のリクエスト情報を取得する
	 *
	 * @param bool $clear 取得後にセッションからリクエスト情報をクリアするかを指定する。省略時はtrue
	 * @return mixed
	 */
	public function getPreAuthRequest($clear = true) {
		$clear = $clear ? true : false;
		
		// この処理固有のZend_Session_Namespaceを取得
		$session = $this->getPreAuthSession();
		// リクエスト情報を取得
		$result = $session->{self::PREAUTH_REQUEST_MEMBER};
		if( $clear ) {
			// クリアフラグにtrueが指定されていたらクリアする
			unset( $session->{self::PREAUTH_REQUEST_MEMBER} );
		}
		
		return $result;
	}
	
	/**
	 * 認証前のリクエスト情報を退避する
	 *
	 * @param mixed $request 退避するリクエスト情報
	 * @return NetB_Controller_Plugin_Authentication
	 */
	public function setPreAuthRequest($request) {
		$session = $this->getPreAuthSession();
		$session->{self::PREAUTH_REQUEST_MEMBER} = $request;
		
		return $this;
	}
	
	/**
	 * 認証前情報を保存するZend_Session_Namespaceを取得する
	 *
	 * @return Zend_Session_Namespace
	 */
	private function getPreAuthSession() {
		// 認証マネージャの名前空間をプリフィックスにする
		$namespace = $this->getSessionNamespace() . '_PreAuthRequest';
		return new Zend_Session_Namespace( $namespace );
	}
}

?>
