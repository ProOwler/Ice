<?php
namespace ice\core;

use ice\data\provider\File;
use ice\data\provider\Mysqli;
use ice\Exception;
use ice\helper\Emmet;
use ice\helper\Object;
use ice\Ice;

/**
 * Core view class
 *
 * @package ice\core
 * @author dp <denis.a.shestakov@gmail.com>
 */
class View
{
    private $_viewData = [];
    private $_result = null;

    public function __construct(array $viewData)
    {
        $this->_viewData = $viewData;
    }

    public function display()
    {
        $output = $this->getOutput();

        if ($output) {
            $dataProvider = Data_Provider::getInstance($output);
            if ($dataProvider instanceof File && $dataProvider->getConnection()) {
                \ice\helper\File::createData($dataProvider->getScheme(), $this->fetch(), false);
            } else if ($dataProvider instanceof Mysqli) {
                $dataProvider->getConnection()->multi_query($this->fetch());
            } else {
                throw new Exception('Data provider not support');
            }
        } else {
            echo $this->fetch();
        }
    }

    public function fetch()
    {
        if ($this->_result != null) {
            return $this->_result;
        }

        $template = $this->getTemplate();

        if (empty($template)) {
            $this->_result = '';
            return $this->_result;
        }

        $hash = crc32(serialize($this->_viewData['data']));

        $dataProvider = Data_Provider::getInstance(Ice::getEnvironment()->get('dataProviderKeys/' . __CLASS__));

        $this->_result = $dataProvider->get($hash);

        if ($this->_result) {
            return $this->_result;
        }

        $prefix = Object::getPrefixByClassShortName(Action::getClass(), $this->_viewData['actionClass']);

        /** @var View_Render $viewRenderClass */
        $viewRenderClass = $this->getViewRenderClass();
        $ext = $viewRenderClass::TEMPLATE_EXTENTION;

        array_unshift(View_Render::$templates, $template . $ext);

        try {
            $this->_result = $viewRenderClass::getInstance()->fetch($template, $this->_viewData['data'], $prefix, $ext);
            $dataProvider->set($hash, $this->_result);
        } catch (\Exception $e) {
            $this->_result = Logger::getMessageView(new Exception('Fetch template "' . $template . $ext . '" failed', [], $e));
        }

        array_shift(View_Render::$templates);

        $layout = $this->getLayout();

        return empty($layout)
            ? $this->_result
            : Emmet::translate($this->getLayout(), ['view' => $this->_result]);
    }

    public function getTemplate()
    {
        if ($this->_viewData['template'] !== null) {
            return str_replace(array('_', '::'), '/', $this->_viewData['template']);
        }

        $actionClass = $this->_viewData['actionClass'];

        $this->_viewData['template'] = in_array('ice\core\action\View', class_implements($actionClass))
            ? str_replace(array('_', '::'), '/', Object::getName($actionClass))
            : '';

        return $this->_viewData['template'];
    }

    /**
     * @return string
     */
    public function getViewRenderClass()
    {
        if (isset($this->_viewData['viewRenderClass'])) {
            return $this->_viewData['viewRenderClass'];
        }

        return Ice::getConfig()->get('defaultViewRenderClass');
    }

    public function getOutput()
    {
        return $this->_viewData['output'];
    }

    /**
     * @return string
     */
    public function getLayout()
    {
        if ($this->_viewData['layout'] !== null) {
            return $this->_viewData['layout'];
        }

        $actionClass = $this->_viewData['actionClass'];

        $this->_viewData['layout'] = in_array('ice\core\action\Cli', class_implements($actionClass))
            ? ''
            : (
            Ice::getConfig()->get('defaultLayoutView', false) === null
                ? 'div#' . Object::getName($actionClass) . '{{$view}}'
                : Ice::getConfig()->get('defaultLayoutView', false)
            );
        return $this->_viewData['layout'];
    }

    public function __toString()
    {
        try {
            return $this->fetch();
        } catch (\Exception $e) {
            return Logger::getMessageView($e);
        }
    }
}