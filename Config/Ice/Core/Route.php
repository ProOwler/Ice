<?php

return [
    [
        'route' => '/',
        'actions' => [
            'title' => ['Ice:Title' => ['title' => 'Main page']],
            'main' => 'Ice:Main'
        ]
    ],
    100020 => [
        'route' => '/registration/',
        'layout' => ['action' => 'Ice:Layout_Account'],
        'actions' => [
            'title' => ['Ice:Title' => ['title' => 'Registration']],
            'main' => 'Ice:Account_Registration'
        ],
        'params' => [
            'accountType' => 'Login_Password',
        ]
    ],
    100030 => [
        'route' => '/authorization/',
        'layout' => ['action' => 'Ice:Layout_Account'],
        'actions' => [
            'title' => ['Ice:Title' => ['title' => 'Authorization']],
            'main' => 'Ice:Account_Authorization'
        ],
        'params' => [
            'accountType' => 'Login_Password',
        ]
    ],
    100040 => [
        'route' => '/logout/',
        'actions' => [
            'title' => ['Ice:Title' => ['title' => 'Exit']],
            'main' => 'Ice:Account_Logout'
        ],
        'params' => [
            'redirect' => '/'
        ]
    ],
];