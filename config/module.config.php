<?php

return [
    //--- default and can be configured in local php
    "auth" => [
        "adapter" => "SampleAdapter",
        "accessStrategy" => "AllClosedSomeOpen",
        "useAcl" => FALSE,
        "accessStrategyConfig" => [
            "AllOpenSomeClosed" => [
                "closedPathes" => ['admin', 'teacher'],
            ],
            "AllClosedSomeOpen" => [
                "openedPathes" => ['api', 'receiver'],
            ],
        ],
        "adapterConfig" => [
            "SampleAdapter" => [
                'service' => \Auth\Adapter\SampleAdapter::class,
                'secretKey' => '',
                'url' => '',
                'site' => '',
            ],
            "DoctrineAdapter" => [
            ],
            "ZendDbAdapter" => [
                'service' => \Zend\Db\Adapter\Adapter::class,
                'tableName' => 'users',
                'loginColumn' => 'login',
                'passwordColumn' => 'password',
                'method' => 'MD5(?)',
            ],
        ],
    ],
    'acl' => [
        'guest' => [
            'home',
            'login',
            'login/process',
            'login/login',
            'ocra_service_manager_yuml',
        ],
        'admin' => [
            'success',
            'home/default',
            'admin',
        ],
    ],
    'session' => [
        'remember_me_seconds' => 3600,
        'use_cookies' => true,
        'cookie_httponly' => true,
    ],
    //---
    'doctrine' => [
        'driver' => [
            'auth_entities' => [
                'class' => \Doctrine\ORM\Mapping\Driver\AnnotationDriver::class,
                'cache' => 'array',
                'paths' => [__DIR__ . '/../src/Auth/Entity'],
            ],
            'orm_default' => [
                'drivers' => [
                    'Auth\Entity' => 'auth_entities',
                ],
            ],
        ],
        'authentication' => [
            'orm_default' => [
                'object_manager' => \Doctrine\ORM\EntityManager::class,
                'identity_class' => \Auth\Entity\User::class,
                'identity_property' => 'email',
                'credential_property' => 'password',
            ],
        ],
    ],
    'controllers' => [
        'invokables' => [
            'Auth\Controller\Success' => \Auth\Controller\SuccessController::class,
            'login' => Auth\Controller\AuthController::class,
        ],
    ],
    'router' => [
        'routes' => [
            'login' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/auth',
                    'defaults' => [
                        '__NAMESPACE__' => 'Auth\Controller',
                        'controller' => 'Auth',
                        'action' => 'login',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'process' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/[:action]',
                            'constraints' => [
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ],
                            'defaults' => [
                            ],
                        ],
                    ],
                    'login' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/login',
                            'defaults' => [
                                '__NAMESPACE__' => 'Auth\Controller',
                                'controller' => 'Auth',
                                'action' => 'login',
                            ],
                        ],
                    ],
                ],
            ],
            'success' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/success',
                    'defaults' => [
                        '__NAMESPACE__' => 'Auth\Controller',
                        'controller' => 'Success',
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'default' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/[:action]',
                            'constraints' => [
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ],
                            'defaults' => [
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'Auth' => __DIR__ . '/../view',
        ],
    ],
];

