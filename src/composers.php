<?php

View::composer('cruddy::layout', function ($view)
{
    $cruddy = app('cruddy');

    if ( ! isset($view->content)) $view->content = '';

    $view->cruddy = $cruddy;
    $view->cruddyJSON = $cruddy->toJSON();

    $view->brand = \Kalnoy\Cruddy\try_trans($cruddy->config('brand'));
    $view->brand_url = $cruddy->config('brand_url') ?: url('/');

    $view->menu = app('cruddy.menu');
    $view->assets = app('cruddy.assets');
    $view->mainMenu = $cruddy->config('menu');
    $view->serviceMenu = $cruddy->config('service_menu');
});