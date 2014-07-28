<?php

Route::group(['prefix' => Config::get('cruddy::uri')], function ()
{
    Route::group(['prefix' => 'api'], function ()
    {
        Route::get('_schema',
        [
            'as' => 'cruddy.api.schema',
            'uses' => 'Kalnoy\Cruddy\Controllers\EntityApiController@schema',
        ]);

        Route::get('{entity}',
        [
            'as' => 'cruddy.api.entity.index',
            'uses' => 'Kalnoy\Cruddy\Controllers\EntityApiController@index',
        ]);

        Route::post('{entity}',
        [
            'as' => 'cruddy.api.entity.create',
            'uses' => 'Kalnoy\Cruddy\Controllers\EntityApiController@create',
        ]);

        Route::get('{entity}/{id}',
        [
            'as' => 'cruddy.api.entity.show',
            'uses' => 'Kalnoy\Cruddy\Controllers\EntityApiController@show',
        ]);

        Route::put('{entity}/{id}',
        [
            'as' => 'cruddy.api.entity.update',
            'uses' => 'Kalnoy\Cruddy\Controllers\EntityApiController@update',
        ]);

        Route::delete('{entity}/{id}',
        [
            'as' => 'cruddy.api.entity.destroy',
            'uses' => 'Kalnoy\Cruddy\Controllers\EntityApiController@destroy',
        ]);
    });

    Route::get('/', 'Kalnoy\Cruddy\Controllers\CruddyController@index');
    Route::get('thumb', 'Kalnoy\Cruddy\Controllers\CruddyController@thumb');

    $entityPattern = app('cruddy.repository')->available();
    $entityPattern = "({$entityPattern})";

    Route::get('{entity}',
    [
        'as' => 'cruddy.index', 
        'uses' => 'Kalnoy\Cruddy\Controllers\CruddyController@show'
    ])
    ->where('entity', $entityPattern);
});