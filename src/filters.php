<?php

Route::filter('auth.backend', function () {

    if (!app("cruddy.permissions")->hasAccess())
    {
        if (Request::ajax()) App::abort(403);

        return Redirect::guest('login');
    }
});