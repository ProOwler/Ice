<?php
namespace ice\view\render;

use ice\core\Loader;
use ice\core\View_Render;
use ice\Ice;

class Smarty extends View_Render
{
    const VIEW_RENDER_SMARTY_CLASS = 'ice\view\render\Smarty';
    const TEMPLATE_EXTENTION = '.tpl';

    public static $config = [
        'dataProviderKey' => 'Registry:view_render/smarty'
    ];

    /** @var \Smarty */
    private $_smarty = null;

    public function init()
    {
        require_once $this->getConfig()->get('class');

        $this->_smarty = new \Smarty();

        Loader::register('\smartyAutoload');

        $templateDirs = [];

        foreach (Ice::getModules() as $modulePath) {
            $templateDirs[] = $modulePath . 'View/Template';
        }
        $this->_smarty->setTemplateDir($templateDirs);

        $this->_smarty->setCompileDir($this->getConfig()->get('templates_c') . Ice::getProject());
        $this->_smarty->addPluginsDir($this->getConfig()->get('plugins', false));
//        $this->_smarty->setCacheDir('/web/www.example.com/smarty/cache');
//        $this->_smarty->setConfigDir('/web/www.example.com/smarty/configs');
        $this->_smarty->debugging = true;
    }

    public function display($template, array $data = [], $ext)
    {
        /** @var \Smarty_Internal_Template $smartyTemplate */
        $smartyTemplate = $this->_smarty->createTemplate($template . $ext);

        foreach ($data as $key => $value) {
            $smartyTemplate->assign($key, $value);
        }

        $smartyTemplate->display();
    }

    public function fetch($template, array $data = [], $ext)
    {
        $templateName = $template . $ext;

        /** @var \Smarty_Internal_Template $smartyTemplate */
        $smartyTemplate = $this->_smarty->createTemplate($templateName);

        foreach ($data as $key => $value) {
            $smartyTemplate->assign($key, $value);
        }

        $view = null;

        $view = $smartyTemplate->fetch();


        return $view;
    }
}