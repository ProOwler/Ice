<?php
namespace ice\view\render;

use ice\core\Loader;
use ice\core\Logger;
use ice\core\View_Render;
use ice\Exception;
use ice\Ice;

class Php extends View_Render
{
    const VIEW_RENDER_PHP_CLASS = 'ice\view\render\Php';
    const TEMPLATE_EXTENTION = '.php';

    public function init()
    {
    }

    public function display($template, array $data = [], $prefix, $ext)
    {
        extract($data);
        unset($data);

        $templateName = Loader::getFilePath($template, $ext, 'View/Template/' . $prefix);

        try {
            require $templateName;
        } catch (\Exception $e) {
            throw new Exception('Render error in template "' . $templateName . '"', [], $e);
        }
    }

    public function fetch($template, array $data = [], $prefix, $ext)
    {
        extract($data);
        unset($data);

        $templateName = Loader::getFilePath($template, $ext, 'View/Template/' . $prefix);

        ob_start();
        ob_implicit_flush(false);

        $view = null;

        try {
            require $templateName;
            $view = ob_get_clean();
        } catch (\Exception $e) {
            $view = Logger::getMessageView(new Exception('Render error in template "' . $templateName . '"' . "\n" . ob_get_clean(), [], $e));
        }
        return $view;
    }
}