<?php
namespace ice\core;

use ice\core\action\Cli;
use ice\Exception;
use ice\helper\Json;
use ice\helper\Object;
use ice\Ice;
use ice\view\render\Php;

/**
 * Abstract core class action
 *
 * @package ice\core
 * @author dp <denis.a.shestakov@gmail.com>
 */
abstract class Action
{
    const REGISTRY_DATA_PROVIDER_KEY = 'Registry:action/';

    /** @var array Переопределяемый конфиг */
    protected static $config = [];
    /** @var array Стек вызовов экшинов */
    private static $_callStack = [];
    /** @var array предопределенные экшины */
    protected $staticActions = [];
    /** @var string|null Emmet style layout */
    protected $layout = null;
    /** @var string|null template of view */
    protected $template = '';
    /** @var string|null Render class for view */
    protected $viewRenderClass = null;
    /** @var array Default input data */
    protected $inputDefaults = [];
    /** @var array input data validators */
    protected $inputValidators = [];
    /** @var array inputDataProviders */
    protected $dataProviderKeys = [];
    /** @var array Loaded config */
    private $_config = null;

    /**
     * Приватный конструктор. Создаем через Action::create()
     */
    private function __construct()
    {
    }

    /**
     * Call named action
     *
     * @param array $data
     * @param int $level
     * @return View
     * @throws Exception
     */
    public static function call(array $data = [], $level = 0)
    {
        $view = null;
        $action = null;

        /** @var Action $actionClass */
        $actionClass = get_called_class();

        /** @var Action $action */
        $action = $actionClass::getInstance();

        try {
            $input = $action->getInput($action->dataProviderKeys, $data);

            $dataProvider = Data_Provider::getInstance(Ice::getEnvironment()->get('dataProviderKeys/' . __CLASS__ . '/output'));

            $hash = $actionClass . '/' . crc32(serialize($input));

//            $viewData = $dataProvider->get($hash);
//
//            if ($viewData) {
//                return $action->flush(new View($viewData));
//            }

            self::pushToCallStack($actionClass, $input);

            $actionContext = new Action_Context($actionClass);

            $actionContext->addAction($action->staticActions);
            $actionContext->setLayout($action->layout);

            if (in_array('ice\core\action\View', class_implements($actionClass))) {
                $actionContext->setTemplate(null);
            } else {
                $actionContext->setTemplate($action->template);
            }

            $actionContext->setViewRenderClass($action->viewRenderClass);

            if (empty($input['errors'])) {
                $actionContext->setData((array)$action->run($input, $actionContext));
            } else {
                $actionContext->setData($input);
                $actionContext->setViewRenderClass(Php::VIEW_RENDER_PHP_CLASS);
                $actionContext->setTemplate('Action_Errors');
                $viewData = $actionContext->getViewData();
                unset($actionContext);
                return $action->flush(new View($viewData));
            }

            foreach ($actionContext->getActions() as $subActionClass => $actionData) {
                $level += 1;
                $data = [];

                $subActionClass = Object::getClassByClassShortName(__CLASS__, $subActionClass);

                foreach ($actionData as $subActionKey => $subActionParams) {
                    try {
                        $data[$subActionKey] = $subActionClass::call($subActionParams, $level);
                    } catch (\Exception $e) {
                        $data[$subActionKey] = Logger::getMessageView(new Exception('Calling subAction "' . $subActionClass . '" in action "' . $actionClass . '" failed', [], $e));
                    }
                }

                $actionContext->assign(Object::getName($subActionClass), $data);
            }

            $viewData = $actionContext->getViewData();

            unset($actionContext);

//            $dataProvider->set($hash, $viewData);

            return $action->flush(new View($viewData));

        } catch (\Exception $e) {
            if (isset($actionContext)) {
                unset($actionContext);
            }

            $view = Logger::getMessageView(new Exception('Calling action "' . $actionClass . '" failed', [], $e));

            if ($action instanceof Cli) {
                echo $view . "\n";
            }
        }

        return $view;
    }

    /**
     * Get action object by name
     *
     * @param null $actionName
     * @throws Exception
     * @return Action
     */
    public static function getInstance($actionName = null)
    {
        /** @var Action $actionClass */
        $actionClass = $actionName
            ? Object::getClassByClassShortName(__CLASS__, $actionName)
            : get_called_class();

        /** @var Data_Provider $dataProvider */
        $dataProvider = Data_Provider::getInstance(Ice::getEnvironment()->get('dataProviderKeys/' . __CLASS__ . '/instance'));

        /** @var Action $action */
        $action = $dataProvider->get($actionClass);

        if ($action) {
            return $action;
        }

        $action = new $actionClass();

        $dataProvider->set($actionClass, $action);

        return $action;
    }

    /**
     * Gets input data from data providers
     *
     * @param $dataProviderKeys
     * @param array $input
     * @throws Exception
     * @return array
     */
    private function getInput($dataProviderKeys, array $input)
    {
        $dataProviderKeys = (array) $dataProviderKeys;

        /** @var Action $actionClass */
        $actionClass = get_class($this);
        $dataProviderKeys[] = $actionClass::getRegistryDataProviderKey();

        /** @var Data_Provider $dataProvider */
        $dataProvider = null;
        foreach ($dataProviderKeys as $dataProviderKey) {
            $dataProvider = Data_Provider::getInstance($dataProviderKey);
            $input = array_merge($input, (array)$dataProvider->get());
        }

        foreach ($this->getInputDefaults() as $param => $value) {
            if (empty($input[$param])) {
                $input[$param] = $value;
            }
        }

        $input['errors'] = Validator::validateByScheme($input, $this->getInputValidators());

        return $input;
    }

    /**
     * Return input data defaults
     *
     * @return array
     */
    private function getInputDefaults()
    {
        $config = $this->getConfig();

        if ($config) {
            $inputDefaults = $config->gets('inputDefaults', false);

            if (!empty($inputDefaults)) {
                $this->inputDefaults = array_merge($this->inputDefaults, $inputDefaults);
            }
        }

        return $this->inputDefaults;
    }

    /**
     * Return action config
     *
     * @return Config
     */
    public function getConfig()
    {
        if ($this->_config !== null) {
            return $this->_config;
        }

        $className = $this->getClass();

        $this->_config = Config::getInstance($className, $className::$config);

        return $this->_config;
    }

    /**
     * Return action class
     *
     * @return string
     */
    public static function getClass()
    {
        return get_called_class();
    }

    private function getInputValidators()
    {
        $config = $this->getConfig();

        if ($config) {
            $inputValidators = $config->gets('inputValidators', false);

            if (!empty($inputValidators)) {
                $this->inputValidators = array_merge($this->inputDefaults, $inputValidators);
            }
        }

        return $this->inputValidators;
    }

    /**
     * Push executed action in call stack array
     *
     * @param $actionClass
     * @param $input
     * @throws Exception
     */
    private static function pushToCallStack($actionClass, $input)
    {
        $actionName = Object::getName($actionClass);
        $inputJson = Json::encode($input);

        if (!isset(self::$_callStack[$actionName])) {
            self::$_callStack[$actionName] = [];
        }

        if (!isset(self::$_callStack[$actionName][$inputJson])) {
            self::$_callStack[$actionName][$inputJson] = 0;
        }

        if (self::$_callStack[$actionName][$inputJson] < 5) {
            self::$_callStack[$actionName][$inputJson]++;
//            fb([self::$callStack[$actionName][$inputJson], $actionName, $inputJson]);
            return;
        }

        throw new Exception('action "' . $actionName . '" with input ' . $inputJson . ' already runned (' . self::$_callStack[$actionName][$inputJson] . '). May by found infinite loop.');
    }

    /**
     * Run action
     *
     * @param array $input
     * @param Action_Context $actionContext
     * @return array
     */
    abstract protected function run(array $input, Action_Context &$actionContext);

    /**
     * Flush action context.
     *
     * Modify view after flush
     *
     * @param View $view
     * @return View
     */
    protected function flush(View $view)
    {
        return $view;
    }

    /**
     * Return current action call stack
     *
     * @return array
     */
    public static function getCallStack()
    {
        return self::$_callStack;
    }

    /**
     * Get hash of input data
     *
     * @param $data
     * @return string
     */
    public static function getHash($data)
    {
        return (hash('crc32b', igbinary_serialize($data)));
    }

    /**
     * Return data provider key of action
     *
     * @return string
     */
    public static function getRegistryDataProviderKey()
    {
        return self::REGISTRY_DATA_PROVIDER_KEY . get_called_class();
    }
}