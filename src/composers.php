<?php

View::creator('cruddy::layout', function ($view)
{
    $cruddy = app('cruddy');

    $view->content = '';
    
    $view->cruddy = $cruddy;
    $view->cruddyJSON = $cruddy->toJSON();

    $view->brand = \Kalnoy\Cruddy\try_trans($cruddy->config('brand'));
    $view->brand_url = $cruddy->config('brand_url') ?: url('/');    

    $view->menu = app('cruddy.menu');
    $view->mainMenu = $cruddy->config('menu');
    $view->serviceMenu = $cruddy->config('service_menu');
});