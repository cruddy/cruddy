<?php

Route::filter('auth.backend', function () {

    if (!app('Kalnoy\Cruddy\PermissionsInterface')->hasAccess())
    {
        if (Request::ajax()) App::abort(403);

        return Redirect::guest('login');
    }
});