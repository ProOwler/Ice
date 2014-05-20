<?php
namespace ice\core\action;

use Controller_Manager;
use ice\core\Action;
use ice\core\Action_Context;
use ice\Exception;

/**
 * Legacy action
 *
 * Action of call Controller/action for IcEngine compatibility
 * @link https://code.google.com/p/icengine/
 *
 * @package ice\core\action
 * @author dp <denis.a.shestakov@gmail.com>
 */
class Legacy extends Action
{
    /**
     * Run action
     *
     * @param array $input
     * @param Action_Context $context
     * @return array
     */
    protected function run(array $input, Action_Context &$context)
    {
        throw new Exception(get_class($this), $input);
        $controllerAction = explode('/', $input['action']);

        unset($input['action']);

        $controllerTask = Controller_Manager::call($controllerAction[0], $controllerAction[1], $input);

        $output = $controllerTask->getTransaction()->buffer();
        $output['template'] = $controllerTask->getTemplate();

        return $output;
    }

    /**
     * Flush action context.
     *
     * Modify view after flush
     *
     * @param View $view
     * @return View
     */
    protected function flush(\ice\core\View $view)
    {
        $view = parent::flush($view);

        /** @var View[] $data */
        $data = $view->getData();

        if (isset($data['template'])) {
            $view->setTemplate($data['template']);
        }

        return $view;
    }
}