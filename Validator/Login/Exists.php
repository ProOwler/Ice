<?php
namespace ice\validator;

use ice\core\Validator;
use ice\model\ice\Account;

/**
 * Class Data_Validator_Phone_Exists
 * Проверка в базе существования логина
 */
class Login_Exists extends Validator
{
    /**
     * Validate data by scheme
     *
     * @example:
     *  'user_name' => [
     *      [
     *          'validator' => 'Ice:Not_Empty',
     *          'message' => 'Введите имя пользователя.'
     *      ],
     *  ],
     *  'name' => 'Ice:Not_Null'
     *
     * @param $data
     * @param null $scheme
     * @return boolean
     */
    public function validate($data, $scheme = null)
    {
        return !Account::getQueryBuilder()
            ->select('/pk')
            ->eq('login', $data)
            ->limit(1)
            ->execute()
            ->getValue();
    }
}