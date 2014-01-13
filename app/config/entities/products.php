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

        'type' =>
        [
            'type' => 'enum',
            'items' => ['first' => 'First type', 'second' => 'Second type'],
            'prompt' => 'Please select type',
        ],

        'description' => 'text',
        'image' => ['type' => 'image', 'required' => true],
        'categories' => 'relation',
    ],

    'columns' => [
        'image' => ['formatter' => 'Image'],
        'title',
        'type',
        'categories',
        'updated_at',
    ]
];