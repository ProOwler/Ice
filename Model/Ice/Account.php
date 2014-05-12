<?php
namespace ice\model\ice;

use ice\core\Model;
use ice\core\Request;
use ice\Exception;

class Account extends Model
{
    const AUTHORIZATION_URL = '/authorization/';
    const REGISTRATION_URL = '/registration/';

    public static function getNewAccount($accountTypeDelegateName, array $fields)
    {
        $accountType = Account_Type::getDelegate($accountTypeDelegateName);

        if (!$accountType) {
            throw new Exception('Не получен тип учетной записи по имени делегата ' . $accountTypeDelegateName);
        }

        $fields['ip'] = Request::ip();
        $fields['account_type__fk'] = $accountType->key();

        return Account::create($fields)->insert();
    }

    public function getUser()
    {
        return User::getQueryBuilder()
            ->innerJoin('User_Account_Link')
            ->eq('User_Account_Link.account__fk', $this->key())
            ->execute()
            ->getModel();
    }
}
