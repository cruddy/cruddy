<?php return [

    'primary_column' => 'title',

    'form' => [
        'model' => 'Category',

        'rules' => array(
            'title' => 'required',
        ),

        'files' => [
            'images' => ['path' => 'images/categories', 'multiple' => true, 'keepNames' => true],
        ],
    ],

    'fields' => [
        'title' => ['required' => true],
        'images' => ['type' => 'image', 'multiple' => true],
        'parent' => ['type' => 'relation', 'reference' => 'categories'],
        'children' => ['type' => 'relation', 'reference' => 'categories'],
    ],

    'columns' => [
        'title',
        'parent',
        'updated_at',
    ],
];