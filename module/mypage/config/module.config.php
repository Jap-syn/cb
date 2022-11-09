<?php
namespace mypage;
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
            'mypage' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/mypage',
                    'defaults' => array(
                            '__NAMESPACE__' => 'mypage\Controller',
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
            'mypage\Controller\Login'                   => 'mypage\Controller\LoginController',
            'mypage\Controller\Index'                   => 'mypage\Controller\IndexController',
            'mypage\Controller\Edit'                    => 'mypage\Controller\EditController',
            'mypage\Controller\Upload'                  => 'mypage\Controller\UploadController',
            'mypage\Controller\BillReIss'               => 'mypage\Controller\BillReIssController',
            'mypage\Controller\Regist'                  => 'mypage\Controller\RegistController',
            'mypage\Controller\Api'                     => 'mypage\Controller\ApiController',
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
            __NAMESPACE__ . '/login/page_header.php'    => __DIR__ . '/../view/components/login/page_header.php',
            __NAMESPACE__ . '/regist/page_header.php'   => __DIR__ . '/../view/components/regist/page_header.php',
            __NAMESPACE__ . '/regist/list.php'          => __DIR__ . '/../view/components/regist/list.php',

            // smart phone components
            __NAMESPACE__ . '/document_header_sp.php'      => __DIR__ . '/../view_sp/components/document_header.php',
            __NAMESPACE__ . '/page_footer_sp.php'          => __DIR__ . '/../view_sp/components/page_footer.php',
            __NAMESPACE__ . '/page_header_sp.php'          => __DIR__ . '/../view_sp/components/page_header.php',
            __NAMESPACE__ . '/system_message_sp.php'       => __DIR__ . '/../view_sp/components/system_message.php',
            __NAMESPACE__ . '/menu_sp.php'                 => __DIR__ . '/../view_sp/components/menu.php',
            __NAMESPACE__ . '/login/page_header_sp.php'    => __DIR__ . '/../view_sp/components/login/page_header.php',
            __NAMESPACE__ . '/regist/page_header_sp.php'   => __DIR__ . '/../view_sp/components/regist/page_header.php',
            __NAMESPACE__ . '/regist/list_sp.php'          => __DIR__ . '/../view_sp/components/regist/list.php',

            // scripts
            __NAMESPACE__ . '/index/index.phtml'        => __DIR__ . '/../view/scripts/index/index.phtml',
            __NAMESPACE__ . '/login/login.phtml'        => __DIR__ . '/../view/scripts/login/login.phtml',
            __NAMESPACE__ . '/login/reissue.phtml'           => __DIR__ . '/../view/scripts/login/reissue.phtml',
            __NAMESPACE__ . '/login/reissueconfirm.phtml'    => __DIR__ . '/../view/scripts/login/reissueconfirm.phtml',
            __NAMESPACE__ . '/login/reissuecomplete.phtml'   => __DIR__ . '/../view/scripts/login/reissuecomplete.phtml',
            __NAMESPACE__ . '/login/reset.phtml'             => __DIR__ . '/../view/scripts/login/reset.phtml',
            __NAMESPACE__ . '/login/resetcomplete.phtml'     => __DIR__ . '/../view/scripts/login/resetcomplete.phtml',
            __NAMESPACE__ . '/login/expired.phtml'           => __DIR__ . '/../view/scripts/login/expired.phtml',
            __NAMESPACE__ . '/edit/index.phtml'         => __DIR__ . '/../view/scripts/edit/index.phtml',
            __NAMESPACE__ . '/edit/confirm.phtml'       => __DIR__ . '/../view/scripts/edit/confirm.phtml',
            __NAMESPACE__ . '/edit/passchg.phtml'       => __DIR__ . '/../view/scripts/edit/passchg.phtml',
            __NAMESPACE__ . '/edit/completion.phtml'    => __DIR__ . '/../view/scripts/edit/completion.phtml',
            __NAMESPACE__ . '/edit/withdraw.phtml'      => __DIR__ . '/../view/scripts/edit/withdraw.phtml',
            __NAMESPACE__ . '/edit/wdcompletion.phtml'  => __DIR__ . '/../view/scripts/edit/wdcompletion.phtml',
            __NAMESPACE__ . '/edit/chgpw_f.phtml'       => __DIR__ . '/../view/scripts/edit/chgpw_f.phtml',
            __NAMESPACE__ . '/edit/chgpw_e.phtml'       => __DIR__ . '/../view/scripts/edit/chgpw_e.phtml',
            __NAMESPACE__ . '/upload/index.phtml'       => __DIR__ . '/../view/scripts/upload/index.phtml',
            __NAMESPACE__ . '/upload/confirm.phtml'     => __DIR__ . '/../view/scripts/upload/confirm.phtml',
            __NAMESPACE__ . '/upload/completion.phtml'  => __DIR__ . '/../view/scripts/upload/completion.phtml',
            __NAMESPACE__ . '/billreiss/index.phtml'    => __DIR__ . '/../view/scripts/billreiss/index.phtml',
            __NAMESPACE__ . '/billreiss/confirm.phtml'  => __DIR__ . '/../view/scripts/billreiss/confirm.phtml',
            __NAMESPACE__ . '/billreiss/completion.phtml'   => __DIR__ . '/../view/scripts/billreiss/completion.phtml',
            __NAMESPACE__ . '/billreiss/error.phtml'    => __DIR__ . '/../view/scripts/billreiss/error.phtml',
            __NAMESPACE__ . '/regist/preregist.phtml'   => __DIR__ . '/../view/scripts/regist/preregist.phtml',
            __NAMESPACE__ . '/regist/regist.phtml'      => __DIR__ . '/../view/scripts/regist/regist.phtml',
            __NAMESPACE__ . '/regist/confirm.phtml'     => __DIR__ . '/../view/scripts/regist/confirm.phtml',
            __NAMESPACE__ . '/regist/completion.phtml'  => __DIR__ . '/../view/scripts/regist/completion.phtml',
            __NAMESPACE__ . '/regist/error.phtml'       => __DIR__ . '/../view/scripts/regist/error.phtml',

            // smart phone scripts
            __NAMESPACE__ . '/index/index_sp.phtml'        => __DIR__ . '/../view_sp/scripts/index/index.phtml',
            __NAMESPACE__ . '/login/login_sp.phtml'        => __DIR__ . '/../view_sp/scripts/login/login.phtml',
            __NAMESPACE__ . '/login/reissue_sp.phtml'        => __DIR__ . '/../view_sp/scripts/login/reissue.phtml',
            __NAMESPACE__ . '/login/reissueconfirm_sp.phtml' => __DIR__ . '/../view_sp/scripts/login/reissueconfirm.phtml',
            __NAMESPACE__ . '/login/reissuecomplete_sp.phtml'=> __DIR__ . '/../view_sp/scripts/login/reissuecomplete.phtml',
            __NAMESPACE__ . '/login/reset_sp.phtml'          => __DIR__ . '/../view_sp/scripts/login/reset.phtml',
            __NAMESPACE__ . '/login/resetcomplete_sp.phtml'  => __DIR__ . '/../view_sp/scripts/login/resetcomplete.phtml',
            __NAMESPACE__ . '/login/expired_sp.phtml'        => __DIR__ . '/../view_sp/scripts/login/expired.phtml',
            __NAMESPACE__ . '/edit/index_sp.phtml'         => __DIR__ . '/../view_sp/scripts/edit/index.phtml',
            __NAMESPACE__ . '/edit/confirm_sp.phtml'       => __DIR__ . '/../view_sp/scripts/edit/confirm.phtml',
            __NAMESPACE__ . '/edit/passchg_sp.phtml'       => __DIR__ . '/../view_sp/scripts/edit/passchg.phtml',
            __NAMESPACE__ . '/edit/completion_sp.phtml'    => __DIR__ . '/../view_sp/scripts/edit/completion.phtml',
            __NAMESPACE__ . '/edit/withdraw_sp.phtml'      => __DIR__ . '/../view_sp/scripts/edit/withdraw.phtml',
            __NAMESPACE__ . '/edit/wdcompletion_sp.phtml'  => __DIR__ . '/../view_sp/scripts/edit/wdcompletion.phtml',
            __NAMESPACE__ . '/edit/chgpw_f_sp.phtml'       => __DIR__ . '/../view_sp/scripts/edit/chgpw_f.phtml',
            __NAMESPACE__ . '/edit/chgpw_e_sp.phtml'       => __DIR__ . '/../view_sp/scripts/edit/chgpw_e.phtml',
            __NAMESPACE__ . '/upload/index_sp.phtml'       => __DIR__ . '/../view_sp/scripts/upload/index.phtml',
            __NAMESPACE__ . '/upload/confirm_sp.phtml'     => __DIR__ . '/../view_sp/scripts/upload/confirm.phtml',
            __NAMESPACE__ . '/upload/completion_sp.phtml'  => __DIR__ . '/../view_sp/scripts/upload/completion.phtml',
            __NAMESPACE__ . '/billreiss/index_sp.phtml'    => __DIR__ . '/../view_sp/scripts/billreiss/index.phtml',
            __NAMESPACE__ . '/billreiss/confirm_sp.phtml'  => __DIR__ . '/../view_sp/scripts/billreiss/confirm.phtml',
            __NAMESPACE__ . '/billreiss/completion_sp.phtml'   => __DIR__ . '/../view_sp/scripts/billreiss/completion.phtml',
            __NAMESPACE__ . '/billreiss/error_sp.phtml'    => __DIR__ . '/../view_sp/scripts/billreiss/error.phtml',
            __NAMESPACE__ . '/regist/preregist_sp.phtml'   => __DIR__ . '/../view_sp/scripts/regist/preregist.phtml',
            __NAMESPACE__ . '/regist/regist_sp.phtml'      => __DIR__ . '/../view_sp/scripts/regist/regist.phtml',
            __NAMESPACE__ . '/regist/confirm_sp.phtml'     => __DIR__ . '/../view_sp/scripts/regist/confirm.phtml',
            __NAMESPACE__ . '/regist/completion_sp.phtml'  => __DIR__ . '/../view_sp/scripts/regist/completion.phtml',
            __NAMESPACE__ . '/regist/error_sp.phtml'       => __DIR__ . '/../view_sp/scripts/regist/error.phtml',
        ),
        'template_path_stack' => array(
            __NAMESPACE__ => __DIR__ . '/../view',
        ),
    ),
);
