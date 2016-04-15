<?php

/**
 * @var \Illuminate\Routing\Router $router
 */

$router->get('/', [
    'as' => 'cruddy.home',
    'uses' => 'CruddyController@index',
]);

$router->get('_schema', [
    'as' => 'cruddy.schema',
    'uses' => 'CruddyController@schema',
]);

$router->pattern('storage_path', '[a-zA-Z0-9\-_/]+');
$router->pattern('storage_file', '[a-zA-Z0-9\-_]+\.[a-zA-Z0-9\-_]+');

$router->get('_files/{storage_path}', [ 'uses' => 'FilesController@index' ]);
$router->get('_files/{storage_path}/{storage_file}', [ 'uses' => 'FilesController@show' ]);
$router->post('_files/{storage_path}', [ 'uses' => 'FilesController@store' ]);

$router->get('{cruddy_entity}', [
    'as' => 'cruddy.index',
    'uses' => 'EntityController@index',
]);

$router->get('{cruddy_entity}/{id}', [
    'as' => 'cruddy.show',
    'uses' => 'EntityController@show',
]);

$router->post('{cruddy_entity}/{action?}', [
    'as' => 'cruddy.store',
    'uses' => 'EntityController@store',
]);

$router->post('{cruddy_entity}/{id}/{action}', [
    'as' => 'cruddy.action',
    'uses' => 'EntityController@executeCustomAction',
]);

$router->put('{cruddy_entity}/{id}/{action?}', [
    'as' => 'cruddy.update',
    'uses' => 'EntityController@update',
]);

$router->delete('{cruddy_entity}/{id}', [
    'as' => 'cruddy.destroy',
    'uses' => 'EntityController@destroy',
]);