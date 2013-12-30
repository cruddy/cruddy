<?php

Route::group(array('prefix' => Config::get('cruddy::uri'), 'before' => 'auth.backend'), function () {

    $index = 'Kalnoy\Cruddy\CruddyController@index';

    Route::get('/', $index);
    Route::get('{model}', array('as' => 'cruddy.index', 'uses' => $index));
    Route::get('{model}/{id}', array('as' => 'cruddy.show', 'uses' => $index));

    Route::group(array('prefix' => 'api/v1'), function () {

        Route::group(array('prefix' => 'entity'), function () {

            Route::get('{model}/schema', array(
                'as' => 'cruddy.api.entity.schema',
                'uses' => 'Kalnoy\Cruddy\EntityApiController@schema',
            ));

            Route::get('{model}', array(
                'as' => 'cruddy.api.entity.index',
                'uses' => 'Kalnoy\Cruddy\EntityApiController@index',
            ));

            Route::post('{model}', array(
                'as' => 'cruddy.api.entity.create',
                'uses' => 'Kalnoy\Cruddy\EntityApiController@create',
            ));

            Route::get('{model}/{id}', array(
                'as' => 'cruddy.api.entity.show',
                'uses' => 'Kalnoy\Cruddy\EntityApiController@show',
            ))
            ->where('id', '[0-9]+');

            Route::put('{model}/{id}', array(
                'as' => 'cruddy.api.entity.update',
                'uses' => 'Kalnoy\Cruddy\EntityApiController@update',
            ))
            ->where('id', '[0-9]+');

            Route::delete('{model}/{id}', array(
                'as' => 'cruddy.api.entity.destroy',
                'uses' => 'Kalnoy\Cruddy\EntityApiController@destroy',
            ))
            ->where('id', '[0-9]+');
        });
    });
});