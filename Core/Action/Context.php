<?php
namespace ice\core;

use ice\core\action\Legacy;
use ice\Exception;
use ice\core\View;

/**
 * Context of action
 *
 * @package ice\core
 * @author dp
 */
class Action_Context
{
    /** @var array childActions */
    private $_actions = [];

    /** @var null action class */
    private $_actionClass = null;

    /** @var View view */
    private $_view;

    /** @var array inputDataProviders */
    private $_dataProviderKeys = [];

    /**
     * Constructor of action context
     *
     * @param $actionClass
     * @param array $actions
     * @param $layout
     */
    public function __construct($actionClass, array $actions, $layout)
    {
        $this->_actionClass = $actionClass;

        if (!empty($actions)) {
            $this->addAction($actions);
        }

        $this->_view = new View($actionClass, $layout);

        if (in_array('ice\core\action\View', class_implements($actionClass))) {
            $this->setTemplate(null);
        }
//
//        if (in_array('ice\core\action\Cli', class_implements($actionClass))) {
//            $this->addDataProviderKeys('Cli:prompt/');
//        }
    }

    /**
     * Push data provider key
     *
     * @param array $dataProviderKeys
     */
    public function addDataProviderKeys($dataProviderKeys)
    {
        $this->_dataProviderKeys = array_merge($this->_dataProviderKeys, (array)$dataProviderKeys);
    }

    /**
     * Return input data provider keys
     *
     * @return array
     */
    public function getDataProviderKeys()
    {
        return $this->_dataProviderKeys;
    }

    /**
     * Set view template
     *
     * @param $template
     */
    public function setTemplate($template)
    {
        $this->_view->setTemplate($template);
    }

    /**
     * Return view
     *
     * @return View
     */
    public function getView()
    {
        return $this->_view;
    }

    /**
     * Assign data to view
     *
     * @param $output
     */
    public function setData($output)
    {
        $this->_view->setData($output);
    }

    /**
     * Return assigned data from view
     *
     * @return array
     */
    public function getData()
    {
        return $this->_view->getData();
    }

    /**
     * Return child actions
     *
     * @return array
     */
    public function getActions()
    {
        return $this->_actions;
    }

    /**
     * Get view render class
     *
     * @param $viewRenderClass
     */
    public function setViewRenderClass($viewRenderClass)
    {
        $this->_view->setViewRenderClass($viewRenderClass);
    }

    /**
     * Add child action
     *
     * @param $actionName
     * @param array $params
     * @param null $key
     * @throws Exception
     */
    public function addAction($actionName, array $params = [], $key = null)
    {
        if (empty($actionName)) {
            throw new Exception('$actionName could not by empty');
        }

        if (is_array($actionName)) {
            foreach ($actionName as $actionKey => $actionData) {
                if (!is_array($actionData)) {
                    $this->addAction($actionData, $params, $key);
                    continue;
                }

                $this->addAction($actionKey, (array)$actionData, $key);
            }

            return;
        }

        /**
         * Legacy support
         *
         * IcEngine compatibility
         */
        if (strpos($actionName, '/')) {
            $params['action'] = $actionName;
            $actionName = Legacy::getClass();
        }

        if (!isset($this->_actions[$actionName])) {
            $this->_actions = [$actionName => []] + $this->_actions;
        }

        if ($key) {
            $this->_actions[$actionName][$key] = $params;
        } else {
            $this->_actions[$actionName][] = $params;
        }
    }
} 