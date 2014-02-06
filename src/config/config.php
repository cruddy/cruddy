<?php return array(

    // The title of the application. It can be a translation key.
    'brand' => 'app.title',

    // The URI that is prefixed to all routes of Cruddy.
    'uri' => 'backend',

    // The permissions type that will be used.
    'permissions' => 'sentry',

    // The main layout view.
    'layout' => 'cruddy::layout',

    // The default ace theme.
    'ace_theme' => 'chrome',

    // The path to the assets.
    'assets' => 'packages/kalnoy/cruddy',

    // The list of key value pairs where key is the entity id and value is
    // an entity class name. For example:
    // 
    // 'users' => 'UserEntity'
    // 
    // Class is resolved through the IoC container.
    'entities' =>
    [

    ],

    // The menu entries. There is three types of entry: entity, dropdown and custom
    // link.
    // 
    // To specify a link to the entity, just insert it's id.
    // 
    // To make a dropdown menu entry, provide a key-value pair where key will be
    // the label and value is array of menu entries.
    // 
    // To make a custom link you need to provide an array with an url and label.
    // 
    // Example:
    // 
    // [
    //      'auth' => ['users', 'groups'],
    //      
    //      ['url' => link_to_route('my.link'), 'label' => 'My link', 'permissions' => 'permissions_id'],
    //      
    //      'Another link' => 'http://mysite.com',
    // ]
    'menu' =>
    [
        
    ],
);