<?php
namespace ice\core;

use ice\core\action\Cli;
use ice\Exception;
use ice\helper\Json;
use ice\helper\Object;
use ice\Ice;

/**
 * Abstract core class action
 *
 * @package ice\core
 * @author dp
 */
abstract class Action
{
    const REGISTRY_DATA_PROVIDER_KEY = 'Registry:action/';

    /** @var array Переопределяемый конфиг */
    public static $config = [];

    /** @var array Стек вызовов экшинов */
    private static $callStack = [];

    /** @var array предопределенные экшины */
    protected $staticActions = [];

    /** @var string|null Emmet style layout */
    protected $layout = null;
    /** @var array Default input data */
    protected $inputDefaults = [];
    /** @var array input data validators */
    protected $inputValidators = [];
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

        $actionContext = null;

        try {
            $actionContext = $action->init();

            $input = $action->getInput($actionContext->getDataProviderKeys(), $data);

            self::pushToCallStack($actionClass, $input);

            if (empty($input['errors'])) {
                $actionContext->setData((array)$action->run($input, $actionContext));
            } else {
                $actionContext->setData($input);
                $view = $actionContext->getView();
                unset($actionContext);
                return $view;
            }

            foreach ($actionContext->getActions() as $subActionClass => $actionData) {
                $level += 1;
                $data = [];

                $subActionClass = Object::getClassByClassShortName(__CLASS__, $subActionClass);

                foreach ($actionData as $subActionKey => $subActionParams) {
                    $data[$subActionKey] = $subActionClass::call($subActionParams, $level);
                }

                $actionContext->getView()->assign(Object::getName($subActionClass), $data);
            }

            $view = $action->flush($actionContext->getView());

            unset($actionContext);

        } catch (\Exception $e) {
            if (isset($actionContext)) {
                unset($actionContext);
            }

            $view = Logger::getMessage($e);

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
        $dataProvider = Data_Provider::getInstance(Ice::getEnvironment()->get('dataProviderKeys/' . __CLASS__));

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
     * Initialization action context
     *
     * @return Action_Context
     */
    protected function init()
    {
        return new Action_Context($this->getClass(), $this->staticActions, $this->getLayout());
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

    /**
     * Return Emmet style layout
     *
     * @return string
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * Gets input data from data providers
     *
     * @param $dataProviderKeys
     * @param array $input
     * @throws \ice\Exception
     * @return array
     */
    private function getInput($dataProviderKeys, array $input)
    {
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

        if (!isset(self::$callStack[$actionName])) {
            self::$callStack[$actionName] = [];
        }

        if (in_array($inputJson, self::$callStack[$actionName])) {
            throw new Exception('action "' . $actionName . '" with input ' . $inputJson . ' already runned. May by found infinite loop.');
        }

        self::$callStack[$actionName][] = $inputJson;
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
        return self::$callStack;
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