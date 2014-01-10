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
        'slug' => ['type' => 'slug', 'required' => true, 'ref' => 'title', 'separator' => '_'],
        'images' => ['type' => 'image', 'multiple' => true],
        'parent' => ['type' => 'relation', 'reference' => 'categories'],
        'children' => ['type' => 'relation', 'reference' => 'categories'],
    ],

    'columns' => [
        'images' => ['formatter' => 'Image', 'formatterOptions' => ['width' => 60]],
        'title',
        'parent',
        'updated_at',
    ],
];