<?php
namespace ice\core;

use ice\helper\Object;
use ice\Exception;
use ice\Ice;

/**
 * Core view class
 *
 * @package ice\core
 * @author dp
 */
class View
{
    private $_viewRenderClass = null;
    private $_actionName = null;
    private $_template = '';
    private $_layout = null;
    private $_data = [];
    private $_view = null;

    public function __construct($actionClass, $layout)
    {
        $this->_actionName = Object::getName($actionClass);
        $this->_layout = $layout;
    }

    public function getTemplate()
    {
        if ($this->_template === null) {
            $this->_template = $this->_actionName;
        }

        return str_replace(array('_', '::'), '/', $this->_template);
    }

    public function setTemplate($template)
    {
        $this->_template = $template;
    }

    /**
     * @param string $layout
     */
    public function setLayout($layout)
    {
        $this->_layout = $layout;
    }

    /**
     * @return string
     */
    public function getLayout()
    {
        if ($this->_layout === null) {
            $this->_layout = 'div#' . $this->_actionName . '{$view}';
        }

        return $this->_layout;
    }

    /**
     * @param null $viewRenderClass
     */
    public function setViewRenderClass($viewRenderClass)
    {
        $this->_viewRenderClass = $viewRenderClass;
    }

    /**
     * @return string
     */
    public function getViewRenderClass()
    {
        if ($this->_viewRenderClass) {
            return $this->_viewRenderClass;
        }

        return Ice::getConfig()->get('defaultViewRenderClass');
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }

    public function setData(array $data)
    {
        $this->_data = $data;
    }

    /**
     * Получить результат рендера шаблона
     *
     * @throws Exception
     * @return string
     */
    public function render()
    {
        if ($this->_view !== null) {
            return $this->_view;
        }

        try {
            $this->_view = $this->fetch();
        } catch (\Exception $e) {
            $this->_view = '';
            $viewRenderClass = $this->getViewRenderClass();
            Logger::getMessage(
                new Exception('Не удалось отрендерить шаблон "' . $this->_template . $viewRenderClass::TEMPLATE_EXTENTION . '"', $e)
            );
        }


        return $this->_view;
    }

    private function fetch()
    {
        $template = $this->getTemplate();

        if (empty($template)) {
            return '';
        }

        /** @var View_Render $viewRenderClass */
        $viewRenderClass = $this->getViewRenderClass();

        return $viewRenderClass::getInstance()->fetch($template, $this->getData(), $viewRenderClass::TEMPLATE_EXTENTION);
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

    public function display()
    {
        $template = $this->getTemplate();

        if (empty($template)) {
            return '';
        }

        /** @var View_Render $viewRenderClass */
        $viewRenderClass = $this->getViewRenderClass();

        $viewRenderClass::getInstance()->display($template, $this->getData(), $viewRenderClass::TEMPLATE_EXTENTION);
    }

    public function __toString()
    {
        return $this->render();
    }
}