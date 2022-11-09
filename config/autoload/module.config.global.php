<?php
return array(
        'router' => array(
                'routes' => array(
                        'home' => array(
                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                'options' => array(
                                        'route'    => '/',
                                        'defaults' => array(
                                        ),
                                ),
                        ),
                ),
        ),
        'service_manager' => array(
                'abstract_factories' => array(
                        'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
                        'Zend\Log\LoggerAbstractServiceFactory',
                ),
                'aliases' => array(
                        'translator' => 'MvcTranslator',
                ),
        ),
        'translator' => array(
                'locale' => 'ja_JP',
                'translation_file_patterns' => array(
                        array(
                                'type'     => 'gettext',
                                'base_dir' => __DIR__ . '/../language',
                                'pattern'  => '%s.mo',
                        ),
                ),
        ),
        'view_manager' => array(
                'display_not_found_reason' => true,
                'display_exceptions'       => true,
                'doctype'                  => 'HTML5',
                'not_found_template'       => 'error/404',
                'exception_template'       => 'error/index',
                //'template_map' => array(
                //        'error/404'               => __DIR__ . '/../view/error/404.phtml',
                //        'error/index'             => __DIR__ . '/../view/error/index.phtml',
                //),
        ),
);

