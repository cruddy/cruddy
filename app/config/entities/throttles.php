<?php return array(

    'form' => array(
        'model' => 'Throttle',
    ),

    'fields' => array(
        'suspended' => 'bool',
        'banned' => 'bool',
        'ip_address',
        'suspended_at' => 'datetime',
        'banned_at' => 'datetime',
    ),

);