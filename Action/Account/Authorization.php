<?php

namespace ice\action;

use ice\core\Action;
use ice\core\Action_Context;
use ice\core\View;
use ice\helper\Object;

/**
 * Authorization form
 *
 * View of authorization form
 *
 * @see \ice\core\Action
 * @see \ice\core\action\View
 *
 * @package ice\action
 * @author dp <denis.a.shestakov@gmail.com>
 * @since -0
 */
class Account_Authorization extends Action implements \ice\core\action\View
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

        $context->setTemplate(Object::getName($this->getClass()) . '_' . $input['accountType']);

        return ['accountType' => $input['accountType']];
    }
}