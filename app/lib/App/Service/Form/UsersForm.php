<?php namespace App\Service\Form;

use Cartalyst\Sentry\Sentry;
use Illuminate\Support\MessageBag;

class UsersForm extends AbstractForm {

    /**
     * Sentry class instance.
     *
     * @var \Cartalyst\Sentry\Sentry
     */
    protected $sentry;

    /**
     * Errors bag.
     *
     * @var \Illuminate\Suport\MessageBag
     */
    protected $errors;

    public function __construct(Sentry $sentry)
    {
        $this->sentry = $sentry;
    }

    /**
     * Authenticate a user.
     *
     * @param  array $credentials
     *
     * @return bool
     */
    public function authenticate($credentials)
    {
        $this->errors = new MessageBag;

        try
        {
            $user = $this->sentry->authenticate($credentials);

            return true;
        }
        catch (\Cartalyst\Sentry\Users\LoginRequiredException $e)
        {
            $this->errors->add('sentry', 'Необходимо ввести логин.');
        }
        catch (\Cartalyst\Sentry\Users\PasswordRequiredException $e)
        {
            $this->errors->add('sentry', 'Необходимо ввести пароль.');
        }
        catch (\Cartalyst\Sentry\Users\UserNotFoundException $e)
        {
            $this->errors->add('sentry', 'Неверный пользователь или пароль.');
        }
        catch (\Cartalyst\Sentry\Users\UserNotActivatedException $e)
        {
            $this->errors->add('sentry', 'Пользователь не активирован.');
        }
        catch (\Cartalyst\Sentry\Throttling\UserSuspendedException $e)
        {
            $this->errors->add('sentry', 'Аккаунт пользователя заблокирован.');
        }
        catch (\Cartalyst\Sentry\Throttling\UserBannedException $e)
        {
            $this->errors->add('sentry', 'Аккаунт пользователя заблокирован.');
        }

        return false;
    }

    /**
     * Log out the user.
     *
     * @return void
     */
    public function logout()
    {
        $this->sentry->logout();
    }

    public function errors()
    {
        return $this->errors;
    }

}