<?php return [

    'primary_column' => 'title',

    'form' => [
        'model' => 'Category',
        'rules' => array(
            'title' => 'required',
        ),
    ],

    'fields' => [
        'title' => ['required' => true],
        'parent' => ['type' => 'relation', 'reference' => 'categories'],
        'children' => ['type' => 'relation', 'reference' => 'categories'],
    ],

    'columns' => [
        'title',
        'parent',
        'updated_at',
    ],
];