<?php

/**
 * @var \Illuminate\Contracts\Routing\Registrar $router
 */

$router->get('/', [
    'as' => 'cruddy.home',
    'uses' => 'CruddyController@index',
]);

$router->get('_schema', [
    'as' => 'cruddy.schema',
    'uses' => 'CruddyController@schema',
]);

$router->get('_thumb', [
    'as' => 'cruddy.thumb',
    'uses' => 'CruddyController@thumb',
]);

$router->get('{entity}', [
    'as' => 'cruddy.index',
    'uses' => 'EntityController@index',
]);

$router->get('{entity}/{id}', [
    'as' => 'cruddy.show',
    'uses' => 'EntityController@show',
]);

$router->post('{entity}', [
    'as' => 'cruddy.store',
    'uses' => 'EntityController@store',
]);

$router->put('{entity}/{id}', [
    'as' => 'cruddy.update',
    'uses' => 'EntityController@update',
]);

$router->delete('{entity}/{id}', [
    'as' => 'cruddy.destroy',
    'uses' => 'EntityController@destroy',
]);