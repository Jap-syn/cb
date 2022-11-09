<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace oemorderpage;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\Feature\LocatorRegisteredInterface;
use Zend\Config\Reader\Ini;

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

    }

    /**
     * 未認証で、ホワイトリスト以外の処理は受け付けない処理を実装
     * ZF1の認証プラグインマネージャーと同等の挙動はする・・・はず
     * @param MvcEvent $event
     */
    public function boforeDispatch(MvcEvent $event){

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
                __NAMESPACE__ . '\Controller\Resource-image',
                __NAMESPACE__ . '\Controller\Resource-css',
                __NAMESPACE__ . '\Controller\Resource-favicon',
                __NAMESPACE__ . '\Controller\Sbpssettlement-pagecon',
        );

        $app = Application::getInstance();

        // 接続時間を設定する
        $rds_session_timezone = $event->getApplication()->getServiceManager()->get('config')['RDS_SESSION_TIMEZONE'];
        if (isset($rds_session_timezone)) {
            $app->dbAdapter->query('SET SESSION time_zone = :time_zone')->execute(array(':time_zone'=>$rds_session_timezone));
        }

        if (!$app->authManager->isAuthenticated()) {
            // 認証済みではない
            if (!in_array ( $requestedResourse, $whiteList )) {
                // ホワイトリストに入っていない

                // 未認証 かつ ホワイトリスト以外の場合、login/loginへフォワード
                $event->getRouteMatch()->setParam('controller', __NAMESPACE__ . '\Controller\Login')->setParam('action', 'login');
            }
        }
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
}
