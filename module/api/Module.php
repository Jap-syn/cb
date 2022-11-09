<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace api;

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
     * ディスパッチ直前の処理を実装
     *
     * @param MvcEvent $event
     */
    public function boforeDispatch(MvcEvent $event){

        $controller = $event->getRouteMatch ()->getParam ( 'controller' );
        $action = $event->getRouteMatch ()->getParam ( 'action' );

        // 自分と異なるモジュールの場合は処理しない
        $param = explode('\\', $controller);
        if ($param[0] != __NAMESPACE__){
            return;
        }

        // appを取得
        $app = Application::getInstance();

        // 接続時間を設定する
        $rds_session_timezone = $event->getApplication()->getServiceManager()->get('config')['RDS_SESSION_TIMEZONE'];
        if (isset($rds_session_timezone)) {
            $app->dbAdapter->query('SET SESSION time_zone = :time_zone')->execute(array(':time_zone'=>$rds_session_timezone));
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

}
