<?php namespace Kalnoy\Cruddy;

use Illuminate\Support\ServiceProvider;

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

        include __DIR__."/../../helpers.php";
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
        $this->registerEntityFactory();
        $this->registerMenu();
        $this->registerCruddy();
    }

    public function registerPermissions()
    {
        $this->app['Kalnoy\Cruddy\PermissionsInterface'] = $this->app->share(function ($app) {

            return new SentryPermissions($app['sentry']);
        });
    }

    public function registerEntityFactory()
    {
        $this->app['cruddy.entity.factory'] = $this->app->share(function ($app) {

            $config = $app['config'];
            $validator = $app['validator'];
            $translator = $app['translator'];
            $permissions = $app['Kalnoy\Cruddy\PermissionsInterface'];

            $fields = new Entity\Fields\Factory();
            $columns = new Entity\Columns\Factory();
            $related = new Entity\Related\Factory();

            $config->addNamespace('entities', app_path('config/entities'));

            return new Entity\Factory($app, $translator, $config, $validator, $permissions, $fields, $columns, $related);
        });
    }

    protected function registerMenu()
    {
        $this->app['cruddy.menu'] = $this->app->share(function ($app) {

            $factory = $app['cruddy.entity.factory'];
            $permissions = $app['Kalnoy\Cruddy\PermissionsInterface'];

            return new Menu($factory, $permissions);
        });
    }

    protected function registerCruddy()
    {
        $this->app['Kalnoy\Cruddy\Environment'] = $this->app->share(function ($app) {

            $config = $app['config'];
            $factory = $app['cruddy.entity.factory'];
            $permissions = $app['Kalnoy\Cruddy\PermissionsInterface'];
            $menu = $app['cruddy.menu'];
            $request = $app['request'];

            return new Environment($config, $factory, $permissions, $menu, $request);
        });
    }
}