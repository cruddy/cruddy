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
        $this->registerFieldFactory();
        $this->registerColumnFactory();
        $this->registerRelatedFactory();
        $this->registerEntityFactory();
        $this->registerMenu();
        $this->registerCruddy();
    }

    public function registerPermissions()
    {
        $this->app['cruddy.permissions'] = $this->app->share(function ($app) {

            return new SentryPermissions($app['sentry']);
        });
    }

    public function registerFieldFactory()
    {
        $this->app->bind('cruddy.factory.field', 'Kalnoy\Cruddy\Fields\Factory', true);
    }

    public function registerColumnFactory()
    {
        $this->app->bind('cruddy.factory.column', 'Kalnoy\Cruddy\Columns\Factory', true);
    }

    public function registerRelatedFactory()
    {
        $this->app->bind('cruddy.factory.related', 'Kalnoy\Cruddy\Related\Factory', true);
    }

    public function registerEntityFactory()
    {
        $this->app['cruddy.factory'] = $this->app->share(function ($app) {

            $config = $app['config'];
            $fields = $app['cruddy.factory.field'];
            $columns = $app['cruddy.factory.column'];
            $related = $app['cruddy.factory.related'];
            $validator = $app['validator'];
            $translator = $app['translator'];

            $config->addNamespace('entities', app_path('config/entities'));

            return new Factory($app, $translator, $config, $validator, $fields, $columns, $related);
        });
    }

    protected function registerMenu()
    {
        $this->app["cruddy.menu"] = $this->app->share(function ($app) {

            return new Menu($app["cruddy.factory"], $app["cruddy.permissions"]);
        });
    }

    protected function registerCruddy()
    {
        $this->app['Kalnoy\Cruddy\Environment'] = $this->app->share(function ($app) {

            $config = $app['config'];
            $factory = $app['cruddy.factory'];
            $permissions = $app['cruddy.permissions'];
            $menu = $app["cruddy.menu"];
            $request = $app["request"];

            return new Environment($config, $factory, $permissions, $menu, $request);
        });
    }

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}