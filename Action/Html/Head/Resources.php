<?php
namespace ice\action;

use CSSmin;
use ice\core\Action;
use ice\core\action\View;
use ice\core\Action_Context;
use ice\core\Data_Provider;
use ice\core\Loader;
use ice\core\Model;
use ice\data\provider\Router;
use ice\helper\Dir;
use ice\Ice;
use ice\view\render\Php;
use JSMin;

/**
 * Action of generation js and css for includes into html tag head (<script.. and <link..)
 *
 * @package ice\action
 * @author dp
 */
class Html_Head_Resources extends Action implements View
{
    const RESOURCE_TYPE_JS = 'js';
    const RESOURCE_TYPE_CSS = 'css';
    public static $config = [
        'Ice' => [
            'jquery' => [
                'path' => 'Vendor/jquery-ui-1.10.3/',
                self::RESOURCE_TYPE_JS => ['js/jquery-1.9.1.js', '-js/jquery-ui-1.10.3.custom.min.js'],
                self::RESOURCE_TYPE_CSS => ['-css/smoothness/jquery-ui-1.10.3.custom.min.css']
            ],
            'bootstrap' => [
                'path' => 'Vendor/bootstrap-3.1.0/',
                self::RESOURCE_TYPE_JS => ['-js/bootstrap.min.js'],
                self::RESOURCE_TYPE_CSS => ['-css/bootstrap.min.css', '-css/bootstrap-theme.min.css']
            ],
            'module' => [
                'path' => null,
                self::RESOURCE_TYPE_JS => ['js/Ice.js'],
                self::RESOURCE_TYPE_CSS => ['css/Ice.css']
            ]
        ]
    ];

    protected $viewRenderClass = Php::VIEW_RENDER_PHP_CLASS;
    protected $dataProviderKeys = Router::DEFAULT_KEY;

    public static function appendJs($resource)
    {
        self::append(self::RESOURCE_TYPE_JS, $resource);
    }

    private static function append($resourceType, $resource)
    {
        /** @var Action $actionClass */
        $actionClass = self::getClass();

        $dataProvider = Data_Provider::getInstance($actionClass::getRegistryDataProviderKey());

        $customResources = $dataProvider->get($resourceType);

        if (!$customResources) {
            $customResources = [];
        }

        array_push($customResources, $resource);

        $dataProvider->set($resourceType, $customResources);
    }

    public static function appendCss($resource)
    {
        self::append(self::RESOURCE_TYPE_CSS, $resource);
    }

    /**
     * Run action
     *
     * @param array $input
     * @param Action_Context $context
     * @return array
     */
    protected function run(array $input, Action_Context &$context)
    {
        $resources = [
            self::RESOURCE_TYPE_JS => [],
            self::RESOURCE_TYPE_CSS => []
        ];

        foreach ($this->getConfig()->gets() as $moduleName => $configResources) {
            foreach ($configResources as $resourceKey => $resourceItem) {
                $source = Ice::getConfig()->get('modules/' . $moduleName) . 'Resource/';

                $res = $moduleName . '/' . $resourceKey . '/';

                if ($resourceItem['path']) {
                    $source .= $resourceItem['path'];
                    Dir::copy($source, Dir::get(Ice::getRootPath() . 'resource/' . $res));
                }

                $jsResource = $resourceItem['path']
                    ? Ice::getRootPath() . 'resource/' . $res . $resourceKey . '.pack.js'
                    : Ice::getRootPath() . 'resource/' . $moduleName . '/javascript.pack.js';

                $cssResource = $resourceItem['path']
                    ? Ice::getRootPath() . 'resource/' . $res . $resourceKey . '.pack.css'
                    : Ice::getRootPath() . 'resource/' . $moduleName . '/style.pack.css';

                foreach ($resourceItem[self::RESOURCE_TYPE_JS] as $resource) {
                    $resources[self::RESOURCE_TYPE_JS][] =
                        [
                            'source' => $source . ltrim($resource, '-'),
                            'resource' => $jsResource,
                            'url' => $resourceItem['path']
                                ? '/' . $res . $resourceKey . '.pack.js'
                                : '/' . $moduleName . '/javascript.pack.js',
                            'pack' => $resource[0] != '-'
                        ];
                }

                foreach ($resourceItem[self::RESOURCE_TYPE_CSS] as $resource) {
                    $resources[self::RESOURCE_TYPE_CSS][] =
                        [
                            'source' => $source . ltrim($resource, '-'),
                            'resource' => $cssResource,
                            'url' => $resourceItem['path']
                                ? '/' . $res . $resourceKey . '.pack.css'
                                : '/' . $moduleName . '/style.pack.css',
                            'pack' => $resource[0] != '-'
                        ];
                }
            }
        }

        $resourceName = crc32($input['params']['pattern']);

        $jsFile = $resourceName . '.pack.js';
        $cssFile = $resourceName . '.pack.css';

        $jsRes = Ice::getProject() . '/js/';
        $cssRes = Ice::getProject() . '/css/';

        $jsResource = Dir::get(Ice::getRootPath() . 'resource/' . $jsRes) . $jsFile;
        $cssResource = Dir::get(Ice::getRootPath() . 'resource/' . $cssRes) . $cssFile;

        foreach (array_keys(Action::getCallStack()) as $actionClass) {
            if (file_exists($jsSource = Loader::getFilePath($actionClass, '.js', 'Resource/js', false))) {
                $resources[self::RESOURCE_TYPE_JS][] =
                    [
                        'source' => $jsSource,
                        'resource' => $jsResource,
                        'url' => '/' . $jsRes . $jsFile,
                        'pack' => true
                    ];
            }
            if (file_exists($cssSource = Loader::getFilePath($actionClass, '.css', 'Resource/css', false))) {
                $resources[self::RESOURCE_TYPE_CSS][] =
                    [
                        'source' => $cssSource,
                        'resource' => $cssResource,
                        'url' => '/' . $cssRes . $cssFile,
                        'pack' => true
                    ];
            }
        }

        $jsFile = 'custom.pack.js';
        $cssFile = 'custom.pack.css';

        $jsResource = Dir::get(Ice::getRootPath() . 'resource/' . $jsRes) . $jsFile;
        $cssResource = Dir::get(Ice::getRootPath() . 'resource/' . $cssRes) . $cssFile;

        if (!empty($input['js'])) {
            foreach ($input['js'] as $resource) {
                $resources[self::RESOURCE_TYPE_JS][] =
                    [
                        'source' => Loader::getFilePath($resource, '.js', 'Resource/js'),
                        'resource' => $jsResource,
                        'url' => '/' . $jsRes . $jsFile,
                        'pack' => true
                    ];
            }
        }
        if (!empty($input['css'])) {
            foreach ($input['css'] as $resource) {
                $resources[self::RESOURCE_TYPE_CSS][] =
                    [
                        'source' => Loader::getFilePath($resource, '.css', 'Resource/css'),
                        'resource' => $cssResource,
                        'url' => '/' . $cssRes . $cssFile,
                        'pack' => true
                    ];
            }
        }

        $this->pack($resources);

        return [
            self::RESOURCE_TYPE_JS => array_unique(array_column($resources[self::RESOURCE_TYPE_JS], 'url')),
            self::RESOURCE_TYPE_CSS => array_unique(array_column($resources[self::RESOURCE_TYPE_CSS], 'url'))
        ];
    }

    private function pack($resources)
    {
        if (!class_exists('JSMin', false) && !function_exists('jsmin')) {
            require_once(Ice::getEnginePath() . 'Vendor/JSMin.php');

            function jsmin($js)
            {
                return JSMin::minify($js);
            }
        }

        if (!class_exists('CSSMin', false)) {
            require_once(Ice::getEnginePath() . 'Vendor/CSSmin.php');
        }
        $handlers = [];

        $CSSmin = new CSSMin();

        foreach ($resources[self::RESOURCE_TYPE_JS] as $resource) {
            if (!isset($handlers[$resource['resource']])) {
                $handlers[$resource['resource']] = fopen($resource['resource'], 'w');
            }

            $pack = $resource['pack']
                ? jsmin(file_get_contents($resource['source']))
                : file_get_contents($resource['source']);

            fwrite($handlers[$resource['resource']], '/* Ice: ' . $resource['source'] . "*/\n" . $pack . "\n\n\n");
        }

        foreach ($resources[self::RESOURCE_TYPE_CSS] as $resource) {
            if (!isset($handlers[$resource['resource']])) {
                $handlers[$resource['resource']] = fopen($resource['resource'], 'w');
            }

            $pack = $resource['pack']
                ? $CSSmin->run(file_get_contents($resource['source']))
                : file_get_contents($resource['source']);

            fwrite($handlers[$resource['resource']], '/* Ice: ' . $resource['source'] . "*/\n" . $pack . "\n\n\n");
        }

        foreach ($handlers as $handler) {
            fclose($handler);
        }
    }
}