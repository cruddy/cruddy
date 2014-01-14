<?php

Route::filter('cruddy.auth', function ()
{
    if (!app('Kalnoy\Cruddy\Service\Permissions\PermissionsInterface')->hasAccess())
    {
        if (Request::ajax()) App::abort(403);

        return Redirect::guest('login');
    }
});