<?php

namespace ice\action;

use ice\core\Action;
use ice\core\Action_Context;
use ice\core\View;
use ice\helper\Object;

/**
 * Registration form
 *
 * View of registratiion form
 *
 * @package ice\action
 * @author dp
 */
class Account_Registration extends Action implements \ice\core\action\View
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
//        if (!User::isGuest()) {
//            Helper_Header::redirect('/');
//        }

        return ['accountType' => $input['accountType']];
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
        $view->setTemplate(Object::getName($this->getClass()) . '_' . $view->getData()['accountType']);
        return $view;
    }
}