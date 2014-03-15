<?php

Route::group(['prefix' => Config::get('cruddy::uri')], function ()
{
    Route::group(['prefix' => 'api'], function ()
    {
        Route::get('{model}',
        [
            'as' => 'cruddy.api.entity.index',
            'uses' => 'Kalnoy\Cruddy\EntityApiController@index',
        ]);

        Route::post('{model}',
        [
            'as' => 'cruddy.api.entity.create',
            'uses' => 'Kalnoy\Cruddy\EntityApiController@create',
        ]);

        Route::get('{model}/{id}',
        [
            'as' => 'cruddy.api.entity.show',
            'uses' => 'Kalnoy\Cruddy\EntityApiController@show',
        ]);

        Route::put('{model}/{id}',
        [
            'as' => 'cruddy.api.entity.update',
            'uses' => 'Kalnoy\Cruddy\EntityApiController@update',
        ]);

        Route::delete('{model}/{id}',
        [
            'as' => 'cruddy.api.entity.destroy',
            'uses' => 'Kalnoy\Cruddy\EntityApiController@destroy',
        ]);
    });

    Route::get('/', 'Kalnoy\Cruddy\CruddyController@index');
    Route::get('thumb', 'Kalnoy\Cruddy\CruddyController@thumb');

    Route::get('{model}',
    [
        'as' => 'cruddy.index', 
        'uses' => 'Kalnoy\Cruddy\CruddyController@show'
    ])
    ->where('model', '\w+');

    Route::get('{model}/create',
    [
        'as' => 'cruddy.create',
        'uses' => 'Kalnoy\Cruddy\CruddyController@show',
    ])
    ->where('model', '\w+');

    Route::get('{model}/{id}',
    [
        'as' => 'cruddy.show', 
        'uses' => 'Kalnoy\Cruddy\CruddyController@show'
    ])
    ->where('model', '\w+');
});