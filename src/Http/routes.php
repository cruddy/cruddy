<?php

use Illuminate\Routing\Router;

/**
 * @var Router $router
 */

$router->get('/', 'CruddyController@index')->name('cruddy.home');
$router->get('_schema', 'CruddyController@schema')->name('cruddy.schema');

// File routes
$router->group([ 'prefix' => 'storage' ], function (Router $router) {
    $router->pattern('storage_path', '[a-zA-Z0-9\-_/]+');
    $router->pattern('storage_file', '[a-zA-Z0-9\-_]+\.[a-zA-Z0-9\-_]+');

    $router->get('{storage_path}', 'FilesController@index');
    $router->get('{storage_path}/{storage_file}', 'FilesController@show')->name('cruddy.files.show');
    $router->post('{storage_path}', [ 'uses' => 'FilesController@store' ]);
});

// Entity routes
$router->group([ 'prefix' => 'entities/{cruddy_entity}' ], function (Router $router) {
    $router->get('/', 'EntityController@index')->name('cruddy.index');
    $router->get('{id}', 'EntityController@show')->name('cruddy.show');
    $router->post('{action?}', 'EntityController@store');
    $router->post('{id}/{action}', 'EntityController@executeCustomAction');
    $router->put('{id}/{action?}', 'EntityController@update');
    $router->delete('{id}', 'EntityController@destroy');
});