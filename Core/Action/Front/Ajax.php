<?php
namespace ice\core\action;

use ice\core\Action;
use ice\core\Action_Context;
use ice\core\View;
use ice\data\provider\Request;
use ice\helper\Object;
use ice\view\render\Php;

class Front_Ajax extends Action implements Ajax, \ice\core\action\View
{
    protected $viewRenderClass = Php::VIEW_RENDER_PHP_CLASS;
    protected $dataProviderKeys = Request::DEFAULT_KEY;

    /**
     * Run action
     *
     * @param array $input
     * @param Action_Context $context
     * @return array
     */
    protected function run(array $input, Action_Context &$context)
    {
        if (strpos($input['call'], '/')) {
            $input['params']['controllerAction'] = $input['call'];
            $input['call'] = Legacy::getClass();
        }

        $context->addAction($input['call'], $input['params']);

        return [
            'frontAjax' => ['Action' => Object::getName($input['call'])],
            'back' => $input['back']
        ];
    }

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
        $view = parent::flush($view);

        /** @var View[] $data */
        $data = $view->getData();

        foreach ($data['frontAjax'] as &$action) {
            $action = [
                'back' => $data['back'],
                'result' => [
                    'data' => $data[$action][0]->getData(),
                    'html' => $data[$action][0]->fetch()
                ]
            ];
        }

        $view->setData($data);

        return $view;
    }
}