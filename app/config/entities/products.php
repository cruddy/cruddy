<?php return [
    'primary_column' => 'title',

    'form' => [
        'model' => 'Product',

        'rules' => [
            'title' => 'required',
            'image' => 'required|image',
        ],

        'files' => [
            'image' => ['path' => 'images/products'],
        ],
    ],

    'fields' => [
        'title' => ['required' => true],
        'description' => 'text',
        'image' => ['type' => 'image', 'required' => true],
        'categories' => 'relation',
    ],

    'columns' => [
//        'id',
        'title',
        'categories',
        'updated_at',
    ]
];