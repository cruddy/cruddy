<?php

Route::get('/', function () {

    return Redirect::to('backend');
});

Route::get('login', 'UsersController@login');
Route::get('logout', 'UsersController@logout');
Route::post('login', 'UsersController@authenticate');