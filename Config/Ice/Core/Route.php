<?php

return [
    100010 => [
        'route' => '/',
        'actions' => [
            'title' => ['ice\action\Title' => ['title' => 'Main page']],
            'main' => 'ice\action\Main'
        ]
    ],
    100020 => [
        'route' => '/registration/',
        'layout' => ['action' => 'ice\action\Layout_Account'],
        'actions' => [
            'title' => ['ice\action\Title' => ['title' => 'Registration']],
            'main' => 'ice\action\Account_Registration'
        ],
        'params' => [
            'accountType' => 'Login_Password',
        ]
    ],
    100030 => [
        'route' => '/authorization/',
        'layout' => ['action' => 'ice\action\Layout_Account'],
        'actions' => [
            'title' => ['ice\action\Title' => ['title' => 'Authorization']],
            'main' => 'ice\action\Account_Authorization'
        ],
        'params' => [
            'accountType' => 'Login_Password',
        ]
    ],
    100040 => [
        'route' => '/logout/',
        'actions' => [
            'title' => ['ice\action\Title' => ['title' => 'Exit']],
            'main' => 'ice\action\Account_Logout'
        ],
        'params' => [
            'redirect' => '/'
        ]
    ],
];