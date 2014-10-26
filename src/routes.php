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

$router->get('_thumb', [
    'as' => 'cruddy.thumb',
    'uses' => 'CruddyController@thumb',
]);

$router->get('{cruddy_entity}', [
    'as' => 'cruddy.index',
    'uses' => 'EntityController@index',
]);

$router->get('{cruddy_entity}/{id}', [
    'as' => 'cruddy.show',
    'uses' => 'EntityController@show',
]);

$router->post('{cruddy_entity}', [
    'as' => 'cruddy.store',
    'uses' => 'EntityController@store',
]);

$router->put('{cruddy_entity}/{id}', [
    'as' => 'cruddy.update',
    'uses' => 'EntityController@update',
]);

$router->delete('{cruddy_entity}/{id}', [
    'as' => 'cruddy.destroy',
    'uses' => 'EntityController@destroy',
]);