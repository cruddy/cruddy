<?php

/**
 * @var \Illuminate\Routing\Router $router
 */

$namespace = 'Kalnoy\Cruddy\Controllers\\';

$router->get('/', [
    'as' => 'cruddy.home',
    'uses' => $namespace.'CruddyController@index',
]);

$router->get('_schema', [
    'as' => 'cruddy.schema',
    'uses' => $namespace.'CruddyController@schema',
]);

$router->get('_thumb', [
    'as' => 'cruddy.thumb',
    'uses' => $namespace.'CruddyController@thumb',
]);

$router->get('{entity}', [
    'as' => 'cruddy.index',
    'uses' => $namespace.'EntityController@index',
]);

$router->get('{entity}/{id}', [
    'as' => 'cruddy.show',
    'uses' => $namespace.'EntityController@show',
]);

$router->post('{entity}', [
    'as' => 'cruddy.store',
    'uses' => $namespace.'EntityController@store',
]);

$router->put('{entity}/{id}', [
    'as' => 'cruddy.update',
    'uses' => $namespace.'EntityController@update',
]);

$router->delete('{entity}/{id}', [
    'as' => 'cruddy.destroy',
    'uses' => $namespace.'EntityController@destroy',
]);