<?php
namespace ice;

use ice\core\action\Front;
use ice\core\action\Front_Ajax;
use ice\core\action\Front_Cli;
use ice\core\Config;
use ice\core\Loader;
use ice\core\Logger;
use ice\core\Request;
use ice\core\View;

/**
 * Class of Ice application
 *
 * Ice is a singleton)
 *
 * @todo Write documentation
 *
 * @link http://iceframework.net Ice home page @endlink
 *
 * @package ice
 * @author dp
 */
class Ice
{
    //! string Framework name
    const ENGINE = 'Ice';

    //! string Framework version
    const VERSION = '-0';

    // @var Ice Instance of ice application
    private static $_ice = null;

    /**
     * Config of ice application
     *
     * @var Config
     */
    private static $_config = null;

    private static $_environment = null;

    private static $_modules = null;

    /**
     * Main module name
     *
     * @var string
     */
    private static $_project = null;

    /**
     * Main module path
     *
     * @var string
     */
    private static $_projectPath = null;

    /**
     * Root path of modules (includes Ice and other modules)
     *
     * @todo check. May be is deprecated (modules can be placed in other pathes)
     * @var string
     */
    private static $_rootPath = null;

    /**
     * Ice module path
     *
     * @var string
     */
    private static $_enginePath = null;


    /**
     *  Result view of application
     *
     * @var View
     */
    private $view = null;

    /**
     * Private constructor of ice application
     *
     * @param $project
     */
    private function __construct($project)
    {
        self::$_project = $project;
        $this->bootstrap();
    }

    /**
     * bootstrap method
     *
     *  - init environment variables
     *  - includes required files
     *  - register autoloader of classes
     *  - init logger
     */
    private function bootstrap()
    {
        setlocale(LC_ALL, 'ru_RU.UTF-8');
        setlocale(LC_NUMERIC, 'C');

        date_default_timezone_set('UTC');

        require_once $this->getEnginePath() . 'Exception.php';
        require_once $this->getEnginePath() . 'Helper/Object.php';
        require_once $this->getEnginePath() . 'Core/Config.php';
        require_once $this->getEnginePath() . 'Core/Data/Provider.php';
        require_once $this->getEnginePath() . 'Data/Provider/Registry.php';
        require_once $this->getEnginePath() . 'Core/Loader.php';
        require_once $this->getEnginePath() . 'Core/Request.php';

        Loader::register('ice\core\Loader::load');
        Logger::init(Ice::getEnvironment()->get('debug'));
    }

    /**
     * Return engine path
     *
     * @return string
     */
    public static function getEnginePath()
    {
        if (self::$_enginePath !== null) {
            return self::$_enginePath;
        }

        self::$_enginePath = __DIR__ . '/';
        return self::$_enginePath;
    }

    /**
     * @return Config
     * @throws Exception
     */
    public static function getEnvironment()
    {
        if (self::$_environment !== null) {
            return self::$_environment;
        }

        self::$_environment = new Config(self::getConfig()->gets('environment'), 'Environment');

        return self::$_environment;
    }

    /**
     * Return main config of ice application
     *
     * @throws Exception
     * @return Config
     */
    public static function getConfig()
    {
        if (self::$_config !== null) {
            return self::$_config;
        }

        $configFileCache = Ice::getProjectPath() . Ice::getProject() . '.conf.cache.php';
//
//        if (file_exists($configFileCache)) {
//            self::$_config = new Config(include $configFileCache, __CLASS__);
//
//            return self::$_config;
//        }

        $_configFile = Ice::getProjectPath() . Ice::getProject() . '.conf.php';

        $config = file_exists($_configFile)
            ? include $_configFile
            : include Ice::getEnginePath() . self::ENGINE . '.conf.php';

        $_config = [];
        foreach ($config['modules'] as $moduleName => $modulePath) {
            $configFile = $modulePath . $moduleName . '.conf.php';

            if (!file_exists($configFile)) {
                die('Could not found module config by path "' . $configFile . '" in ' . $_configFile);
            }

            $_config = array_merge_recursive($_config, include $configFile);
        }

        ini_set('xdebug.var_display_max_depth', -1);

        $host = Request::host();
        foreach ($_config['hosts'] as $pattern => $environment) {
            $matches = [];
            preg_match($pattern, $host, $matches);
            if (!empty($matches)) {
                $_config['host'] = $environment;
                break;
            }
        }
        unset($_config['hosts']);

        foreach ($_config['environments'] as $environment => $settings) {
            $_config['environment'] = array_merge_recursive($settings, $_config['environment']);
            if ($environment == $_config['host']) {
                break;
            }
        }
        unset($_config['environments']);

        file_put_contents($configFileCache, '<?php' . "\n" . 'return ' . var_export($_config, true) . ';');

        self::$_config = new Config($_config, __CLASS__);

        return self::$_config;
    }

    /**
     * Return path of main module
     *
     * @return string
     */
    public static function getProjectPath()
    {
        if (self::$_projectPath !== null) {
            return self::$_projectPath;
        }

        self::$_projectPath = self::getRootPath() . self::getProject() . '/';
        return self::$_projectPath;
    }

    /**
     * Return root path of all modules
     *
     * @todo check. May be is deprecated (modules can be placed in other pathes)
     * @return string
     */
    public static function getRootPath()
    {
        if (self::$_rootPath !== null) {
            return self::$_rootPath;
        }

        self::$_rootPath = dirname(self::getEnginePath()) . '/';
        return self::$_rootPath;
    }

    /**
     * Return main module name
     *
     * @return string
     */
    public static function getProject()
    {
        return self::$_project;
    }

    /**
     * Return instance of Ice application
     *
     * @param $project
     * @return Ice
     */
    public static function get($project)
    {
        if (self::$_ice !== null) {
            return self::$_ice;
        }

        self::$_ice = new Ice($project);

        return self::$_ice;
    }

    /**
     * @return array
     * @throws Exception
     */
    public static function getModules()
    {
        if (self::$_modules !== null) {
            return self::$_modules;
        }

        self::$_modules = [];
        foreach (self::getConfig()->gets('modules') as $moduleName => $modulePath) {
            self::$_modules[$moduleName] = is_array($modulePath)
                ? reset($modulePath)
                : $modulePath;
        }

        return self::$_modules;
    }

    /**
     * Run executing actions
     *
     * Hierarhical call of actions
     *
     * @return Ice
     */
    public function run()
    {
        try {
            if (!empty($_SERVER['argv'])) {
                $this->view = Front_Cli::call();
                return $this;
            }

            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
                $this->view = Front_Ajax::call();
                return $this;
            }

            $this->view = Front::call();
        } catch (\Exception $e) {
            Logger::output(Logger::getMessageView($e));
        }

        return $this;
    }


    /**
     * Flushing ice application
     *
     * Display rendered view and close opened descriptors and resources
     *
     */
    public function flush()
    {
        if (!($this->view instanceof View)) {
            die($this->view);
        }

        $this->view->display();
    }
}