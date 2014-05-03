<?php

namespace ice\action;

use ice\core\action\Ajax;
use ice\core\Action;
use ice\core\Action_Context;

/**
 * Logout action class
 *
 * Flush session
 *
 * @package ice\action
 * @author dp
 */
class Account_Logout extends Action implements Ajax
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
        $_SESSION = [];
        Session::getCurrent()->delete();

        $redirect = Request::referer();

        if (!empty($input['redirect'])) {
            $redirect = $input['redirect'];
        }

        $redirect = Helper_Uri::validRedirect(
            $redirect ? $redirect : '/'
        );

        return ['redirect' => $redirect];
    }
}