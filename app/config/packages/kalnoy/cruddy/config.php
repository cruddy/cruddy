<?php return array(

    'brand' => 'App',

    'uri' => 'backend',

    'layout' => 'cruddy::layout',

    'assets' => 'public',

    'menu' => array(
        'backend.auth' => array(
            '@users',
            '@groups',
        ),
    ),
);