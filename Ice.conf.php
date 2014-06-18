<?php
/**
 * @file Ice module config
 *
 * Sets default config params for ice application components
 *
 * @author dp <denis.a.shestakov@gmail.com>
 * @since -0
 */

if (!defined('OBJECT_CACHE')) {
    define('OBJECT_CACHE', function_exists('apc_store') ? 'Apc' : 'Registry');
}
if (!defined('STRING_CACHE')) {
    define('STRING_CACHE', class_exists('Redis', false) ? 'Redis' : 'File');
}

return [
    'defaultLayoutView' => null,
    'defaultLayoutAction' => 'Ice:Layout_Main',
    'defaultViewRenderClass' => 'Ice:Php',
    'modules' => [
        'Ice' => __DIR__ . '/',
    ],
    'configs' => [
        'ice\core\Model' => [
            'Ice' => 'ice\model\ice\\'
        ],
        'ice\core\Action' => [
            'Ice' => 'ice\action\\',
        ],
        'ice\core\Validator' => [
            'Ice' => 'ice\validator\\'
        ],
        'ice\core\Query_Translator' => [
            'Ice' => 'ice\query\translator\\'
        ],
        'ice\core\View_Render' => [
            'Ice' => 'ice\view\render\\'
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
        ],
        'test' => [
            'debug' => true,
        ],
        'development' => [
            'dataProviderKeys' => [
                'ice\core\Loader' => 'Registry:storage/loader',
                'ice\core\Action' => [
                    'instance' => 'Registry:storage/action',
                    'output' => 'Null:cache/action'
                ],
                'ice\core\Route' => 'Registry:storage/router',
                'ice\core\Config' => 'Registry:storage/config',
                'ice\core\View_Render' => 'Registry:storage/view_render',
                'ice\core\Query_Translator' => 'Registry:storage/query_translator',
                'ice\core\Query' => [
                    'sql' => 'Null:cache/sql',
                    'query' => 'Null:cache/query',
                ],
                'ice\core\Model_Mapping' => 'Registry:storage/model_mapping',
                'ice\core\Data_Mapping' => 'Registry:storage/data_mapping',
                'ice\core\Model_Scheme' => 'Registry:storage/model_scheme',
                'ice\core\Validator' => 'Registry:storage/validator',
                'ice\core\Cache' => 'Null:cache/tags',
                'ice\core\View' => 'Null:cache/view',
            ],
        ]
    ],
    'viewRenders' => [
        'Php' => []
    ],
    'dataProviders' => [
        'Apc:storage' => [],
        'Registry:storage' => [],
        'Defined:model' => [],
        'Factory:model' => [],
        'Redis:cache' => [
            'host' => 'localhost',
            'port' => 6379
        ],
        'File:cache' => [
            'path' => dirname(__DIR__) . '/cache/'
        ],
        'File:output' => [
            'path' => null
        ],
        'Null:cache' => [],
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
];