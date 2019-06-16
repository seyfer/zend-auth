<?php

//copy this file to your autoload and config
return [
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
];
