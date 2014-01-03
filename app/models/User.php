<?php

use Cartalyst\Sentry\Users\Eloquent\User as SentryUser;

class User extends SentryUser {

	use Permissions;

	protected static $groupModel = 'Group';

	protected static $throttleModel = 'Cartalyst\Sentry\Throttling\Eloquent\Throttle';

	protected $fillable = array(
		'first_name', 'last_name',
		'email', 'password', 'permissions_string', 'activated', 'permissions',
	);

	/**
	 * Change password if any value is provided.
	 *
	 * @param string $value
	 */
	public function setChangePasswordAttribute($value)
	{
		if (!empty($value)) $this->password = $value;
	}

	public function throttle()
	{
		return $this->hasOne(static::$throttleModel, 'user_id');
	}

    public function address()
    {
        return $this->morphOne('Address', 'addressable');
    }
}