<?php
namespace oemorderpage;
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
            'oemorderpage' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/oemorderpage',
                    'defaults' => array(
                            '__NAMESPACE__' => 'oemorderpage\Controller',
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
            'estoreorderpage' => array(
                    'type'    => 'Literal',
                    'options' => array(
                            'route'    => '/estore/orderpage',
                            'defaults' => array(
                                    '__NAMESPACE__' => 'oemorderpage\Controller',
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
            'smbcfsorderpage' => array(
                    'type'    => 'Literal',
                    'options' => array(
                            'route'    => '/smbcfs/orderpage',
                            'defaults' => array(
                                    '__NAMESPACE__' => 'oemorderpage\Controller',
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
            // SMBC2
            // -----------------------
            'sforderpage' => array(
                    'type'    => 'Literal',
                    'options' => array(
                            'route'    => '/sf/orderpage',
                            'defaults' => array(
                                    '__NAMESPACE__' => 'oemorderpage\Controller',
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
            'seino-financialorderpage' => array(
                    'type'    => 'Literal',
                    'options' => array(
                            'route'    => '/seino-financial/orderpage',
                            'defaults' => array(
                                    '__NAMESPACE__' => 'oemorderpage\Controller',
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
            'baseorderpage' => array(
                    'type'    => 'Literal',
                    'options' => array(
                            'route'    => '/base/orderpage',
                            'defaults' => array(
                                    '__NAMESPACE__' => 'oemorderpage\Controller',
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
            'mizuhoorderpage' => array(
                    'type'    => 'Literal',
                    'options' => array(
                            'route'    => '/mizuho/orderpage',
                            'defaults' => array(
                                    '__NAMESPACE__' => 'oemorderpage\Controller',
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
            'tri-paymentorderpage' => array(
                    'type'    => 'Literal',
                    'options' => array(
                            'route'    => '/tri-payment/orderpage',
                            'defaults' => array(
                                    '__NAMESPACE__' => 'oemorderpage\Controller',
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
            'oemorderpage\Controller\Login'                => 'oemorderpage\Controller\LoginController',
            'oemorderpage\Controller\Index'                => 'oemorderpage\Controller\IndexController',
            'oemorderpage\Controller\BillReIss'            => 'oemorderpage\Controller\BillReIssController',
            'oemorderpage\Controller\Sbpssettlement'       => 'oemorderpage\Controller\SbpssettlementController',
            'oemorderpage\Controller\Resource'             => 'oemorderpage\Controller\ResourceController',
            'oemorderpage\Controller\CreditSettlement'     => 'oemorderpage\Controller\CreditSettlementController',
            'oemorderpage\Controller\ReceiptPdfOutput'     => 'oemorderpage\Controller\ReceiptPdfOutputController',
        ),
    ),
    'view_manager' => array(
        'template_map' => array(
            // 共通化する？　ここから
            'error/404'                                 => __DIR__ . '/../view/error/404.phtml',
            'error/index'                               => __DIR__ . '/../view/error/index.phtml',
            'layout/layout'                             => __DIR__ . '/../view/layout/layout.phtml',
            // 共通化する？　ここまで
            // components
            __NAMESPACE__ . '/document_header.php'      => __DIR__ . '/../view/components/document_header.php',
            __NAMESPACE__ . '/page_footer.php'          => __DIR__ . '/../view/components/page_footer.php',
            __NAMESPACE__ . '/page_header.php'          => __DIR__ . '/../view/components/page_header.php',
            __NAMESPACE__ . '/system_message.php'       => __DIR__ . '/../view/components/system_message.php',
            __NAMESPACE__ . '/menu.php'                 => __DIR__ . '/../view/components/menu.php',
            __NAMESPACE__ . '/document_header.php'      => __DIR__ . '/../view/components/document_header.php',
            __NAMESPACE__ . '/common_assign.php'        => __DIR__ . '/../view/components/common_assign.php',
            __NAMESPACE__ . '/login/page_header.php'    => __DIR__ . '/../view/components/login/page_header.php',
            __NAMESPACE__ . '/index/peyment_credit.php' => __DIR__ . '/../view/components/index/peyment_credit.php',
            __NAMESPACE__ . '/creditsettlement/page_header.php'    => __DIR__ . '/../view/components/creditsettlement/page_header.php',
            __NAMESPACE__ . '/creditsettlement/page_footer.php'    => __DIR__ . '/../view/components/creditsettlement/page_footer.php',
            __NAMESPACE__ . '/receiptpdfoutput/page_header.php'    => __DIR__ . '/../view/components/receiptpdfoutput/page_header.php',
            __NAMESPACE__ . '/receiptpdfoutput/page_footer.php'    => __DIR__ . '/../view/components/receiptpdfoutput/page_footer.php',

            // scripts
            __NAMESPACE__ . '/index/index.phtml'        => __DIR__ . '/../view/scripts/index/index.phtml',
            __NAMESPACE__ . '/login/login.phtml'        => __DIR__ . '/../view/scripts/login/login.phtml',
            __NAMESPACE__ . '/login/invalid.phtml'        => __DIR__ . '/../view/scripts/login/invalid.phtml',
            __NAMESPACE__ . '/billreiss/index.phtml'    => __DIR__ . '/../view/scripts/billreiss/index.phtml',
            __NAMESPACE__ . '/billreiss/confirm.phtml'  => __DIR__ . '/../view/scripts/billreiss/confirm.phtml',
            __NAMESPACE__ . '/billreiss/completion.phtml'   => __DIR__ . '/../view/scripts/billreiss/completion.phtml',
            __NAMESPACE__ . '/billreiss/error.phtml'    => __DIR__ . '/../view/scripts/billreiss/error.phtml',
            __NAMESPACE__ . '/sbpssettlement/index.phtml'     => __DIR__ . '/../view/scripts/sbpssettlement/index.phtml',
            __NAMESPACE__ . '/sbpssettlement/error.phtml'  => __DIR__ . '/../view/scripts/sbpssettlement/error.phtml',
            __NAMESPACE__ . '/creditsettlement/input.phtml'     => __DIR__ . '/../view/scripts/creditsettlement/input.phtml',
            __NAMESPACE__ . '/creditsettlement/confirm.phtml'   => __DIR__ . '/../view/scripts/creditsettlement/confirm.phtml',
            __NAMESPACE__ . '/creditsettlement/complete.phtml'  => __DIR__ . '/../view/scripts/creditsettlement/complete.phtml',
            __NAMESPACE__ . '/creditsettlement/error.phtml'  => __DIR__ . '/../view/scripts/creditsettlement/error.phtml',
            __NAMESPACE__ . '/receiptpdfoutput/receiptpreview.phtml'   => __DIR__ . '/../view/scripts/receiptpdfoutput/receiptpreview.phtml',
            __NAMESPACE__ . '/receiptpdfoutput/receipt.phtml'   => __DIR__ . '/../view/scripts/receiptpdfoutput/receipt.phtml',
            __NAMESPACE__ . '/receiptpdfoutput/error.phtml'     => __DIR__ . '/../view/scripts/receiptpdfoutput/error.phtml',

            // smart phone共通化する？　ここから
            'error/404_sp'                                 => __DIR__ . '/../view_sp/error/404.phtml',
            'error/index_sp'                               => __DIR__ . '/../view_sp/error/index.phtml',
            'layout/layout_sp'                             => __DIR__ . '/../view_sp/layout/layout.phtml',
            // 共通化する？　ここまで
            // smart phonenoのcomponents
            __NAMESPACE__ . '/document_header_sp.php'      => __DIR__ . '/../view_sp/components/document_header.php',
            __NAMESPACE__ . '/page_footer_sp.php'          => __DIR__ . '/../view_sp/components/page_footer.php',
            __NAMESPACE__ . '/page_header_sp.php'          => __DIR__ . '/../view_sp/components/page_header.php',
            __NAMESPACE__ . '/system_message_sp.php'       => __DIR__ . '/../view_sp/components/system_message.php',
            __NAMESPACE__ . '/menu_sp.php'                 => __DIR__ . '/../view_sp/components/menu.php',
            __NAMESPACE__ . '/document_header_sp.php'      => __DIR__ . '/../view_sp/components/document_header.php',
            __NAMESPACE__ . '/common_assign_sp.php'        => __DIR__ . '/../view_sp/components/common_assign.php',
            __NAMESPACE__ . '/login/page_header_sp.php'    => __DIR__ . '/../view_sp/components/login/page_header.php',
            __NAMESPACE__ . '/index/peyment_credit_sp.php' => __DIR__ . '/../view_sp/components/index/peyment_credit.php',
            __NAMESPACE__ . '/creditsettlement/page_header_sp.php'    => __DIR__ . '/../view_sp/components/creditsettlement/page_header.php',
            __NAMESPACE__ . '/creditsettlement/page_footer_sp.php'    => __DIR__ . '/../view_sp/components/creditsettlement/page_footer.php',

            // smartp honenoのscripts
            __NAMESPACE__ . '/index/index_sp.phtml'        => __DIR__ . '/../view_sp/scripts/index/index.phtml',
            __NAMESPACE__ . '/login/login_sp.phtml'        => __DIR__ . '/../view_sp/scripts/login/login.phtml',
            __NAMESPACE__ . '/login/invalid_sp.phtml'        => __DIR__ . '/../view_sp/scripts/login/invalid.phtml',
            __NAMESPACE__ . '/billreiss/index_sp.phtml'    => __DIR__ . '/../view_sp/scripts/billreiss/index.phtml',
            __NAMESPACE__ . '/billreiss/confirm_sp.phtml'  => __DIR__ . '/../view_sp/scripts/billreiss/confirm.phtml',
            __NAMESPACE__ . '/billreiss/completion_sp.phtml'   => __DIR__ . '/../view_sp/scripts/billreiss/completion.phtml',
            __NAMESPACE__ . '/billreiss/error_sp.phtml'    => __DIR__ . '/../view_sp/scripts/billreiss/error.phtml',
            __NAMESPACE__ . '/receiptpdfoutput/receiptpreview_sp.phtml'   => __DIR__ . '/../view_sp/scripts/receiptpdfoutput/receiptpreview.phtml',
            __NAMESPACE__ . '/receiptpdfoutput/receipt_sp.phtml'   => __DIR__ . '/../view_sp/scripts/receiptpdfoutput/receipt.phtml',
            __NAMESPACE__ . '/receiptpdfoutput/error_sp.phtml'     => __DIR__ . '/../view_sp/scripts/receiptpdfoutput/error.phtml',
            __NAMESPACE__ . '/sbpssettlement/index_sp.phtml'     => __DIR__ . '/../view_sp/scripts/sbpssettlement/index.phtml',
            __NAMESPACE__ . '/sbpssettlement/error_sp.phtml'     => __DIR__ . '/../view_sp/scripts/sbpssettlement/error.phtml',
            __NAMESPACE__ . '/creditsettlement/input_sp.phtml'     => __DIR__ . '/../view_sp/scripts/creditsettlement/input.phtml',
            __NAMESPACE__ . '/creditsettlement/confirm_sp.phtml'   => __DIR__ . '/../view_sp/scripts/creditsettlement/confirm.phtml',
            __NAMESPACE__ . '/creditsettlement/complete_sp.phtml'  => __DIR__ . '/../view_sp/scripts/creditsettlement/complete.phtml',
            __NAMESPACE__ . '/creditsettlement/error_sp.phtml'  => __DIR__ . '/../view_sp/scripts/creditsettlement/error.phtml',
        ),
        'template_path_stack' => array(
            __NAMESPACE__ => __DIR__ . '/../view',
        ),
    ),
);
