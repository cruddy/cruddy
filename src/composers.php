<?php

View::creator('cruddy::layout', function ($view)
{
    $cruddy = app('cruddy');

    $view->brand = \Kalnoy\Cruddy\try_trans($cruddy->config('brand'));
    $view->brand_url = $cruddy->config('brand_url') ?: url('/');
    $view->logout_url = $cruddy->config('logout_url');
    $view->cruddy = $cruddy;
    $view->content = '';
});