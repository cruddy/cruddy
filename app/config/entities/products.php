<?php return [
    'primary_column' => 'title',

    'form' => [
        'model' => 'Product',
        'rules' => [
            'title' => 'required',
        ],
    ],

    'fields' => [
        'title',
        'description' => 'text',
        'categories' => 'relation',
    ],

    'columns' => [
//        'id',
        'title',
        'categories',
        'updated_at',
    ]
];