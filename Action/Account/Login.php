<?php
namespace ice\action;

use ice\core\action\Ajax;
use ice\core\Action;
use ice\core\Action_Context;
use ice\model\ice\Account_Type;
use ice\model\ice\Account_Type_Exception;

/**
 * Login action class
 *
 * Action of authorization user
 *
 * @see \ice\core\Action
 * @see \ice\core\action\Ajax
 *
 * @package ice\action
 * @author dp <denis.a.shestakov@gmail.com>
 * @since -0
 */
class Account_Login extends Action implements Ajax
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
        $errorMessage = '';

        try {
            /** @var Account_Type $accountType */
            $accountType = Account_Type::getDelegate($input['accountType']);

            if (!$accountType) {
                throw new Account_Exception('Учетная запись заданного типа "' . $input['accountType'] . '" не может быть получена');
            }

            $errors = $accountType->check($input, 'Login');

            if (empty($errors)) {
                $accountType->login($input);
            } else {
                $errorMessage = reset($errors);
            }

        } catch (Account_Exception $e) {
            $errorMessage = $e->getMessage();
        } catch (Account_Type_Exception $e) {
            $errorMessage = $e->getMessage();
        }

        if (!empty($errorMessage)) {
            return [
                'error' => ['message' => $errorMessage],
                'hasError' => 1
            ];
        }

        return $input;
    }
}