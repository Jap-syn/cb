<?php
namespace api;
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'router' => array(
            'routes' => array(
                    // The following is a route to simplify getting started creating
                    // new controllers and actions without needing to create a new
                    // module. Simply drop new controllers in, and you can access them
                    // using the path /application/:controller/:action
                    'api' => array(
                            'type'    => 'Literal',
                            'options' => array(
                                    'route'    => '/api',
                                    'defaults' => array(
                                            '__NAMESPACE__' => 'api\Controller',
                                            'controller'    => 'Error',
                                            'action'        => 'error',
                                    ),
                            ),
                            'may_terminate' => true,
                            'child_routes' => array(
                                    'default' => array(
                                            'type'    => 'Segment',
                                            'options' => array(
                                                    'route'    => '/[:controller[/:action]]',
                                                    'constraints' => array(
                                                            'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                                            'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                                                    ),
                                                    'defaults' => array(
                                                    ),
                                            ),
                                            'child_routes' => array(
                                                    'wildcard' => array(
                                                            'type'    => 'Wildcard',
                                                    ),
                                            ),
                                    ),
                            ),
                    ),

                    // -----------------------
                    // イーストア
                    // -----------------------
                    'estoreapi' => array(
                            'type'    => 'Literal',
                            'options' => array(
                                    'route'    => '/estore/api',
                                    'defaults' => array(
                                            '__NAMESPACE__' => 'api\Controller',
                                            'controller'    => 'Error',
                                            'action'        => 'error',
                                    ),
                            ),
                            'may_terminate' => true,
                            'child_routes' => array(
                                    'default' => array(
                                            'type'    => 'Segment',
                                            'options' => array(
                                                    'route'    => '/[:controller[/:action]]',
                                                    'constraints' => array(
                                                            'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                                            'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                                                    ),
                                                    'defaults' => array(
                                                    ),
                                            ),
                                            'child_routes' => array(
                                                    'wildcard' => array(
                                                            'type'    => 'Wildcard',
                                                    ),
                                            ),
                                    ),
                            ),
                    ),

                    // -----------------------
                    // SMBC
                    // -----------------------
                    'smbcfsapi' => array(
                            'type'    => 'Literal',
                            'options' => array(
                                    'route'    => '/smbcfs/api',
                                    'defaults' => array(
                                            '__NAMESPACE__' => 'api\Controller',
                                            'controller'    => 'Error',
                                            'action'        => 'error',
                                    ),
                            ),
                            'may_terminate' => true,
                            'child_routes' => array(
                                    'default' => array(
                                            'type'    => 'Segment',
                                            'options' => array(
                                                    'route'    => '/[:controller[/:action]]',
                                                    'constraints' => array(
                                                            'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                                            'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                                                    ),
                                                    'defaults' => array(
                                                    ),
                                            ),
                                            'child_routes' => array(
                                                'wildcard' => array(
                                                    'type'    => 'Wildcard',
                                                ),
                                            ),
                                    ),
                            ),
                    ),

                    // -----------------------
                    // セイノー
                    // -----------------------
                    'seino-financialapi' => array(
                            'type'    => 'Literal',
                            'options' => array(
                                    'route'    => '/seino-financial/api',
                                    'defaults' => array(
                                            '__NAMESPACE__' => 'api\Controller',
                                            'controller'    => 'Error',
                                            'action'        => 'error',
                                    ),
                            ),
                            'may_terminate' => true,
                            'child_routes' => array(
                                    'default' => array(
                                            'type'    => 'Segment',
                                            'options' => array(
                                                    'route'    => '/[:controller[/:action]]',
                                                    'constraints' => array(
                                                            'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                                            'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                                                    ),
                                                    'defaults' => array(
                                                    ),
                                            ),
                                            'child_routes' => array(
                                            'wildcard' => array(
                                            'type'    => 'Wildcard',
                                            ),
                                            ),
                                    ),
                            ),
                    ),
                    // -----------------------
                    // BASE
                    // -----------------------
                    'baseapi' => array(
                            'type'    => 'Literal',
                            'options' => array(
                                    'route'    => '/base/api',
                                    'defaults' => array(
                                            '__NAMESPACE__' => 'api\Controller',
                                            'controller'    => 'Error',
                                            'action'        => 'error',
                                    ),
                            ),
                            'may_terminate' => true,
                            'child_routes' => array(
                                    'default' => array(
                                            'type'    => 'Segment',
                                            'options' => array(
                                                    'route'    => '/[:controller[/:action]]',
                                                    'constraints' => array(
                                                            'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                                            'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                                                    ),
                                                    'defaults' => array(
                                                    ),
                                            ),
                                            'child_routes' => array(
                                            'wildcard' => array(
                                            'type'    => 'Wildcard',
                                            ),
                                            ),
                                    ),
                            ),
                    ),
                    // -----------------------
                    // テモナ
                    // -----------------------
                    'temonaapi' => array(
                            'type'    => 'Literal',
                            'options' => array(
                                    'route'    => '/temona/api',
                                    'defaults' => array(
                                            '__NAMESPACE__' => 'api\Controller',
                                            'controller'    => 'Error',
                                            'action'        => 'error',
                                    ),
                            ),
                            'may_terminate' => true,
                            'child_routes' => array(
                                    'default' => array(
                                            'type'    => 'Segment',
                                            'options' => array(
                                                    'route'    => '/[:controller[/:action]]',
                                                    'constraints' => array(
                                                            'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                                            'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                                                    ),
                                                    'defaults' => array(
                                                    ),
                                            ),
                                            'child_routes' => array(
                                            'wildcard' => array(
                                            'type'    => 'Wildcard',
                                            ),
                                            ),
                                    ),
                            ),
                    ),
                    // -----------------------
                    // みずほファクター
                    // -----------------------
                    'mizuhoapi' => array(
                            'type'    => 'Literal',
                            'options' => array(
                                    'route'    => '/mizuho/api',
                                    'defaults' => array(
                                            '__NAMESPACE__' => 'api\Controller',
                                            'controller'    => 'Error',
                                            'action'        => 'error',
                                    ),
                            ),
                            'may_terminate' => true,
                            'child_routes' => array(
                                    'default' => array(
                                            'type'    => 'Segment',
                                            'options' => array(
                                                    'route'    => '/[:controller[/:action]]',
                                                    'constraints' => array(
                                                            'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                                            'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                                                    ),
                                                    'defaults' => array(
                                                    ),
                                            ),
                                            'child_routes' => array(
                                            'wildcard' => array(
                                            'type'    => 'Wildcard',
                                            ),
                                            ),
                                    ),
                            ),
                    ),
                    // -----------------------
                    // トゥモロー総研株式会社
                    // -----------------------
                    'tri-paymentapi' => array(
                            'type'    => 'Literal',
                            'options' => array(
                                    'route'    => '/tri-payment/api',
                                    'defaults' => array(
                                            '__NAMESPACE__' => 'api\Controller',
                                            'controller'    => 'Error',
                                            'action'        => 'error',
                                    ),
                            ),
                            'may_terminate' => true,
                            'child_routes' => array(
                                    'default' => array(
                                            'type'    => 'Segment',
                                            'options' => array(
                                                    'route'    => '/[:controller[/:action]]',
                                                    'constraints' => array(
                                                            'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                                            'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                                                    ),
                                                    'defaults' => array(
                                                    ),
                                            ),
                                            'child_routes' => array(
                                            'wildcard' => array(
                                            'type'    => 'Wildcard',
                                            ),
                                            ),
                                    ),
                            ),
                    ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'api\Controller\Billing'    => 'api\Controller\BillingController',
            'api\Controller\Cancel'     => 'api\Controller\CancelController',
            'api\Controller\Delivlist'  => 'api\Controller\DelivlistController',
            'api\Controller\Detail'     => 'api\Controller\DetailController',
            'api\Controller\Error'      => 'api\Controller\ErrorController',
            'api\Controller\Idmap'      => 'api\Controller\IdmapController',
            'api\Controller\Jnummod'    => 'api\Controller\JnummodController',
            'api\Controller\OrderMod'   => 'api\Controller\OrderModController',
            'api\Controller\Modify'     => 'api\Controller\ModifyController',
            'api\Controller\Order'      => 'api\Controller\OrderController',
            'api\Controller\Payeasy'    => 'api\Controller\PayeasyController',
            'api\Controller\Shipping'   => 'api\Controller\ShippingController',
            'api\Controller\Sitemod'    => 'api\Controller\SitemodController',
            'api\Controller\Status'     => 'api\Controller\StatusController',
        ),
    ),
    'view_manager' => array(
        'template_map' => array(
            // 共通化する？　ここから
            //'error/404'                             => __DIR__ . '/../view/error/404.phtml',
            //'error/index'                           => __DIR__ . '/../view/error/index.phtml',
            //'layout/layout'                         => __DIR__ . '/../view/layout/layout.phtml',
            // 共通化する？　ここまで
        ),
        'template_path_stack' => array(
                __NAMESPACE__ => __DIR__ . '/../view',
        ),
    ),
);
