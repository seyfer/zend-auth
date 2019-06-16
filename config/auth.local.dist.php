<?php

//copy this file to your autoload and config
return [
    "auth" => [
        "adapter" => "ZendDbAdapter",
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
    'session' => [
        'remember_me_seconds' => 3600,
        'use_cookies' => true,
        'cookie_httponly' => true,
    ],
];
