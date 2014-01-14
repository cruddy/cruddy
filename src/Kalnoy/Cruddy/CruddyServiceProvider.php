<?php namespace Kalnoy\Cruddy;

use Illuminate\Support\ServiceProvider;
use Kalnoy\Cruddy\Service\Permissions\SentryPermissions;

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
        $this->app['Kalnoy\Cruddy\Service\Permissions\PermissionsInterface'] = $this->app->share(function ($app) {

            return new SentryPermissions($app['sentry']);
        });
    }

    protected function registerCruddy()
    {
        $this->app['Kalnoy\Cruddy\Environment'] = $this->app->share(function ($app) {

            $config = $app['config'];
            $config->addNamespace('entities', app_path('config/entities'));

            $validator = $app['validator'];
            $translator = $app['translator'];
            $files = $app['files'];
            $permissions = $app['Kalnoy\Cruddy\Service\Permissions\PermissionsInterface'];

            $fields = new Entity\Fields\Factory();
            $columns = new Entity\Columns\Factory();
            $related = new Entity\Related\Factory();

            $factory = new Entity\Factory($app, $files, $translator, $config, $validator, $permissions, $fields,
            $columns,
                $related);

            $menu = new Menu($factory, $permissions);

            return new Environment($config, $factory, $permissions, $menu, $app['request']);
        });
    }
}