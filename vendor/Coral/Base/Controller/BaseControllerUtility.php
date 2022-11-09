<?php
namespace Coral\Base\Controller;

//require_once 'Zend/Controller/Request/Http.php';
use Zend\Stdlib\RequestInterface;

/**
 * コントローラをサポートするユーティリティクラス
 */
final class BaseControllerUtility {

// BaseControllerActionのgetBaseUrl()に移植↓↓↓
//     /**
// 	 * リクエストオブジェクトから現在のアプリケーションのベースURLを取得する。
// 	 * リクエストが初期化されていない場合はfalseを返す
// 	 *
// 	 * @static
// 	 * @param Zend_Controller_Request_Abstract $request リクエストオブジェクト。Zend_Controller_Request_Httpである必要がある
// 	 * @return string|false
// 	 */
// 	public static function getApplicationUrl($request) {
// 		if( $request == null ) return false;

//     	return ( preg_match('/^on$/i', $request->getServer('HTTPS')) ? 'https' : 'http' )
//     		. '://'
// 			. $request->getServer('HTTP_HOST')
//     		. $request->getBaseUrl();
// 	}
// BaseControllerActionのgetBaseUrl()に移植↑↑↑

// getPureParamsは廃止↓↓↓
// パラメータを取得する場合は、以下の方法を使うこと
//     $this->params()->fromPost('paramname');   // From POST
//     $this->params()->fromQuery('paramname');  // From GET
//     $this->params()->fromRoute('paramname');  // From RouteMatch
//     $this->params()->fromHeader('paramname'); // From header
//     $this->params()->fromFiles('paramname');  // From file being uploaded
//
// 	/**
// 	 * リクエストオブジェクトからモジュール・コントローラ・アクションの指定を取り除いた
// 	 * パラメータを抽出する
// 	 *
// 	 * @param RequestInterface $request リクエストオブジェクト
// 	 * @return array
// 	 */
// 	public static function getPureParams($request) {
// 		$result = array();
// 		if( $request instanceof Zend_Controller_Request_Abstract ) {
// 			$keys = array(
// 				$request->getModuleKey(),
// 				$request->getControllerKey(),
// 				$request->getActionKey()
// 			);
// 			foreach( $this->params()->fromPost() as $key => $value ) {
// 				if( in_array( $key, $keys ) ) continue;
// 				$result[ $key ] = $value;
// 			}
// 		}
// 		return $result;
// 	}
// getPureParamsは廃止↑↑↑

//未使用のため削除↓↓↓
// 	/**
// 	 * リクエストオブジェクトから、現在のリクエストの絶対URIを取得する。
// 	 * リクエストが初期化されていない場合はfalseを返す
// 	 *
// 	 * @static
// 	 * @param Zend_Controller_Request_Abstract $request リクエストオブジェクト。Zend_Controller_Request_Httpである必要がある
// 	 * @return string|false
// 	 */
// 	public static function getAbsoluteRequestUri($request) {
// 		if( $request == null ) return false;

// 		return ( preg_match( '/^on$/i', $request->getServer('HTTPS')) ? 'https' : 'http' )
// 			. '://'
// 			. $request->getServer('HTTP_HOST')
// 			. $request->getRequestUri();
// 	}
//未使用のため削除↑↑↑
}
