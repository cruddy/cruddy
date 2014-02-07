<?php

View::creator('cruddy::layout', function ($view)
{
    $cruddy = app('cruddy');

    $view->brand = \Kalnoy\Cruddy\try_trans($cruddy->config('brand'));
    $view->cruddy = $cruddy;
    $view->content = '';
});