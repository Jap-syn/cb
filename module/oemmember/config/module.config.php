<?php
namespace oemmember;
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
                    'oemmember' => array(
                            'type'    => 'Literal',
                            'options' => array(
                                    'route'    => '/oemmember',
                                    'defaults' => array(
                                            '__NAMESPACE__' => 'oemmember\Controller',
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
                    'estoremember' => array(
                            'type'    => 'Literal',
                            'options' => array(
                                    'route'    => '/estore/member',
                                    'defaults' => array(
                                            '__NAMESPACE__' => 'oemmember\Controller',
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
                    'smbcfsmember' => array(
                            'type'    => 'Literal',
                            'options' => array(
                                    'route'    => '/smbcfs/member',
                                    'defaults' => array(
                                            '__NAMESPACE__' => 'oemmember\Controller',
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
                    'seino-financialmember' => array(
                            'type'    => 'Literal',
                            'options' => array(
                                    'route'    => '/seino-financial/member',
                                    'defaults' => array(
                                            '__NAMESPACE__' => 'oemmember\Controller',
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
                    'basemember' => array(
                            'type'    => 'Literal',
                            'options' => array(
                                    'route'    => '/base/member',
                                    'defaults' => array(
                                            '__NAMESPACE__' => 'oemmember\Controller',
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
                    'temonamember' => array(
                            'type'    => 'Literal',
                            'options' => array(
                                    'route'    => '/temona/member',
                                    'defaults' => array(
                                            '__NAMESPACE__' => 'oemmember\Controller',
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
                    'mizuhomember' => array(
                            'type'    => 'Literal',
                            'options' => array(
                                    'route'    => '/mizuho/member',
                                    'defaults' => array(
                                            '__NAMESPACE__' => 'oemmember\Controller',
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
                    'tri-paymentmember' => array(
                            'type'    => 'Literal',
                            'options' => array(
                                    'route'    => '/tri-payment/member',
                                    'defaults' => array(
                                            '__NAMESPACE__' => 'oemmember\Controller',
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
            'oemmember\Controller\Login'     => 'oemmember\Controller\LoginController',
            'oemmember\Controller\Index'     => 'oemmember\Controller\IndexController',
            'oemmember\Controller\Account'   => 'oemmember\Controller\AccountController',
            'oemmember\Controller\Account'    => 'oemmember\Controller\AccountController',
            'oemmember\Controller\Ajax'       => 'oemmember\Controller\AjaxController',
//             'oemmember\Controller\Billing'    => 'oemmember\Controller\BillingController',
            'oemmember\Controller\Claim'      => 'oemmember\Controller\ClaimController',
            'oemmember\Controller\Error'      => 'oemmember\Controller\ErrorController',
            'oemmember\Controller\Index'      => 'oemmember\Controller\IndexController',
            'oemmember\Controller\Login'      => 'oemmember\Controller\LoginController',
            'oemmember\Controller\Merge'      => 'oemmember\Controller\MergeController',
            'oemmember\Controller\Monthly'    => 'oemmember\Controller\MonthlyController',
            'oemmember\Controller\Order'      => 'oemmember\Controller\OrderController',
            'oemmember\Controller\Resource'   => 'oemmember\Controller\ResourceController',
            'oemmember\Controller\Rwclaim'    => 'oemmember\Controller\RwclaimController',
            'oemmember\Controller\Search'     => 'oemmember\Controller\SearchController',
            'oemmember\Controller\Shipping'   => 'oemmember\Controller\ShippingController',
            'oemmember\Controller\Shippingsp' => 'oemmember\Controller\ShippingspController',
            'oemmember\Controller\OrderCancel' => 'oemmember\Controller\OrderCancelController',
            'oemmember\Controller\ChangeCsv' => 'oemmember\Controller\ChangeCsvController',
        ),
    ),
    'view_manager' => array(
        'template_map' => array(
            'error/404'                             => __DIR__ . '/../view/error/404.phtml',
            __NAMESPACE__ . '/error/index.phtml'    => __DIR__ . '/../view/error/index.phtml',

            // components
            __NAMESPACE__ . '/array_table.php' => __DIR__ . '/../view/components/array_table.php',
            __NAMESPACE__ . '/common_assign.php' => __DIR__ . '/../view/components/common_assign.php',
            __NAMESPACE__ . '/document_header.php' => __DIR__ . '/../view/components/document_header.php',
            __NAMESPACE__ . '/footer_menu.php' => __DIR__ . '/../view/components/footer_menu.php',
            __NAMESPACE__ . '/header_menu.php' => __DIR__ . '/../view/components/header_menu.php',
            __NAMESPACE__ . '/important_messages.php' => __DIR__ . '/../view/components/important_messages.php',
            __NAMESPACE__ . '/information_message.php' => __DIR__ . '/../view/components/information_message.php',
            __NAMESPACE__ . '/oem_page_footer.php' => __DIR__ . '/../view/components/oem_page_footer.php',
            __NAMESPACE__ . '/oem_page_header.php' => __DIR__ . '/../view/components/oem_page_header.php',
            __NAMESPACE__ . '/page_footer.php' => __DIR__ . '/../view/components/page_footer.php',
            __NAMESPACE__ . '/page_header.php' => __DIR__ . '/../view/components/page_header.php',
            __NAMESPACE__ . '/page_title.php' => __DIR__ . '/../view/components/page_title.php',
            __NAMESPACE__ . '/page_navigation.php' => __DIR__ . '/../view/components/page_navigation.php',
            __NAMESPACE__ . '/quick_search_bar.php' => __DIR__ . '/../view/components/quick_search_bar.php',
            __NAMESPACE__ . '/system_message.php' => __DIR__ . '/../view/components/system_message.php',
            __NAMESPACE__ . '/tab_support.php' => __DIR__ . '/../view/components/tab_support.php',
            __NAMESPACE__ . '/column_table.php' => __DIR__ . '/../view/components/column_table.php',
            __NAMESPACE__ . '/account/site_table.php' => __DIR__ . '/../view/components/account/site_table.php',
            __NAMESPACE__ . '/account/sbps_payments.php' => __DIR__ . '/../view/components/account/sbps_payments.php',
            __NAMESPACE__ . '/claim/cancel_list.php' => __DIR__ . '/../view/components/claim/cancel_list.php',
            __NAMESPACE__ . '/claim/charge_list.php' => __DIR__ . '/../view/components/claim/charge_list.php',
            __NAMESPACE__ . '/claim/stamp_list.php' => __DIR__ . '/../view/components/claim/stamp_list.php',
            __NAMESPACE__ . '/claim/summary.php' => __DIR__ . '/../view/components/claim/summary.php',
            __NAMESPACE__ . '/claim/adjustment_list.php' => __DIR__ . '/../view/components/claim/adjustment_list.php',
            __NAMESPACE__ . '/claim/payback_list.php' => __DIR__ . '/../view/components/claim/payback_list.php',
            __NAMESPACE__ . '/index/order_summaries.php' => __DIR__ . '/../view/components/index/order_summaries.php',
            __NAMESPACE__ . '/index/page_title.php' => __DIR__ . '/../view/components/index/page_title.php',
            __NAMESPACE__ . '/monthly/cancel_list.php' => __DIR__ . '/../view/components/monthly/cancel_list.php',
            __NAMESPACE__ . '/monthly/charge_list.php' => __DIR__ . '/../view/components/monthly/charge_list.php',
            __NAMESPACE__ . '/monthly/list_summary.php' => __DIR__ . '/../view/components/monthly/list_summary.php',
            __NAMESPACE__ . '/monthly/stamp_list.php' => __DIR__ . '/../view/components/monthly/stamp_list.php',
            __NAMESPACE__ . '/monthly/summary.php' => __DIR__ . '/../view/components/monthly/summary.php',
            __NAMESPACE__ . '/monthly/adjustment_list.php' => __DIR__ . '/../view/components/monthly/adjustment_list.php',
            __NAMESPACE__ . '/monthly/payback_list.php' => __DIR__ . '/../view/components/monthly/payback_list.php',
            __NAMESPACE__ . '/order/basic_form.php' => __DIR__ . '/../view/components/order/basic_form.php',
            __NAMESPACE__ . '/order/customer_form.php' => __DIR__ . '/../view/components/order/customer_form.php',
            __NAMESPACE__ . '/order/delivery_form.php' => __DIR__ . '/../view/components/order/delivery_form.php',
            __NAMESPACE__ . '/order/item_form.php' => __DIR__ . '/../view/components/order/item_form.php',
            __NAMESPACE__ . '/order/main_js.php' => __DIR__ . '/../view/components/order/main_js.php',
            __NAMESPACE__ . '/order/page_title.php' => __DIR__ . '/../view/components/order/page_title.php',
            __NAMESPACE__ . '/search/checkboxes.php' => __DIR__ . '/../view/components/search/checkboxes.php',
            __NAMESPACE__ . '/search/column_order_table.php' => __DIR__ . '/../view/components/search/column_order_table.php',
            __NAMESPACE__ . '/search/date_span.php' => __DIR__ . '/../view/components/search/date_span.php',
            __NAMESPACE__ . '/search/list.php' => __DIR__ . '/../view/components/search/list.php',
            __NAMESPACE__ . '/search/multi_text.php' => __DIR__ . '/../view/components/search/multi_text.php',
            __NAMESPACE__ . '/search/post_body_js.php' => __DIR__ . '/../view/components/search/post_body_js.php',
            __NAMESPACE__ . '/search/pre_body_js.php' => __DIR__ . '/../view/components/search/pre_body_js.php',
            __NAMESPACE__ . '/search/search_back_form.php' => __DIR__ . '/../view/components/search/search_back_form.php',
            __NAMESPACE__ . '/search/search_form.php' => __DIR__ . '/../view/components/search/search_form.php',
            __NAMESPACE__ . '/search/search_result_table.php' => __DIR__ . '/../view/components/search/search_result_table.php',
            __NAMESPACE__ . '/search/simple_text.php' => __DIR__ . '/../view/components/search/simple_text.php',
            __NAMESPACE__ . '/search/text_span.php' => __DIR__ . '/../view/components/search/text_span.php',
            __NAMESPACE__ . '/search/journalnumber.php' => __DIR__ . '/../view/components/search/journalnumber.php',
            __NAMESPACE__ . '/search/request_cancel.php' => __DIR__ . '/../view/components/search/request_cancel.php',
            __NAMESPACE__ . '/account/sbps_payments.php' => __DIR__ . '/../view/components/account/sbps_payments.php',

            // scripts
            __NAMESPACE__ .'/shipping/shipping_inputs.php' => __DIR__ . '/../view/components/shipping/shipping_inputs.php',
            __NAMESPACE__ .'/account/confirm.phtml' => __DIR__ . '/../view/scripts/account/confirm.phtml',
            __NAMESPACE__ .'/account/index.phtml' => __DIR__ . '/../view/scripts/account/index.phtml',
            __NAMESPACE__ .'/account/changecsv.phtml' => __DIR__ . '/../view/scripts/account/changecsv.phtml',
            __NAMESPACE__ . '/account/sbps_payments_table.phtml' => __DIR__ . '/../view/scripts/account/sbps_payments_table.phtml',
            __NAMESPACE__ .'/claim/index.phtml' => __DIR__ . '/../view/scripts/claim/index.phtml',
            __NAMESPACE__ .'/claim/confirmNews.phtml' => __DIR__ . '/../view/scripts/claim/confirmNews.phtml',
            __NAMESPACE__ .'/claim/billissue.phtml' => __DIR__ . '/../view/scripts/claim/billissue.phtml',
            __NAMESPACE__ .'/error/error.phtml' => __DIR__ . '/../view/scripts/error/error.phtml',
            __NAMESPACE__ .'/index/download.phtml' => __DIR__ . '/../view/scripts/index/download.phtml',
            __NAMESPACE__ .'/index/index.phtml' => __DIR__ . '/../view/scripts/index/index.phtml',
            __NAMESPACE__ .'/login/autherror.phtml' => __DIR__ . '/../view/scripts/login/autherror.phtml',
            __NAMESPACE__ .'/login/login.phtml' => __DIR__ . '/../view/scripts/login/login.phtml',
            __NAMESPACE__ .'/login/chgpw_e.phtml' => __DIR__ . '/../view/scripts/login/chgpw_e.phtml',
            __NAMESPACE__ .'/login/chgpw_f.phtml' => __DIR__ . '/../view/scripts/login/chgpw_f.phtml',
            __NAMESPACE__ .'/merge/confirm.phtml' => __DIR__ . '/../view/scripts/merge/confirm.phtml',
            __NAMESPACE__ .'/merge/list.phtml' => __DIR__ . '/../view/scripts/merge/list.phtml',
            __NAMESPACE__ .'/monthly/index.phtml' => __DIR__ . '/../view/scripts/monthly/index.phtml',
            __NAMESPACE__ .'/monthly/billissue.phtml' => __DIR__ . '/../view/scripts/monthly/billissue.phtml',
            __NAMESPACE__ .'/order/changecsv.phtml' => __DIR__ . '/../view/scripts/order/changecsv.phtml',
            __NAMESPACE__ .'/order/complete.phtml' => __DIR__ . '/../view/scripts/order/complete.phtml',
            __NAMESPACE__ .'/order/completeedit.phtml' => __DIR__ . '/../view/scripts/order/completeedit.phtml',
            __NAMESPACE__ .'/order/confirm.phtml' => __DIR__ . '/../view/scripts/order/confirm.phtml',
            __NAMESPACE__ .'/order/confirmCsv.phtml' => __DIR__ . '/../view/scripts/order/confirmcsv.phtml',
            __NAMESPACE__ .'/order/edit.phtml' => __DIR__ . '/../view/scripts/order/edit.phtml',
            __NAMESPACE__ .'/order/editconfirm.phtml' => __DIR__ . '/../view/scripts/order/editconfirm.phtml',
            __NAMESPACE__ .'/order/order.phtml' => __DIR__ . '/../view/scripts/order/order.phtml',
            __NAMESPACE__ .'/order/orderCsv.phtml' => __DIR__ . '/../view/scripts/order/ordercsv.phtml',
            __NAMESPACE__ .'/order/save.phtml' => __DIR__ . '/../view/scripts/order/save.phtml',
            __NAMESPACE__ .'/order/changecsv.phtml' => __DIR__ . '/../view/scripts/order/changecsv.phtml',
            __NAMESPACE__ .'/order/editCsv.phtml' => __DIR__ . '/../view/scripts/order/editcsv.phtml',
            __NAMESPACE__ .'/order/changeeditCsv.phtml' => __DIR__ . '/../view/scripts/order/changeeditcsv.phtml',
            __NAMESPACE__ .'/order/confirmeditCsv.phtml' => __DIR__ . '/../view/scripts/order/confirmeditcsv.phtml',
            __NAMESPACE__ .'/order/defectlist.phtml' => __DIR__ . '/../view/scripts/order/defectlist.phtml',
            __NAMESPACE__ .'/rwclaim/list.phtml' => __DIR__ . '/../view/scripts/rwclaim/list.phtml',
            __NAMESPACE__ .'/rwclaim/csvsetting.phtml' => __DIR__ . '/../view/scripts/rwclaim/csvsetting.phtml',
            __NAMESPACE__ .'/rwclaim/changecsv.phtml' => __DIR__ . '/../view/scripts/rwclaim/changecsv.phtml',
            __NAMESPACE__ .'/rwclaim/printadjust.phtml' => __DIR__ . '/../view/scripts/rwclaim/printadjust.phtml',
            __NAMESPACE__ .'/rwclaim/billeddokon.phtml' => __DIR__ . '/../view/scripts/rwclaim/billeddokon.phtml',
            __NAMESPACE__ .'/rwclaim/billedgyunyu.phtml' => __DIR__ . '/../view/scripts/rwclaim/billedgyunyu.phtml',
            __NAMESPACE__ .'/search/detail.phtml' => __DIR__ . '/../view/scripts/search/detail.phtml',
            __NAMESPACE__ .'/search/no-detail.phtml' => __DIR__ . '/../view/scripts/search/no-detail.phtml',
            __NAMESPACE__ .'/search/result.phtml' => __DIR__ . '/../view/scripts/search/result.phtml',
            __NAMESPACE__ .'/search/search.phtml' => __DIR__ . '/../view/scripts/search/search.phtml',
            __NAMESPACE__ .'/shipping/complete.phtml' => __DIR__ . '/../view/scripts/shipping/complete.phtml',
            __NAMESPACE__ .'/shipping/confirm.phtml' => __DIR__ . '/../view/scripts/shipping/confirm.phtml',
            __NAMESPACE__ .'/shipping/confirmCsv.phtml' => __DIR__ . '/../view/scripts/shipping/confirmcsv.phtml',
            __NAMESPACE__ .'/shipping/regist.phtml' => __DIR__ . '/../view/scripts/shipping/regist.phtml',
            __NAMESPACE__ .'/shipping/registCsv.phtml' => __DIR__ . '/../view/scripts/shipping/registcsv.phtml',
            __NAMESPACE__ .'/shipping/changeCsv.phtml' => __DIR__ . '/../view/scripts/shipping/changecsv.phtml',
            __NAMESPACE__ .'/shipping/save.phtml' => __DIR__ . '/../view/scripts/shipping/save.phtml',
            __NAMESPACE__ .'/shipping/detail.phtml' => __DIR__ . '/../view/scripts/shipping/detail.phtml',
            __NAMESPACE__ .'/shipping/detailerr.phtml' => __DIR__ . '/../view/scripts/shipping/detailerr.phtml',
            __NAMESPACE__ .'/shipping/completeChangeCsv.phtml' => __DIR__ . '/../view/scripts/shipping/completechangecsv.phtml',
            __NAMESPACE__ .'/shipping/confirmChangeCsv.phtml' => __DIR__ . '/../view/scripts/shipping/confirmchangecsv.phtml',
            __NAMESPACE__ .'/shippingsp/complete.phtml' => __DIR__ . '/../view/scripts/shippingsp/complete.phtml',
            __NAMESPACE__ .'/shippingsp/confirmCsv.phtml' => __DIR__ . '/../view/scripts/shippingsp/confirmcsv.phtml',
            __NAMESPACE__ .'/shippingsp/detail.phtml' => __DIR__ . '/../view/scripts/shippingsp/detail.phtml',
            __NAMESPACE__ .'/shippingsp/detailerr.phtml' => __DIR__ . '/../view/scripts/shippingsp/detailerr.phtml',
            __NAMESPACE__ .'/shippingsp/index.phtml' => __DIR__ . '/../view/scripts/shippingsp/index.phtml',
            __NAMESPACE__ .'/ordercancel/registCsv.phtml' => __DIR__ . '/../view/scripts/ordercancel/registcsv.phtml',
            __NAMESPACE__ .'/ordercancel/confirmCsv.phtml' => __DIR__ . '/../view/scripts/ordercancel/confirmcsv.phtml',
            __NAMESPACE__ .'/ordercancel/completeCsv.phtml' => __DIR__ . '/../view/scripts/ordercancel/completecsv.phtml',
        ),
        'template_path_stack' => array(
                __NAMESPACE__ => __DIR__ . '/../view',
        ),
    ),
);
