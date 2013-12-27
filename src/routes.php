<?php

Route::group(array('prefix' => Config::get("cruddy::uri"), 'before' => 'auth.backend'), function () {

    $index = "Kalnoy\\Cruddy\\CruddyController@index";

    Route::get("/", $index);
    Route::get("{model}", array("as" => "cruddy.index", "uses" => $index));
    Route::get("{model}/{id}", array("as" => "cruddy.show", "uses" => $index));

    Route::group(array('prefix' => 'api/v1'), function () {

        // Get a model definition.
        Route::get('{model}/entity', array(
            'as' => 'cruddy.api.entity',
            'uses' => 'Kalnoy\Cruddy\CruddyApiController@entity',
        ));

        // Index models.
        Route::get('{model}', array(
            'as' => 'cruddy.api.index',
            'uses' => 'Kalnoy\Cruddy\CruddyApiController@index',
        ));

        // Create a model instance.
        Route::post('{model}', array(
            'as' => 'cruddy.api.create',
            'uses' => 'Kalnoy\Cruddy\CruddyApiController@create',
        ));

        // View a model instance.
        Route::get('{model}/{id}', array(
            'as' => 'cruddy.api.show',
            'uses' => 'Kalnoy\Cruddy\CruddyApiController@show',
        ))
        ->where('id', '[0-9]+');

        // Update a model instance.
        Route::put('{model}/{id}', array(
            'as' => 'cruddy.api.update',
            'uses' => 'Kalnoy\Cruddy\CruddyApiController@update',
        ))
        ->where('id', '[0-9]+');

        // Destroy a model instance.
        Route::delete('{model}/{id}', array(
            'as' => 'cruddy.api.destroy',
            'uses' => 'Kalnoy\Cruddy\CruddyApiController@destroy',
        ))
        ->where('id', '[0-9]+');
    });
});