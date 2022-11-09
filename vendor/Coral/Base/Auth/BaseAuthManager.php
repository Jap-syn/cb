<?php
namespace Coral\Base\Auth;

use Zend\Db\Adapter\Adapter;
use Zend\Authentication\Adapter\DbTable as AuthAdapter;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Storage\Session as SessionStrage;
use Zend\Session\Storage\SessionStorage;
use Zend\Authentication\Storage\Session;
use Zend\Authentication\Result;
use Coral\Base\BaseDelegate;

/**
 * ユーザー認証処理を管理するマネージャークラス
 */
class BaseAuthManager {
	/**
	 * 認証セッションストレージに使用する名前空間のサフィックス
	 */
	const SESSION_NAMESPACE_SUFFIX = '_Auth';

	/**
	 * 認証オブジェクトのインスタンス
	 *
	 * @access protected
	 * @var AuthenticationService
	 */
	protected $_auth;

	/**
	 * 認証アダプタが使用するAdapter
	 *
	 * @access protected
	 * @var Adapter
	 */
	protected $_dbAdapter;

	/**
	 * 認証テーブルの名前
	 *
	 * @access protected
	 * @var string
	 */
	protected $_tableName;

	/**
	 * 認証テーブルのIDカラム名
	 *
	 * @access protected
	 * @var string
	 */
	protected $_idColumn;

	/**
	 * 認証テーブルのパスワードカラム名
	 *
	 * @access protected
	 * @var string
	 */
	protected $_pswColumn;

	/**
	 * 認証テーブルのメタデータキャッシュ
	 *
	 * @access protected
	 * @var stdClass
	 */
	protected $_tableMetadata = null;

	/**
	 * 認証セッションストレージの既定の名前空間
	 *
	 * @var string
	 */
	protected $_sessionNamespace;

	/**
	 * DB認証処理後に実行される追加の認証コールバック配列
	 *
	 * @access protected
	 * @var array
	 */
	protected $_additional_authentications;

	/**
	 * BaseAuthManagerの新しいインスタンスを初期化します。
	 *
	 * @access public
	 * @param $dbAdapter 認証用データベースへ接続するDbAdapter
	 * @param $tableName string 認証テーブルの名前
	 * @param $idColumn string 認証テーブルのIDカラム名
	 * @param $pswColumn string 認証テーブルのパスワードカラム名
	 * @param $namespace string 認証セッションストレージの既定の名前空間。省略時はZend\Authentication\Storage\Sessionのデフォルト名前空間が採用される
	 */
	public function __construct(Adapter $dbAdapter, $tableName, $idColumn, $pswColumn, $namespace = null) {

	    $this->_auth = new AuthenticationService();

        $this->setDbAdapter($dbAdapter);
        $this->setAuthTableName($tableName);
        $this->setAuthIdColumn($idColumn);
        $this->setAuthPasswordColumn($pswColumn);
        $this->setSessionNamespace($namespace);

        $this->_additional_authentications = array();
	}

	/**
	 * DBアダプタを取得する
	 *
	 * @return Adapter
	 */
	public function getDbAdapter() {
		return $this->_dbAdapter;
	}
	/**
	 * DBアダプタを設定する
	 *
	 * @param Adapter $dbAdapter DBアダプタ
	 * @return BaseAuthManager このインスタンス
	 */
	public function setDbAdapter(Adapter $dbAdapter) {
		$this->_dbAdapter = $dbAdapter;
		return $this;
	}

	/**
	 * 認証テーブルのテーブル名を取得する
	 *
	 * @return string
	 */
	public function getAuthTableName() {
		return $this->_tableName;
	}
	/**
	 * 認証テーブルのテーブル名を設定する
	 *
	 * @param string $tableName 認証テーブルのテーブル名
	 * @return BaseAuthManager このインスタンス
	 */
	public function setAuthTableName($tableName) {
		$this->_tableName = $tableName;
		return $this;
	}

	/**
	 * 認証テーブルのIDカラム名を取得する
	 *
	 * @return string
	 */
	public function getAuthIdColumn() {
		return $this->_idColumn;
	}
	/**
	 * 認証テーブルのIDカラム名を設定する
	 *
	 * @param string $idColumn 認証テーブルのIDカラム名
	 * @return BaseAuthManager このインスタンス
	 */
	public function setAuthIdColumn($idColumn) {
		$this->_idColumn = $idColumn;
		return $this;
	}

	/**
	 * 認証テーブルのパスワードカラム名を取得する
	 *
	 * @return string
	 */
	public function getAuthPasswordColumn() {
		return $this->_pswColumn;
	}
	/**
	 * 認証テーブルのパスワードカラム名を設定する
	 *
	 * @param string $passwordColumn 認証テーブルのパスワードカラム名
	 * @return BaseAuthManager このインスタンス
	 */
	public function setAuthPasswordColumn($passwordColumn) {
		$this->_pswColumn = $passwordColumn;
		return $this;
	}

	/**
	 * 現在の設定に基づくAuthのアダプタインスタンスを取得する
	 *
	 * @return AuthAdapter
	 */
	public function getAuthAdapter() {

	    $authAdapter = new AuthAdapter( $this->getDbAdapter() );

	    $authAdapter->setTableName($this->getAuthTableName())
	                ->setIdentityColumn($this->getAuthIdColumn())
	                ->setCredentialColumn($this->getAuthPasswordColumn());

	    return $authAdapter;
	}

	/**
	 * IDとパスワードを指定してユーザ認証処理を行います。
	 *
	 * @access public
	 * @param $userId string 認証ID
	 * @param $password string 認証パスワード
	 * @return Result 認証結果オブジェクト
	 */
	public function login($userId, $password) {
 		$auth_adapter = $this->getAuthAdapter();
		$auth_adapter
			->setIdentity( $userId )
			->setCredential( $password );

		$result = $this->_auth->authenticate( $auth_adapter );
		if( $result->isValid() ) {
			if( ! $this->execAdditionalAuthentications($userId, $password, $auth_adapter->getResultRowObject()) ) {
 			    $this->logout();
				$result = new Result(
					Result::FAILURE_IDENTITY_NOT_FOUND,
					$userId,
					array( 'identity could not found.' )
				);
			} else {
				$this->_auth->getStorage()->write( $auth_adapter->getResultRowObject() );
			}
		}
		return $result;
	}

	/**
	 * このセッションのユーザをログアウトします。
	 *
	 * @access public
	 */
	public function logout() {
		$this->_auth->clearIdentity();
	}

	/**
	 * このセッションのユーザが認証済みかを取得します
	 *
	 * @access public
	 * @return bool
	 */
	public function isAuthenticated() {
		return $this->_auth->hasIdentity();
	}

	/**
	 * このセッションのユーザのユーザ情報を取得します
	 *
	 * @access public
	 * @return mixed
	 */
	public function getUserInfo() {
		return $this->_auth->getStorage()->read();
	}

// Del By Takemasa(NDC) 20141202 Stt 未使用故コメントアウト化
// 	/**
// 	 * ユーザー情報のスキーマに一致する、空のstdClassを作成します。
// 	 *
// 	 * @access public
// 	 * @return stdClass
// 	 */
// 	public function createStubInfo() {
// 		if( $this->_tableMetadata == null ) {
// 			$tbl = new NetB_Db_Table_Generic( array(
// 				NetB_Db_Table_Generic::ADAPTER => $this->_dbAdapter,
// 				NetB_Db_Table_Generic::NAME => $this->_tableName
// 			) );
//
// 			$info = $tbl->info();
// 			$this->_tableMetadata = $info['metadata'];
// 		}
//
// 		$result = new stdClass();
//
// 		foreach( $this->_tableMetadata as $key => $value ) {
// 			$result->$key = null;
// 		}
//
// 		return $result;
// 	}
// Del By Takemasa(NDC) 20141202 End 未使用故コメントアウト化

	/**
	 * DB認証後に行う、追加の認証コールバックを処理リストに追加する。
	 * 登録するコールバックは、認証IDとパスワード、およびResultRowObjectを受け取り、boolの値を返す必要がある
	 *
	 * @param BaseDelegate $callback 追加の認証処理を行うコールバックデリゲート
	 * @return BaseAuthManager
	 */
	public function addAuthentication(BaseDelegate $callback) {
		$this->_additional_authentications[] = $callback;

		return $this;
	}

// Del By Takemasa(NDC) 20141202 Stt 未使用故コメントアウト化
// 	/**
// 	 * 指定の認証コールバックを追加認証処理リストから削除する
// 	 *
// 	 * @param NetB_Delegate $callback 処理リストから削除するコールバックデリゲート
// 	 * @return NetB_Auth_Manager
// 	 */
// 	public function removeAuthentication(NetB_Delegate $callback) {
// 		$list = array();
// 		foreach( $this->_additional_authentications as $cb ) {
// 			if( $cb != $callback ) $list[] = $cb;
// 		}
// 		$this->_additional_authentications = $list;
//
// 		return $this;
// 	}
// Del By Takemasa(NDC) 20141202 End 未使用故コメントアウト化

	/**
	 * 現在登録されている追加認証コールバックリストを取得する
	 *
	 * @return array 現在登録されている追加認証処理リストのクローン
	 */
	public function getAddtionalAuthentications() {
		return array_merge( array(), $this->_additional_authentications );
	}

	/**
	 * 現在登録されている追加認証コールバックをすべてクリアする
	 *
	 * @return BaseAuthManager
	 */
	public function clearAdditionalAuthentications() {
		$this->_additional_authentications = array();

		return $this;
	}

	/**
	 * 認証アダプタで取得したResultRowObjectを元に
	 * このアプリケーションで使用するユーザ情報オブジェクトを返します。
	 *
	 * このメソッドを派生クラスでオーバーライドすることにより、
	 * アプリケーションで必要とするユーザ情報をセッションに関連付けることができます。
	 *
	 * @access protected
	 * @param stdClass $obj
	 * @return mixed
	 */
	protected function createUserInfo($obj) {
		// TODO: 派生クラスで必要なオブジェクトを返すようにオーバーライドしてください。
		return $obj;
	}

	/**
	 * 認証IDとパスワード、および認証アダプタで取得したResultRowObjectを使用して、登録されている
	 * 追加認証コールバックをすべて実行し、認証を確定させる。
	 *
	 * @param string $userId 認証ID。loginメソッドに渡されたものに一致
	 * @param string $password 認証パスワード。loginメソッドに渡されたものに一致
	 * @param stdClass $rowObject 認証アダプタで取得したResultRowObject
	 * @param null | stdClass $altRowObject 認証アダプタで取得した代理認証結果のResultRowObject
	 * @return bool すべての追加認証処理をクリアした場合はtrue、それ以外はfalse
	 */
	protected function execAdditionalAuthentications($userId, $password, $rowObject, $altRowObject = null) {
		foreach( $this->_additional_authentications as $auth_method ) {
			if( ! $auth_method->invoke( $userId, $password, $rowObject, $altRowObject ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * 認証情報を書き込むセッションストレージの名前空間を取得する
	 *
	 * @return string
	 */
	public function getSessionNamespace() {
		return $this->_sessionNamespace;
	}

	/**
	 * 認証情報を書き込むセッションストレージの名前空間を設定する。
	 * 省略時はZend\Authentication\Storage\Sessionのデフォルト名前空間が採用される。
	 *
	 * @param string $namespace セッションストレージに設定する名前空間
	 * @return BaseAuthManager
	 */
	public function setSessionNamespace($namespace = null) {

	    if( empty( $namespace ) ) $namespace = Session::NAMESPACE_DEFAULT;
		if( $namespace !== $this->_sessionNamespace ) {
			// 現在と違う名前空間の場合のみ再設定
			$this->_sessionNamespace = $namespace;
 			$this->_auth->setStorage( new Session( $this->_sessionNamespace ) );
		}

		return $this;
	}
}
