<?php

Route::group(['prefix' => Config::get('cruddy::uri')], function () {

    Route::group(['prefix' => 'api'], function () {

        Route::get('{model}', [
            'as' => 'cruddy.api.entity.index',
            'uses' => 'Kalnoy\Cruddy\EntityApiController@index',
        ]);

        Route::get('{model}/search', [
            'as' => 'cruddy.api.entity.search',
            'uses' => 'Kalnoy\Cruddy\EntityApiController@search',
        ]);

        Route::post('{model}', [
            'as' => 'cruddy.api.entity.create',
            'uses' => 'Kalnoy\Cruddy\EntityApiController@create',
        ]);

        Route::get('{model}/{id}', [
            'as' => 'cruddy.api.entity.show',
            'uses' => 'Kalnoy\Cruddy\EntityApiController@show',
        ])
        ->where('id', '[0-9]+');

        Route::put('{model}/{id}', [
            'as' => 'cruddy.api.entity.update',
            'uses' => 'Kalnoy\Cruddy\EntityApiController@update',
        ])
        ->where('id', '[0-9]+');

        Route::delete('{model}/{id}', [
            'as' => 'cruddy.api.entity.destroy',
            'uses' => 'Kalnoy\Cruddy\EntityApiController@destroy',
        ])
        ->where('id', '[0-9]+');
    });

    $index = 'Kalnoy\Cruddy\CruddyController@index';

    Route::get('/', $index);
    Route::get('thumb', 'Kalnoy\Cruddy\CruddyController@thumb');

    Route::get('{model}', ['as' => 'cruddy.index', 'uses' => $index]);
    Route::get('{model}/{id}', ['as' => 'cruddy.show', 'uses' => $index]);
});