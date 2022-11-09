<?php
namespace oemadmin;
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
                    'oemadmin' => array(
                            'type'    => 'Literal',
                            'options' => array(
                                    'route'    => '/oemadmin',
                                    'defaults' => array(
                                            '__NAMESPACE__' => 'oemadmin\Controller',
                                            'controller'    => 'Login',
                                            'action'        => 'login',
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
                    'estoreadmin' => array(
                             'type'    => 'Literal',
                             'options' => array(
                                     'route'    => '/estore/admin',
                                    'defaults' => array(
                                            '__NAMESPACE__' => 'oemadmin\Controller',
                                            'controller'    => 'Login',
                                            'action'        => 'login',
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
                    'smbcfsadmin' => array(
                            'type'    => 'Literal',
                            'options' => array(
                                    'route'    => '/smbcfs/admin',
                                    'defaults' => array(
                                            '__NAMESPACE__' => 'oemadmin\Controller',
                                            'controller'    => 'Login',
                                            'action'        => 'login',
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
                    'seino-financialadmin' => array(
                            'type'    => 'Literal',
                            'options' => array(
                                    'route'    => '/seino-financial/admin',
                                    'defaults' => array(
                                            '__NAMESPACE__' => 'oemadmin\Controller',
                                            'controller'    => 'Login',
                                            'action'        => 'login',
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
                    'baseadmin' => array(
                            'type'    => 'Literal',
                            'options' => array(
                                    'route'    => '/base/admin',
                                    'defaults' => array(
                                            '__NAMESPACE__' => 'oemadmin\Controller',
                                            'controller'    => 'Login',
                                            'action'        => 'login',
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
                    'temonaadmin' => array(
                            'type'    => 'Literal',
                            'options' => array(
                                    'route'    => '/temona/admin',
                                    'defaults' => array(
                                            '__NAMESPACE__' => 'oemadmin\Controller',
                                            'controller'    => 'Login',
                                            'action'        => 'login',
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
                    'mizuhoadmin' => array(
                            'type'    => 'Literal',
                            'options' => array(
                                    'route'    => '/mizuho/admin',
                                    'defaults' => array(
                                            '__NAMESPACE__' => 'oemadmin\Controller',
                                            'controller'    => 'Login',
                                            'action'        => 'login',
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
                    'tri-paymentadmin' => array(
                            'type'    => 'Literal',
                            'options' => array(
                                    'route'    => '/tri-payment/admin',
                                    'defaults' => array(
                                            '__NAMESPACE__' => 'oemadmin\Controller',
                                            'controller'    => 'Login',
                                            'action'        => 'login',
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
            'oemadmin\Controller\Claim'         => 'oemadmin\Controller\ClaimController',
            'oemadmin\Controller\Enterprise'    => 'oemadmin\Controller\EnterpriseController',
            'oemadmin\Controller\Error'         => 'oemadmin\Controller\ErrorController',
            'oemadmin\Controller\Gp'            => 'oemadmin\Controller\GpController',
            'oemadmin\Controller\Index'         => 'oemadmin\Controller\IndexController',
            'oemadmin\Controller\Login'         => 'oemadmin\Controller\LoginController',
            'oemadmin\Controller\Monthly'       => 'oemadmin\Controller\MonthlyController',
            'oemadmin\Controller\Oem'           => 'oemadmin\Controller\OemController',
            'oemadmin\Controller\Operator'      => 'oemadmin\Controller\OperatorController',
            'oemadmin\Controller\Paying'        => 'oemadmin\Controller\PayingController',
            'oemadmin\Controller\Pdf'           => 'oemadmin\Controller\PdfController',
            'oemadmin\Controller\Resource'      => 'oemadmin\Controller\ResourceController',
            'oemadmin\Controller\Rworder'       => 'oemadmin\Controller\RworderController',
            'oemadmin\Controller\Rworderhist'   => 'oemadmin\Controller\RworderhistController',
            'oemadmin\Controller\Searche'       => 'oemadmin\Controller\SearcheController',
            'oemadmin\Controller\Searcho'       => 'oemadmin\Controller\SearchoController',
            'oemadmin\Controller\Site'          => 'oemadmin\Controller\SiteController',
        ),
    ),
    'view_manager' => array(
        'template_map' => array(
            // 共通化する？　ここから
            //'error/404'                             => __DIR__ . '/../view/error/404.phtml',
            //'error/index'                           => __DIR__ . '/../view/error/index.phtml',
            //'layout/layout'                         => __DIR__ . '/../view/layout/layout.phtml',
            // 共通化する？　ここまで
            __NAMESPACE__ . '/error/index.phtml'    => __DIR__ . '/../view/error/index.phtml',

            // components
            __NAMESPACE__ . '/document_header.php'          => __DIR__ . '/../view/components/document_header.php',
            __NAMESPACE__ . '/id_search_form.php'           => __DIR__ . '/../view/components/id_search_form.php',
            __NAMESPACE__ . '/important_messages.php'       => __DIR__ . '/../view/components/important_messages.php',
            __NAMESPACE__ . '/page_footer.php'              => __DIR__ . '/../view/components/page_footer.php',
            __NAMESPACE__ . '/page_header.php'              => __DIR__ . '/../view/components/page_header.php',
            __NAMESPACE__ . '/page_navigation.php'          => __DIR__ . '/../view/components/page_navigation.php',
            __NAMESPACE__ . '/claim/page_menu.php'          => __DIR__ . '/../view/components/claim/page_menu.php',
            __NAMESPACE__ . '/enterprise/page_menu.php'     => __DIR__ . '/../view/components/enterprise/page_menu.php',
            __NAMESPACE__ . '/gp/page_menu.php'             => __DIR__ . '/../view/components/gp/page_menu.php',
            __NAMESPACE__ . '/index/page_menu.php'          => __DIR__ . '/../view/components/index/page_menu.php',
            __NAMESPACE__ . '/login/page_header.php'        => __DIR__ . '/../view/components/login/page_header.php',
            __NAMESPACE__ . '/monthly/page_menu.php'        => __DIR__ . '/../view/components/monthly/page_menu.php',
            __NAMESPACE__ . '/oem/page_menu.php'            => __DIR__ . '/../view/components/oem/page_menu.php',
            __NAMESPACE__ . '/operator/page_menu2.php'      => __DIR__ . '/../view/components/operator/page_menu2.php',
            __NAMESPACE__ . '/paying/page_menu.php'         => __DIR__ . '/../view/components/paying/page_menu.php',
            __NAMESPACE__ . '/rworder/page_menu.php'        => __DIR__ . '/../view/components/rworder/page_menu.php',
            __NAMESPACE__ . '/searche/page_menu.php'        => __DIR__ . '/../view/components/searche/page_menu.php',
            __NAMESPACE__ . '/searcho/page_menu.php'        => __DIR__ . '/../view/components/searcho/page_menu.php',
            __NAMESPACE__ . '/searcho/page_navigation.php'        => __DIR__ . '/../view/components/searcho/page_navigation.php',
            __NAMESPACE__ . '/site/page_menu.php'           => __DIR__ . '/../view/components/site/page_menu.php',

            // scripts
            __NAMESPACE__ .'/claim/index.phtml'             => __DIR__ . '/../view/scripts/claim/index.phtml',
            __NAMESPACE__ .'/enterprise/detail.phtml'       => __DIR__ . '/../view/scripts/enterprise/detail.phtml',
            __NAMESPACE__ .'/enterprise/list.phtml'         => __DIR__ . '/../view/scripts/enterprise/list.phtml',
            __NAMESPACE__ .'/error/error.phtml'             => __DIR__ . '/../view/scripts/error/error.phtml',
            __NAMESPACE__ .'/error/nop.phtml'               => __DIR__ . '/../view/scripts/error/nop.phtml',
            __NAMESPACE__ .'/gp/mailtf.phtml'               => __DIR__ . '/../view/scripts/gp/mailtf.phtml',
            __NAMESPACE__ .'/gp/notice.phtml'               => __DIR__ . '/../view/scripts/gp/notice.phtml',
            __NAMESPACE__ .'/index/index.phtml'             => __DIR__ . '/../view/scripts/index/index.phtml',
            __NAMESPACE__ .'/login/autherror.phtml'         => __DIR__ . '/../view/scripts/login/autherror.phtml',
            __NAMESPACE__ .'/login/login.phtml'             => __DIR__ . '/../view/scripts/login/login.phtml',
            __NAMESPACE__ .'/login/chgpw_e.phtml'           => __DIR__ . '/../view/scripts/login/chgpw_e.phtml',
            __NAMESPACE__ .'/login/chgpw_f.phtml'           => __DIR__ . '/../view/scripts/login/chgpw_f.phtml',
            __NAMESPACE__ .'/monthly/cancel.phtml'          => __DIR__ . '/../view/scripts/monthly/cancel.phtml',
            __NAMESPACE__ .'/monthly/canceldetail.phtml'    => __DIR__ . '/../view/scripts/monthly/canceldetail.phtml',
            __NAMESPACE__ .'/monthly/settlement.phtml'      => __DIR__ . '/../view/scripts/monthly/settlement.phtml',
            __NAMESPACE__ .'/monthly/store.phtml'           => __DIR__ . '/../view/scripts/monthly/store.phtml',
            __NAMESPACE__ .'/monthly/storedetail.phtml'     => __DIR__ . '/../view/scripts/monthly/storedetail.phtml',
            __NAMESPACE__ .'/monthly/trading.phtml'         => __DIR__ . '/../view/scripts/monthly/trading.phtml',
            __NAMESPACE__ .'/monthly/keisansyo.phtml'       => __DIR__ . '/../view/scripts/monthly/keisansyo.phtml',
            __NAMESPACE__ .'/oem/completion.phtml'          => __DIR__ . '/../view/scripts/oem/completion.phtml',
            __NAMESPACE__ .'/oem/confirm.phtml'             => __DIR__ . '/../view/scripts/oem/confirm.phtml',
            __NAMESPACE__ .'/oem/detail.phtml'              => __DIR__ . '/../view/scripts/oem/detail.phtml',
            __NAMESPACE__ .'/oem/form.phtml'                => __DIR__ . '/../view/scripts/oem/form.phtml',
            __NAMESPACE__ .'/oem/save.phtml'                => __DIR__ . '/../view/scripts/oem/save.phtml',
            __NAMESPACE__ .'/operator/chgpw_e.phtml'        => __DIR__ . '/../view/scripts/operator/chgpw_e.phtml',
            __NAMESPACE__ .'/operator/chgpw_f.phtml'        => __DIR__ . '/../view/scripts/operator/chgpw_f.phtml',
            __NAMESPACE__ .'/operator/changepw_e2.phtml'       => __DIR__ . '/../view/scripts/operator/changepw_e2.phtml',
            __NAMESPACE__ .'/operator/changepw_f2.phtml'       => __DIR__ . '/../view/scripts/operator/changepw_f2.phtml',
            __NAMESPACE__ .'/paying/cnllist.phtml'          => __DIR__ . '/../view/scripts/paying/cnllist.phtml',
            __NAMESPACE__ .'/paying/dlist2.phtml'           => __DIR__ . '/../view/scripts/paying/dlist2.phtml',
            __NAMESPACE__ .'/paying/dlist3.phtml'           => __DIR__ . '/../view/scripts/paying/dlist3.phtml',
            __NAMESPACE__ .'/paying/elist.phtml'            => __DIR__ . '/../view/scripts/paying/elist.phtml',
            __NAMESPACE__ .'/paying/forecast2.phtml'        => __DIR__ . '/../view/scripts/paying/forecast2.phtml',
            __NAMESPACE__ .'/paying/list.phtml'             => __DIR__ . '/../view/scripts/paying/list.phtml',
            __NAMESPACE__ .'/paying/menu.phtml'             => __DIR__ . '/../view/scripts/paying/menu.phtml',
            __NAMESPACE__ .'/paying/stamplist.phtml'        => __DIR__ . '/../view/scripts/paying/stamplist.phtml',
            __NAMESPACE__ .'/paying/trnlist.phtml'          => __DIR__ . '/../view/scripts/paying/trnlist.phtml',
            __NAMESPACE__ .'/paying/paybacklist.phtml'      => __DIR__ . '/../view/scripts/paying/paybacklist.phtml',
            __NAMESPACE__ .'/pdf/monthlysettlement.phtml'   => __DIR__ . '/../view/scripts/pdf/monthlysettlement.phtml',
            __NAMESPACE__ .'/rworder/detail.phtml'          => __DIR__ . '/../view/scripts/rworder/detail.phtml',
            __NAMESPACE__ . '/rworderhist/list.phtml'       => __DIR__ . '/../view/scripts/rworderhist/list.phtml',
            __NAMESPACE__ . '/rworderhist/detail.phtml'     => __DIR__ . '/../view/scripts/rworderhist/detail.phtml',
            __NAMESPACE__ .'/searche/form.phtml'            => __DIR__ . '/../view/scripts/searche/form.phtml',
            __NAMESPACE__ .'/searche/search.phtml'          => __DIR__ . '/../view/scripts/searche/search.phtml',
            __NAMESPACE__ .'/searcho/form.phtml'            => __DIR__ . '/../view/scripts/searcho/form.phtml',
            __NAMESPACE__ .'/searcho/qform.phtml'           => __DIR__ . '/../view/scripts/searcho/qform.phtml',
            __NAMESPACE__ .'/searcho/search.phtml'          => __DIR__ . '/../view/scripts/searcho/search.phtml',
            __NAMESPACE__ .'/site/list.phtml'               => __DIR__ . '/../view/scripts/site/list.phtml',
        ),
        'template_path_stack' => array(
                __NAMESPACE__ => __DIR__ . '/../view',
        ),
    ),
);
