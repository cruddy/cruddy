<?php

Route::filter('cruddy.auth', function ()
{
    if ( ! app('cruddy')->getPermissions()->hasAccess())
    {
        if (Request::ajax()) App::abort(403);

        return Redirect::guest('login');
    }
});