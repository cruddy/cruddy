<?php return array(

    // The title of the application. It can be a translation key.
    'brand' => 'app.title',

    // The URI that is prefixed to all routes of Cruddy.
    'uri' => 'backend',

    // The main layout view.
    'layout' => 'cruddy::layout',

    // The default ace theme.
    'ace_theme' => 'chrome',

    // The path to the assets.
    'assets' => 'packages/kalnoy/cruddy',

    // The menu entries.
    'menu' => [
        'Auth' => ['@users', '@groups'],
    ],
);