<?php

use Cartalyst\Sentry\Groups\Eloquent\Group as SentryGroup;

class Group extends SentryGroup {

    use Permissions;

    protected $fillable = array(
        'name', 'permissions_string',
    );
}