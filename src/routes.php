<?php

Route::group(['prefix' => Config::get('cruddy::uri')], function ()
{
    Route::group(['prefix' => 'api'], function ()
    {
        Route::get('{entity}',
        [
            'as' => 'cruddy.api.entity.index',
            'uses' => 'Kalnoy\Cruddy\EntityApiController@index',
        ]);

        Route::post('{entity}',
        [
            'as' => 'cruddy.api.entity.create',
            'uses' => 'Kalnoy\Cruddy\EntityApiController@create',
        ]);

        Route::get('{entity}/{id}',
        [
            'as' => 'cruddy.api.entity.show',
            'uses' => 'Kalnoy\Cruddy\EntityApiController@show',
        ]);

        Route::put('{entity}/{id}',
        [
            'as' => 'cruddy.api.entity.update',
            'uses' => 'Kalnoy\Cruddy\EntityApiController@update',
        ]);

        Route::delete('{entity}/{id}',
        [
            'as' => 'cruddy.api.entity.destroy',
            'uses' => 'Kalnoy\Cruddy\EntityApiController@destroy',
        ]);
    });

    Route::get('/', 'Kalnoy\Cruddy\CruddyController@index');
    Route::get('thumb', 'Kalnoy\Cruddy\CruddyController@thumb');

    $entityPattern = implode("|", array_keys(app('cruddy')->getSchemaRepository()->getClasses()));
    $entityPattern = "({$entityPattern})";

    Route::get('{entity}',
    [
        'as' => 'cruddy.index', 
        'uses' => 'Kalnoy\Cruddy\CruddyController@show'
    ])
    ->where('entity', $entityPattern);

    Route::get('{entity}/create',
    [
        'as' => 'cruddy.create',
        'uses' => 'Kalnoy\Cruddy\CruddyController@show',
    ])
    ->where('entity', $entityPattern);

    Route::get('{entity}/{id}',
    [
        'as' => 'cruddy.show', 
        'uses' => 'Kalnoy\Cruddy\CruddyController@show'
    ])
    ->where('entity', $entityPattern);
});