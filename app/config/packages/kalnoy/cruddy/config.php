<?php return array(

    'brand' => 'App',

    'uri' => 'backend',

    'layout' => 'cruddy::layout',

    'assets' => 'public',

    'menu' => array(
        'Store' => array(
            '@products',
            '@categories',
        ),

        'backend.auth' => array(
            '@users',
            '@groups',
        ),
    ),
);