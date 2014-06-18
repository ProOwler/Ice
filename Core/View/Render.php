<?php
namespace ice\core;

use ice\helper\Object;
use ice\Ice;

abstract class View_Render
{
    public static $config = [];

    public static $templates = [];

    private $_config = null;

    private function __construct(array $config = [])
    {
        $this->_config = Config::getInstance($this->getClass(), array_merge(self::$config, $config));
    }

    public static function getClass()
    {
        return get_called_class();
    }

    /**
     * @return View_Render
     */
    public static function getInstance()
    {
        /** @var View_Render $viewRenderClass */
        $viewRenderClass = self::getClass();

        $config = Ice::getConfig()->gets('viewRenders/' . Object::getName($viewRenderClass));

        $dataProviderKey = isset($config['dataProviderKey'])
            ? Ice::getConfig()->get('viewRenders/' . Object::getName($viewRenderClass) . '/dataProviderKey')
            : Ice::getEnvironment()->get('dataProviderKeys/' . __CLASS__);

        /** @var Data_Provider $dataProvider */
        $dataProvider = Data_Provider::getInstance($dataProviderKey);

        $viewRender = $dataProvider->get($viewRenderClass); //$viewRender = null;

        if ($viewRender) {
            return $viewRender;
        }

        /** @var View_Render $viewRender */
        $viewRender = new $viewRenderClass($config);
        $viewRender->init();

        $dataProvider->set($viewRenderClass, $viewRender);

        return $viewRender;
    }

    abstract public function init();

    abstract public function display($template, array $data = [], $prefix, $ext);

    abstract public function fetch($template, array $data = [], $prefix, $ext);

    public function getConfig()
    {
        return $this->_config;
    }
}