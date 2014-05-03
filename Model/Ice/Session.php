<?php
namespace ice\model\ice;

use ice\core\Data_Provider;
use ice\core\Data_Source;
use ice\helper\Date;
use ice\core\Request;
use ice\core\Model;

class Session extends Model
{
    /**
     * @desc Текущая сессия
     *
     * var Session
     */
    private static $_session = null;

    /**
     * Получаем текущую сессию
     *
     * @return Session|null
     */
    public static function getCurrent()
    {
        if (self::$_session) {
            return self::$_session;
        }

        $sessionPk = Data_Provider::getInstance('Session:php/')->get('PHPSESSID');

        self::$_session = Session::getModel($sessionPk, ['/pk', 'user__fk', 'data__json']);

        if (self::$_session) {
            self::$_session->update('last_active', Date::getCurrent());

            return self::$_session;
        }

        return self::$_session = Session::create(
            [
                'session_pk' => $sessionPk,
                'last_active' => Date::getCurrent(),
                'ip' => Request::ip(),
                'user_agent' => Request::agent(),
                'auth_date' => Date::getCurrent(),
                'user__fk' => User::getGuest()->getPk()
            ]
        )->insert();
    }

//    /**
//     * @param Account $account
//     */
//    public static function switchAccount($account)
//    {
//        $user = $account->getUser();
//
//        if ($user) {
//            Session::getCurrent()->update(
//                [
//                    'account__fk' => $account->getPk(),
//                    'last_active' => Helper_Date::toUnix(),
//                    'auth_date' => Helper_Date::toUnix(),
//                    '_use_pk' => $user->getPk()
//                ]
//            );
//        }
//    }

    /**
     * Очистить сессию
     */
    public static function clearSession()
    {
        $session = self::getCurrent();
        $session->delete();
        self::$_session = null;
    }

    /**
     * Установка юзера в сессию
     *
     * @param User $user
     */
    public function switchUser(User $user)
    {
        $update = [
            'user__fk' => $user->getPk(),
            'auth_date' => Date::getCurrent(),
        ];

        $this->update($update);
    }

    /**
     * установка города в сессию
     *
     * @param City $city
     */
    public function switchCity(City $city)
    {
        $this->update('city__fk', $city->getPk());

        $city->update('view_count', $city->view_count + 1);
    }
}