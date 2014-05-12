<?php
namespace ice\model\ice;

use ice\core\model\Factory_Delegate;
use ice\core\Request;
use ice\helper\Date;

class Account_Type_Login_Password extends Account_Type implements Factory_Delegate
{
    /**
     * Регистрация пользователя
     *
     * @param array $data
     *
     * @throws Account_Type_Exception
     * @return Account
     */
    public function register(array $data)
    {
        return Account::create(
            [
                'account_type' => Account_Type::getDelegate($data['accountType']),
                'ip' => Request::ip(),
                'reg_date' => Date::getCurrent(),
                'login' => $data['login'],
                'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                'user' => User::getNewUser($data['login'])
            ]
        )->insert();
    }

    /**
     * Авторизация пользователя
     *
     * @param array $data
     *
     * @throws Account_Type_Exception
     * @return User
     */
    public function login(array $data)
    {
        $account = Account::getQueryBuilder()
            ->select(array('password', 'user__fk', 'User/name'))
            ->inner(User::getClass())
            ->eq('login', $data['login'])
            ->limit(1)
            ->execute()
            ->getModel();

        if (!$account) {
            throw new Account_Type_Exception('Пользователь не зарегистрирован.');
        }

        if (!password_verify($data['password'], $account->get('password'))) {
            throw new Account_Type_Exception('Отказано в доступе. Проверьте введенный пароль.');
        }

        User::setCurrent($account->get(User::getClass()));

        return User::getCurrent();
    }
}