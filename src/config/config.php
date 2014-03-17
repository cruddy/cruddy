<?php return array(

    // The title of the application. It can be a translation key.
    'brand' => 'My application',

    // The link to the main page
    'brand_url' => url('/'),

    // The name of the view that is used to render the dashboard.
    // You can specify an entity id prefixing it with `@` like so: `@users`.
    'dashboard' => 'cruddy::dashboard',

    // The URI that is prefixed to all routes of Cruddy.
    'uri' => 'backend',

    // The permissions driver.
    // Two types available: `stub` and `sentry`.
    'permissions' => 'stub',

    // The name of the filter that will be used for authentication.
    // I.e. `auth.basic` or `auth`.
    'auth_filter' => null,

    // The url that is used to log out a user
    'logout_url' => null,

    // The main layout view.
    'layout' => 'cruddy::layout',

    // The default ace theme.
    'ace_theme' => 'chrome',

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