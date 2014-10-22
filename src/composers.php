<?php

View::composer('cruddy::layout', function ($view)
{
    $cruddy = app('cruddy');
    $url = app('url');
    $request = app('request');

    $view->cruddy = $cruddy;
    $view->cruddyData = $cruddy->data();

    $view->cruddyData += [
        'schemaUrl' => $url->route('cruddy.schema'),
        'thumbUrl' => $url->route('cruddy.thumb'),
        'baseUrl' => $url->route('cruddy.home'),
        'root' => $request->root(),
    ];

    $view->brand = \Kalnoy\Cruddy\try_trans($cruddy->config('brand'));
    $view->brand_url = $cruddy->config('brand_url') ?: url('/');

    $view->menu = app('cruddy.menu');
    $view->assets = app('cruddy.assets');
    $view->mainMenu = $cruddy->config('menu');
    $view->serviceMenu = $cruddy->config('service_menu');
});