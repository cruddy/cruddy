<?php return array(

    'primary_column' => 'name',

    'form' => array(
        'model' => 'Group',
    ),

    'columns' => array(
        'id',
        'name',
        'updated_at' => array('order_dir' => 'desc'),
    ),

    'fields' => array(

        'name',

        'permissions_string' => array(
            'type' => 'text',
            'help' => 'Список разрешений группы, по одному на строку.',
            'rows' => 10,
        ),

        'users' => 'relation',
    ),

);