<?php namespace App\Service;

class FormServiceProvider extends \Illuminate\Support\ServiceProvider {

    public function register()
    {
        $ns = __NAMESPACE__.'\Form\\';

        $this->app[$ns.'UsersForm'] = $this->app->share(function ($app) {
            return new Form\UsersForm($app['sentry']);
        });
    }

}