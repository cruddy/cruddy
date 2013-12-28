<?php

use Cartalyst\Sentry\Throttling\Eloquent\Throttle as SentryThrottle;

class Throttle extends SentryThrottle {

    protected $fillable = array('user_id', 'banned', 'suspended');

    public function setSuspendedAttribute($value)
    {
        $value = (bool)$value;

        if ($value == $this->suspended) return;

        $this->attributes['suspended'] = $value;
        $this->attributes['suspended_at'] = $value ? $this->freshTimeStamp() : null;
    }

    public function setBannedAttribute($value)
    {
        $value = (bool)$value;

        if ($value == $this->banned) return;

        $this->attributes['banned'] = $value;
        $this->attributes['banned_at'] = $value ? $this->freshTimeStamp() : null;
    }
}