<?php

class HomeController extends BaseController {

    protected $layout = 'layouts/master';

    public function home()
    {
        $this->layout->content = View::make('hello');
    }
}