<?php

namespace Kalnoy\Cruddy;

use Illuminate\Support\ServiceProvider;
use Kalnoy\Cruddy\Service\Permissions\PermissionsManager;

class CruddyServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('kalnoy/cruddy');

        include __DIR__."/../../filters.php";
        include __DIR__."/../../routes.php";
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
    {
        $this->registerPermissions();
        $this->registerCruddy();
    }

    public function registerPermissions()
    {
        $this->app['cruddy.permissions'] = $this->app->share(function ($app)
        {
            return new PermissionsManager($app);
        });
    }

    protected function registerCruddy()
    {
        $this->app['cruddy'] = $this->app->share(function ($app)
        {
            $config = $app['config'];
            $validator = $app['validator'];
            $translator = $app['translator'];
            $files = $app['files'];
            $permissions = $app['cruddy.permissions'];

            $fields = new Schema\Fields\Factory;
            $columns = new Schema\Columns\Factory;

            $repository = new Schema\Repository($app, $config->get('cruddy::entities', []));

            return new Environment($config, $app['request'], $translator, $repository, $fields, $columns, $permissions);
        });
    }
}