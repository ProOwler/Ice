<?php
namespace ice\model\ice;

use ice\core\Model;

class User extends Model
{
    const GUEST_USER_ID = 10;
    const GUEST_USER_NAME = 'Гость';
    const ROLE_ADMIN = 'Admin';
    /**
     * Текущий пользователь
     *
     * @var User
     */
    private static $_user = null;

    /**
     * Является ли пользователь гостем или автоирзован на сайте
     *
     * @return bool
     */
    public static function isGuest()
    {
        return self::getCurrent()->getPk() == self::GUEST_USER_ID;
    }

    /**
     * Вернуть текущего пользователя
     *
     * @throws Page_Not_Found_Exception
     * @return User
     */
    public static function getCurrent()
    {
        if (self::$_user) {
            return self::$_user;
        }

        $session = Session::getCurrent();

        self::$_user = $session->get(User::getClass());

        if (self::$_user) {
            return self::$_user;
        }

        $user = User::getGuest();

        if (!$user) {
            throw new Page_Not_Found_Exception('Не удалось создать пользователя "Гость"');
        }

        User::setCurrent($user);

        return self::$_user;
    }

    public static function getGuest()
    {
        $user = User::getModel(User::GUEST_USER_ID, '/pk');

        if ($user) {
            return $user;
        }

        return User::getNewUser(User::GUEST_USER_NAME, User::GUEST_USER_ID);
    }

    public static function getNewUser($user_name, $user_pk = null)
    {
        return User::create(
            [
                'user_pk' => $user_pk,
                'user_name' => $user_name
            ]
        )->insert();
    }

    /**
     * Устанавливаем текущего юзера
     *
     * @param User $user
     */
    public static function setCurrent(User $user)
    {
        Session::getCurrent()->switchUser($user);
        self::$_user = $user;
    }

    /**
     * Есть ли у пользователя роль админа
     *
     * @return bool
     */
    public function isAdmin()
    {
        return (bool)$this->getRole(self::ROLE_ADMIN);
    }

    public function getRole($delegate_name)
    {
        $roleCollection = $this->getRoleCollection();

        $roleCollection->getQuery()
            ->eq('/delegate_name', $delegate_name);

        return $roleCollection->count() ? $roleCollection->first() : null;
    }

    public function getRoleCollection()
    {
        $roleCollection = Role::getCollection();

        $roleCollection->getQuery()
            ->innerJoin('User_Role_Link')
            ->eq('User_Role_Link.user__fk', $this->getPk());

        return $roleCollection;
    }

    /**
     * @deprecated используй ::getRole()
     *
     * Проверить, привязана ли роль к пользователю
     *
     * @param string $roleName
     * @return Model|null
     */
    public function hasRole($roleName)
    {
        $roles = $this->linked('Role')
            ->addOptions(
                [
                    'name' => '::Delegate_Name',
                    'delegate' => $roleName
                ]
            );

        return $roles->count();
    }

    public function isAuth()
    {
        return $this->getPk() > self::GUEST_USER_ID;
    }
}