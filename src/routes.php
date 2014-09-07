<?php

Route::group(['prefix' => Config::get('cruddy::uri'), 'namespace' => 'Kalnoy\Cruddy\Controllers'], function ()
{
    Route::group(['prefix' => 'api'], function ()
    {
        Route::get('_schema',
        [
            'as' => 'cruddy.api.schema',
            'uses' => 'EntityApiController@schema',
        ]);

        Route::get('{entity}',
        [
            'as' => 'cruddy.api.entity.index',
            'uses' => 'EntityApiController@index',
        ]);

        Route::post('{entity}',
        [
            'as' => 'cruddy.api.entity.create',
            'uses' => 'EntityApiController@create',
        ]);

        Route::get('{entity}/{id}',
        [
            'as' => 'cruddy.api.entity.show',
            'uses' => 'EntityApiController@show',
        ]);

        Route::put('{entity}/{id}',
        [
            'as' => 'cruddy.api.entity.update',
            'uses' => 'EntityApiController@update',
        ]);

        Route::delete('{entity}/{id}',
        [
            'as' => 'cruddy.api.entity.destroy',
            'uses' => 'EntityApiController@destroy',
        ]);
    });

    Route::get('/', 'CruddyController@index');
    Route::get('thumb', 'CruddyController@thumb');

    $entityPattern = app('cruddy.repository')->available();
    $entityPattern = "({$entityPattern})";

    Route::get('{entity}',
    [
        'as' => 'cruddy.index',
        'uses' => 'CruddyController@show'
    ])
    ->where('entity', $entityPattern);
});