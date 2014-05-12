<?php
/**
 * @file Ice module config
 *
 * Sets default config params for ice application components
 *
 * @author dp
 */

if (!defined('OBJECT_CACHE')) {
    define('OBJECT_CACHE', function_exists('apc_store') ? 'Apc' : 'Registry');
}
if (!defined('STRING_CACHE')) {
    define('STRING_CACHE', class_exists('Redis', false) ? 'Redis' : 'File');
}

return [
    'defaultLayoutAction' => 'ice\action\Layout_Main',
    'defaultViewRenderClass' => 'ice\view\render\Php',
    'modules' => [
        'Ice' => __DIR__ . '/',
    ],
    'configs' => [
        'ice\core\Model' => [
            'Ice' => 'ice\model\ice\\'
        ],
        'ice\core\Action' => [
            'Ice' => 'ice\action\\'
        ],
        'ice\core\Validator' => [
            'Ice' => 'ice\validator\\'
        ],
        'ice\core\Query_Translator' => [
            'Ice' => 'ice\query\translator\\'
        ]
    ],
    'host' => '',
    'hosts' => [
        '/localhost/' => 'development',
        '/.*/' => 'production'
    ],
    'environment' => [],
    'environments' => [
        'production' => [
            'debug' => false,
            'dataProviderKeys' => [
                'ice\core\Data_Source' => 'Mysqli:default/mysql',
                'ice\core\Loader' => OBJECT_CACHE . ':storage/loader',
                'ice\core\Action' => [
                    'instance' => OBJECT_CACHE . ':storage/action',
                    'output' => STRING_CACHE . ':cache/action'
                ],
                'ice\core\Route' => OBJECT_CACHE . ':storage/router',
                'ice\core\Config' => OBJECT_CACHE . ':storage/config',
                'ice\core\View_Render' => OBJECT_CACHE . ':storage/view_render',
                'ice\core\Query_Translator' => OBJECT_CACHE . ':storage/query_translator',
                'ice\core\Query' => [
                    'sql' => STRING_CACHE . ':cache/sql',
                    'query' => STRING_CACHE . ':cache/query',
                ],
                'ice\core\Model_Mapping' => OBJECT_CACHE . ':storage/model_mapping',
                'ice\core\Data_Mapping' => OBJECT_CACHE . ':storage/data_mapping',
                'ice\core\Model_Scheme' => OBJECT_CACHE . ':storage/model_scheme',
                'ice\core\Validator' => OBJECT_CACHE . ':storage/validator',
                'ice\core\Cache' => STRING_CACHE . ':cache/tags',
                'ice\core\View' => STRING_CACHE . ':cache/view',
            ],
            'dataProviders' => [
                OBJECT_CACHE . ':storage' => [],
                'Defined:model' => [],
                'Factory:model' => [],
                'Redis:cache' => [
                    'host' => 'localhost',
                    'port' => 6379
                ],
                'File:cache' => [
                    'path' => dirname(__DIR__) . '/cache/'
                ],
                'Request:http' => [],
                'Cli:prompt' => [],
                'Session:php' => [],
                'Registry:view_render' => [],
                'Registry:model_repository' => [],
                'Registry:action' => [],
                'Registry:registry' => [],
                'Router:route' => [],
                'Mysqli:default' => [
                    'host' => 'localhost',
                    'username' => 'root',
                    'password' => '',
                    'charset' => 'utf8'
                ]
            ]
        ],
        'test' => [
            'debug' => true,
        ],
        'development' => []
    ],
    'viewRenders' => [
        'Php' => []
    ]
];