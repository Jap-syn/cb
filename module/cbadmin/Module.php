<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace cbadmin;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\Feature\LocatorRegisteredInterface;
use Zend\Config\Reader\Ini;
use Zend\EventManager\Event;
use Zend\Mvc\Application as ZendApplication;

class Module implements LocatorRegisteredInterface
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $eventManager->attach(MvcEvent::EVENT_DISPATCH, array(
                $this,
                'boforeDispatch'
        ), 101);

        $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, array(
                $this,
                'dispatchError'
        ), -200);

        $eventManager->attach(MvcEvent::EVENT_RENDER_ERROR, array(
                $this,
                'dispatchError'
        ), -200);

    }

    /**
     * 未認証で、ホワイトリスト以外の処理は受け付けない処理を実装
     * ZF1の認証プラグインマネージャーと同等の挙動はする・・・はず
     * @param MvcEvent $event
     */
    public function boforeDispatch(MvcEvent $event){

        /**
         * @var $request \Zend\Http\PhpEnvironment\Request
         */
        $request = $event->getRequest();

        $controller = $event->getRouteMatch ()->getParam ( 'controller' );
        $action = $event->getRouteMatch ()->getParam ( 'action' );

        // 自分と異なるモジュールの場合は処理しない
        $param = explode('\\', $controller);
        if ($param[0] != __NAMESPACE__){
            return;
        }

        $requestedResourse = $controller . "-" . $action;

        // ホワイトリストのアクセスのみ許可する
        // ホワイトリストの指定 [controller]-[action]
        $whiteList = array (
                __NAMESPACE__ . '\Controller\Login-login',
                __NAMESPACE__ . '\Controller\Login-auth',
                __NAMESPACE__ . '\Controller\Login-logout',
                __NAMESPACE__ . '\Controller\AccountReport-executedairy',
                __NAMESPACE__ . '\Controller\AccountReport-executemonthly'
        );

        $whiteList2 = array (
                __NAMESPACE__ . '\Controller\Login-login',
                __NAMESPACE__ . '\Controller\Login-auth',
                __NAMESPACE__ . '\Controller\Login-logout',
        );
        // appを取得
        $app = Application::getInstance();

        // 接続時間を設定する
        $rds_session_timezone = $event->getApplication()->getServiceManager()->get('config')['RDS_SESSION_TIMEZONE'];
        if (isset($rds_session_timezone)) {
            $app->dbAdapter->query('SET SESSION time_zone = :time_zone')->execute(array(':time_zone'=>$rds_session_timezone));
        }

        $userId = null;
        $userName = null;

        if (!$app->authManagerAdmin->isAuthenticated()) {
            // 認証済みではない
            if (!in_array ( $requestedResourse, $whiteList )) {
                // ホワイトリストに入っていない

                // 未認証 かつ ホワイトリスト以外の場合、login/loginへフォワード
                $event->getRouteMatch()->setParam('controller', __NAMESPACE__ . '\Controller\Login')->setParam('action', 'login');
            }
        } else {
            // 認証済み
            $tblUser = new \models\Table\TableUser($app->dbAdapter);

            // ユーザーIDの取得
            $userId = $tblUser->getUserId(0, $app->authManagerAdmin->getUserInfo()->OpId);

            /// ユーザー名の取得
            $userName = $tblUser->getUserName($userId);

            $LoginId = $app->authManagerAdmin->getUserInfo()->LoginId;
            $tblPass = new \models\Table\TablePasswordHistory($app->dbAdapter);
            $passData = $tblPass->findnew(1, $LoginId)->current();
            // count関数対策
            if((empty($passData) || $passData['PasswdLimitDay'] < date('Y-m-d')) && !in_array ( $requestedResourse, $whiteList2 )){
                $event->getRouteMatch()->setParam('controller', __NAMESPACE__ . '\Controller\Login')->setParam('action', 'chgpw');
            }

        }

        // リクエスト情報を取得し、永続化する操作ログを構成する
        $opConf = $this->getOperationConfigFile();

        if ( isset($opConf[$requestedResourse]) ) {
            // 操作ログ取得対象の場合

            // パラメーターをマージ
            $qParams = $request->getQuery()->toArray();        // GETパラメーター
            $pParams = $request->getPost()->toArray();         // POSTパラメーター
            $rParams = $event->getRouteMatch ()->getParams();  // ROUTEパラメーター
            $parameter = array_merge($qParams, $pParams, $rParams);

            // 特定画面のパスワード入力だけはマスクする
            isset($parameter['op_passwd']) ? $parameter['op_passwd'] = '***' : null ;
            isset($parameter['opw'])       ? $parameter['opw']       = '***' : null ;
            isset($parameter['npw1'])      ? $parameter['npw1']      = '***' : null ;
            isset($parameter['npw2'])      ? $parameter['npw2']      = '***' : null ;

            // サーバー情報を取得
            $server = $request->getServer()->toArray();

            // DB保存
            $tblOpLog = new \models\Table\TableOperationLog($app->dbAdapter);

            // 登録データを作成
            $newdata = array(
                    'Module'            => __NAMESPACE__,
                    'OperationTime'     => isset($server['REQUEST_TIME_FLOAT']) ? date('Y-m-d H:i:s', $server['REQUEST_TIME_FLOAT']) : date('Y-m-d H:i:s'),
                    'OperationContent'  => isset($opConf[$requestedResourse]) ? $opConf[$requestedResourse] : null,
                    'Url'               => $request->getUriString(),
                    'Paramter'          => json_encode($parameter, JSON_UNESCAPED_UNICODE) , // Zend\Jsonでは、DB保存時に日本語のまま保存出来ないため、標準の関数を使用
                    'UserId'            => $userId,
                    'UserName'          => $userName,
                    'IPAddress'         => isset($server['HTTP_X_FORWARDED_FOR']) ? $server['HTTP_X_FORWARDED_FOR'] : $server['REMOTE_ADDR'],
                    'Note'              => null,     // 予備項目（JSON想定）
                    'RegistId'          => $userId,
            );

            // INSERT
            $tblOpLog->saveNew($newdata);

        }

    }

    /**
     * エラー画面をモジュール単位で独自に制御するための処理
     * @param MvcEvent $event
     */
    public function dispatchError(MvcEvent $event){
        // エラーじゃなければ論外
        if (!$event->isError()) {
            return;
        }

        /**
         * @var $request \Zend\Http\PhpEnvironment\Request
         */
        $request = $event->getRequest();

        // routeが取得できない場合は処理しない
        $route = $event->getRouteMatch();
        if (!$route ){
            return;
        }
        $controller = $route->getParam ( 'controller' );

        // 自分と異なるモジュールの場合は処理しない
        $param = explode('\\', $controller);
        if ($param[0] != __NAMESPACE__){
            return;
        }

        // エラーページの設定
        // エラー画面に渡すパラーメーターの作りこみ
        $variables = array(
            'exception' => $event->getParam('exception'),
            'baseUrl' => $this->getBaseUrl($event),
            'styleSheets' => array(0 => "../css/default02.css"),
            'javaScripts' => array(),
            'pageTitle' => 'エラーが発生しました',
            'userInfo' => Application::getInstance()->authManagerAdmin->getUserInfo(),
        );
        $viewModel = $event->getViewModel();
        $viewModel->setTemplate(__NAMESPACE__ . '/error/index.phtml');
        $viewModel->setVariables($variables);

        // エラーログ出力
        Application::getInstance()->logger->err($event->getParam('exception')->getMessage());
        Application::getInstance()->logger->err($event->getParam('exception')->getTraceAsString());
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                    __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getIniFile()
    {
        //TODO config.iniを読み込んで返す
        $data = array();
        $file = __DIR__ . '/config/config.ini';
        if (file_exists($file))
        {
            $reader = new Ini();
            $data = $reader->fromFile($file);
        }
        return $data;
    }

    public function getOperationConfigFile()
    {
        // operation.config.php を読み込んで返す
        $file = __DIR__ . '/config/operation.config.php';
        if (file_exists($file))
        {
            return include $file;
        }
        return array();
    }

    /**
     * ベースURLを取得します（ex. http://localhost/cb/public）
     * @return string
     */
    protected function getBaseUrl($event)
    {
        $uri = $event->getRequest()->getUri();

        // http を取得
        $scheme = $uri->getScheme();

        // localhost を取得
        $host = $uri->getHost();

        // /cb/public/ を取得
        $route = $event->getRequest()->getBaseUrl();

        $module = '/' . __NAMESPACE__;

        $base = sprintf('%s://%s%s%s', $scheme, $host, $route ,$module );
        return $base;
    }

}

