<?php
namespace Coral\Coral\Controller;

use Coral\Coral\View\CoralViewModel;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\Controller\AbstractController;
use Zend\View\Model\ViewModel;
use Zend\Mvc\MvcEvent;

use Zend\Log\Writer\Stream;
use Zend\Log\Logger;
use Zend\Http\Request;
use Zend\Debug\Debug;

use Zend\Stdlib\RequestInterface;

/**
 * 共通コントローラー
 */
abstract class CoralControllerAction extends AbstractActionController
{

	//---------------------
	// 定数・定義
	//---------------------
	const C_LAYOUT_KEY_BASE_URL = 'baseUrl';
	const C_LAYOUT_KEY_CURRENT_ACTION = 'currentAction';
	const C_LAYOUT_KEY_JAVA_SCRIPTS = 'javaScripts';
	const C_LAYOUT_KEY_STYLE_SHEETS = 'styleSheets';
	const C_LAYOUT_KEY_PAGE_TITLE   = 'pageTitle';
	const C_LAYOUT_DEFAULT_PHTML = 'layout/%s/%s/layout'; // %s/%sは、モジュール名/コントローラ名
	const C_ROUTER_DEFAULT = '/default/wildcard';  // default/wildcardは固定'


	//---------------------
	// 変数
	//---------------------
	private $_actionName;
	private $_controllerName;
	private $_javaScripts;	// JavaScript
	private $_layoutPath;
	private $_moduleName;
	private $_pageTitle;	// Title
	private $_styleSheets;	// StyleSheet
	private $_oemName;
	private $_oemType;
	protected $view;

	/**
	 * コンストラクタ
	 */
	function __construct()
	{

		// 初期化
		$this->clearJavaScripts();
		$this->clearStyleSheets();
		$this->setPageTitle('');

		// ViewModelを作成
		$this->view = new CoralViewModel();
		//$this->view->setTerminal(true); // layout機能は使わない→layoutファイルを空にする

		// 派生クラスの初期化処理をCall
		$this->_init();

	}

	/**
	 * 派生クラス初期化処理
	 * 派生クラスにて実装必須
	 */
	abstract protected function _init();

	/**
	 * ZF1リダイレクト互換関数
	 * @param string $prmUrl リダイレクトするURL(controller/action)を指定
	 * @return \Zend\Http\Response
	 */
	public function _redirect($prmUrl)
	{
	    // 変数初期化
	    $url = explode('/', $prmUrl);
        $route = '';
	    $route_array = array();
	    $key = '';

	    // どのルーターを使うか指定
//        $route = $this->_moduleName . self::C_ROUTER_DEFAULT;  // default/wildcardは固定
        if (is_null($this->_oemName)) {
            $route = $this->_moduleName . self::C_ROUTER_DEFAULT;  // default/wildcardは固定
        } else {
            $route = $this->_oemName . $this->_oemType . self::C_ROUTER_DEFAULT;  // default/wildcardは固定
        }

        for ($i = 0; $i < count($url); $i++){
            if ($i == 0){
                // --------------------
                // コントローラ
                // --------------------
                $route_array['controller']  = $url[$i];

            }elseif($i == 1){
                // --------------------
                // アクション
                // --------------------
                $route_array['action']      = $url[$i];

            }else {
                // --------------------
                // パラメーター
                // --------------------
                if ($i % 2 == 0){
                    // キー
                    $key = $url[$i];

                }else{
                    // 値
                    $route_array[$key]      = $url[$i];

                }
            }

        }

	    return $this->redirect()->toRoute($route, $route_array);
	}

    /**
     * ZF1フォワード互換関数
     * 注意：フォワードする際は、アクションメソッドにて、
     *     ：この関数の戻り値をReturnしてください。
     * ex) return $this->_forward('login');
     * @param string $prmAction フォワードするアクションを指定
     * @param array $prmParameter フォワード先に渡すRouteパラメーターを設定
     * @return \Coral\Coral\View\CoralViewModel
     */
    public function _forward($prmAction, $prmParameter = array())
    {

        $param = $this->params()->fromRoute();

        // 引数にパラメーターが指定されていた場合は、上書き
        $param = array_merge($param, $prmParameter);

        // フォワード第1引数に渡すControllerNameを取得
        $controller = $param['controller'];

        // フォワード第2引数に渡すRouteパラメーターを作成
        $param = array_merge($param , array('action' => $prmAction) );

        // フォワード
        return $this->forward()->dispatch($controller, $param );
    }

	/**
	 * Layoutに指定されたJavaScriptを追加します
	 * @param string $prmJavaScript
	 */
	protected function addJavaScript($prmJavaScript = null)
	{
		if ($prmJavaScript == null)
		{
			return;
		}
		array_push($this->_javaScripts, $prmJavaScript);

		return $this;

	}

	/**
	 * Layoutに指定されたスタイルシートを追加します。
	 * @param string $prmStyleSheet
	 */
	protected function addStyleSheet($prmStyleSheet = null)
	{
		if ($prmStyleSheet == null)
		{
			return;
		}
		array_push($this->_styleSheets, $prmStyleSheet);

		return $this;
	}

	/**
	 * preDispatch、postDispatchの紐付けを行うリスナーです
	 * @see \Zend\Mvc\Controller\AbstractController::attachDefaultListeners()
	 */
	protected function attachDefaultListeners()
	{
		parent::attachDefaultListeners();

		$events = $this->getEventManager();
		$events->attach('dispatch', array($this, 'preDispatch'), 100);
		$events->attach('dispatch', array($this, 'postDispatch'), -100);
	}

	/**
	 * Layoutに指定したJavaScriptを初期化します。
	 */
	protected function clearJavaScripts()
	{
		$this->_javaScripts = array();

		return $this;
	}

	/**
	 * Layoutに指定したスタイルシートを初期化します。
	 */
	protected function clearStyleSheets()
	{
		$this->_styleSheets = array();

		return $this;
	}

	/**
	 * アクション名を取得します
	 * @return \Zend\Mvc\Router\mixed
	 */
	protected function getActionName(){
		return $this->_actionName;
	}

	/**
	 * ベースURLを取得します（ex. http://localhost/cb/public）
	 * @return string
	 */
	protected function getBaseUrl()
	{
		$uri = $this->getRequest()->getUri();

		// http を取得
		$scheme = $uri->getScheme();

		// localhost を取得
		$host = $uri->getHost();

		// /cb/public/ を取得
		$route = $this->url()->fromRoute('home');

        ////最後の/を削除
        //$route = substr($route, 0, -1);

//        $base = sprintf('%s://%s%s%s', $scheme, $host, $route , $this->_moduleName );
        if (is_null($this->_oemName)) {
            $base = sprintf('%s://%s%s%s', $scheme, $host, $route , $this->_moduleName );
        } else {
            $base = sprintf('%s://%s%s%s', $scheme, $host, $route , $this->_oemName . "/" . $this->_oemType );
        }
		return $base;
	}

	/**
	 * コントローラー名を取得します
	 */
	protected function getControllerName(){
		return $this->_controllerName;
	}

	/**
	 * 指定している配列の値を取得します。
	 * @return array:
	 */
	protected function getLayoutParams()
	{
		$params = array();

		// JavaScriptパラメーターをマージ
		$params = array_merge(
				$params ,
				array(self::C_LAYOUT_KEY_JAVA_SCRIPTS => $this->_javaScripts)
		);

		// StyleSheetパラメーターをマージ
		$params = array_merge(
				$params ,
				array(self::C_LAYOUT_KEY_STYLE_SHEETS => $this->_styleSheets)
		);

		// タイトルをマージ
		$params = array_merge(
				$params ,
				array(self::C_LAYOUT_KEY_PAGE_TITLE => $this->_pageTitle)
		);

		// アクション名をマージ
		$params = array_merge(
				$params ,
				array(self::C_LAYOUT_KEY_CURRENT_ACTION => $this->_actionName)
		);

		// baseURLをマージ
		$params = array_merge(
				$params ,
				array(self::C_LAYOUT_KEY_BASE_URL => $this->getBaseUrl())
		);

		return $params;
	}

	/**
	 * モジュール名を取得します
	 */
	protected function getModuleName(){
		return $this->_moduleName;
	}

	/**
	 * NAMESPACE・コントローラ・アクションの指定を取り除いた
	 * パラメータを抽出する
	 *
	 * @return array
	 */
	public function getPureParams() {
	    // ZF2の機構により乗ってしまうリクエストパラメーターは除外し、
	    // 純粋に、画面からの入力パラメーターのみを取得する
        $result = array();

        $keys = array(
                '__NAMESPACE__',
                'controller',
                'action',
                '__CONTROLLER__',
        );

        foreach( $this->getParams() as $key => $value ) {
            if( in_array( $key, $keys ) ) continue;
            $result[ $key ] = $value;
        }

	    return $result;
	}

	/**
	 * POST、GET、Route
	 * すべてのパラメーターをマージした結果を配列で返す
	 * @return array
	 */
	public function getParams()
	{
	    $params = array();
        $params = array_merge($params, $this->params()->fromPost());   // From POST
        $params = array_merge($params, $this->params()->fromQuery());   // From GET
        $params = array_merge($params, $this->params()->fromRoute());   // From RouteMatch
        return $params;
	}

	/**
	 * 各アクション実行前に実行されます
	 */
	public function onBeforeAction()
	{
	    // 子コントローラーでオーバーライドしてください
	}

	/**
	 * 各アクション実行後に実行されます
	 */
	public function onAfterAction()
	{
	    // 子コントローラーでオーバーライドしてください
	}

	/**
	 * Action処理の前に実行される初期化処理です。
	 * @param MvcEvent $e
	 */
	public function preDispatch(MvcEvent $e)
	{
	    // モジュール名、コントローラー名、アクション名を取得します。
		$param = strtolower($this->getEvent()->getRouteMatch()->getParam('controller'));
		$param = explode('\\', $param);
		$this->_moduleName = $param[0];
		$this->_controllerName = $param[2];
		$this->_actionName = $this->getEvent()->getRouteMatch()->getParam('action', 'NA');
		if ($this->isOemAccess()) {
		    preg_match('/^\/([^\/]+)\/([^\/]+)\/?/', $_SERVER['REQUEST_URI'], $matches);
		    if (count($matches) > 0) {
		        if ($matches[1] != "oemadmin") {
		            $this->_oemName = $matches[1];
		            $this->_oemType = $matches[2];
		        }
		    }
		}

		// viewを設定
		//$this->view->setTemplate(sprintf('/scripts/%s/%s.phtml' ,$this->_controllerName ,$this->_actionName));
		$this->setTemplate($this->_actionName);

		// 子コントローラーの各アクション実行前の処理を実行
        $this->onBeforeAction();
	}

	/**
	 * Action処理の後に実行される初期化処理です。
	 * @param MvcEvent $e
	 */
	public function postDispatch(MvcEvent $e)
	{
        $this->view->setVariables($this->getLayoutParams());

		// 子コントローラーの各アクション実行後の処理を実行
        $this->onAfterAction();
	}

	/**
	 * Layoutに指定されたJavaScriptを設定します。
	 * @param array $prmJavaScripts
	 */
	protected function setJavaScripts($prmJavaScripts = null)
	{
		if ($prmJavaScripts == null)
		{
			return;
		}
		$this->_javaScripts = $prmJavaScripts;

		return $this;
	}

	/**
	 * タイトルを設定します。
	 * @param string $prmPageTitle
	 */
	protected function setPageTitle($prmPageTitle = '')
	{
		$this->_pageTitle = $prmPageTitle;

		return $this;
	}

	/**
	 * タイトルを取得します。
	 * @return string タイトル
	 */
	protected function getPageTitle()
	{
	    return $this->_pageTitle;
	}

	/**
	 * 指定されたスタイルシートを設定します。
	 * @param array $prmStyleSheets
	 */
	protected function setStyleSheets($prmStyleSheets = null)
	{
		if ($prmStyleSheets == null)
		{
			return $this;
		}
		$this->_styleSheets = $prmStyleSheets;

		return $this;
	}

	/**
	 * 指定されたテンプレートを設定します。
	 * @param string $prmName テンプレート名（XXX.phtml）
	 */
	public function setTemplate($prmName = null)
	{
	    if ($prmName == null)
	    {
	        return;
	    }

        if (($this->_moduleName == "mypage" || $this->_moduleName == "orderpage"|| $this->_moduleName == "oemmypage" || $this->_moduleName == "oemorderpage") && $this->is_mobile_request())
        {
            $this->view->setTemplate(sprintf('%s/%s/%s_sp.phtml' ,$this->_moduleName, $this->_controllerName ,$prmName));
        }
        else
        {
            $this->view->setTemplate(sprintf('%s/%s/%s.phtml' ,$this->_moduleName, $this->_controllerName ,$prmName));
        }
	}

	/**
	 * スマートフォン判定
	 * @return boolean
	 */
	protected function is_mobile_request()
	{
	    $_SERVER['ALL_HTTP'] = isset($_SERVER['ALL_HTTP']) ? $_SERVER['ALL_HTTP'] : '';
	    $mobile_browser = '0';
	    if(preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|iphone|ipad|ipod|android|xoom)/i', strtolower($_SERVER['HTTP_USER_AGENT'])))
	        $mobile_browser++;
	    if((isset($_SERVER['HTTP_ACCEPT'])) and (strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml') !== false))
	        $mobile_browser++;
	    if(isset($_SERVER['HTTP_X_WAP_PROFILE']))
	        $mobile_browser++;
	    if(isset($_SERVER['HTTP_PROFILE']))
	        $mobile_browser++;
	    $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'],0,4));
	    $mobile_agents = array(
	            'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',
	            'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
	            'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
	            'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
	            'newt','noki','oper','palm','pana','pant','phil','play','port','prox',
	            'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',
	            'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',
	            'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',
	            'wapr','webc','winw','winw','xda','xda-'
	    );
	    if(in_array($mobile_ua, $mobile_agents))
	        $mobile_browser++;
	    if(strpos(strtolower($_SERVER['ALL_HTTP']), 'operamini') !== false)
	        $mobile_browser++;
	    // Pre-final check to reset everything if the user is on Windows
	    if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows') !== false)
	        $mobile_browser=0;
	    // But WP7 is also Windows, with a slightly different characteristic
	    if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows phone') !== false)
	        $mobile_browser++;
	    if($mobile_browser>0)
	        return true;
	    else
	        return false;
	}

	/**
	 * モジュール固有の設定情報（module.config.php）を取得します
	 */
	public function getIniConfig()
	{

	    $module = $this->getServiceLocator()->get($this->_moduleName . '\Module');
	    return $module->getIniFile();
	}

	/**
	 * OEMによるアクセスであるか否か
	 * マイページでは本関数をオーバーライドする
	 */
	protected function isOemAccess()
	{
	    if ($this->_moduleName == "oemadmin" || $this->_moduleName == "oemmember" || $this->_moduleName == "oemmypage" || $this->_moduleName == "oemorderpage") {
	        return true;
	    }
	    return false;
	}

}