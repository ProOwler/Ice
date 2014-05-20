<?php
namespace ice\core;

use ice\core\action\Legacy;
use ice\Exception;
use ice\helper\Object;

/**
 * Context of action
 *
 * @package ice\core
 * @author dp <denis.a.shestakov@gmail.com>
 */
class Action_Context
{
    /** @var array childActions */
    private $_actions = [];

    /** @var null action class */
    private $_actionClass = null;

    private $_layout = null;

    private $_template = '';

    private $_data = [];

    private $_viewRenderClass = null;

    /**
     * Constructor of action context
     *
     * @param $actionClass
     * @internal param array $actions
     * @internal param $layout
     */
    public function __construct($actionClass)
    {
        $this->_actionClass = $actionClass;
    }

    /**
     * Add child action
     *
     * 'Ice:Title',
     * 'title_template_var1' => 'Ice:Title',
     * 'Ice:Title' => ['title' => 'text'],
     * 'title_template_var2' => ['Ice:Title', ['title' => 'text']]
     *
     * @param $actionName
     * @param array $params
     * @param null $key
     * @throws Exception
     */
    public function addAction($actionName, array $params = [], $key = null)
    {
        if (empty($actionName)) {
            return;
        }

        if (is_array($actionName)) {
            foreach ($actionName as $actionKey => $actionData) {
                if (is_numeric($actionKey)) {
                    $this->addAction($actionData);
                    continue;
                }

                if (!is_array($actionData)) {
                    $this->addAction($actionData, [], $actionKey);
                    continue;
                }

                if (is_numeric(each($actionData)['key'])) {
                    $this->addAction(array_shift($actionData), array_shift($actionData), $actionKey);
                    continue;
                }

                $this->addAction($actionKey, $actionData, $key);
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

    /**
     * Set view template
     *
     * @param $template
     */
    public function setTemplate($template)
    {
        $this->_template = $template;
    }

    /**
     * Return viewData
     *
     * @return array
     */
    public function getViewData()
    {
        return [
            'actionName' => Object::getName($this->_actionClass),
            'layout' => $this->_layout,
            'template' => $this->_template,
            'viewRenderClass' => $this->_viewRenderClass,
            'data' => $this->_data
        ];
    }

    public function assign($key, $value)
    {
        foreach ($value as $index => $val) {
            if (is_int($index)) {
                $this->_data[$key][$index] = $val;
            } else {
                $this->_data[$index] = $val;
            }
        }
    }

    /**
     * Assign data to view
     *
     * @param $output
     */
    public function setData($output)
    {
        $this->_data = $output;
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
        $this->_viewRenderClass = $viewRenderClass;
    }

    public function setLayout($layout)
    {
        $this->_layout = $layout;
    }
} 