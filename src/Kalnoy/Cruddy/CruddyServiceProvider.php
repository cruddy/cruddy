<?php

namespace Kalnoy\Cruddy;

use Illuminate\Support\ServiceProvider;
use Kalnoy\Cruddy\Service\MenuBuilder;
use Kalnoy\Cruddy\Service\Permissions\PermissionsManager;
use Kalnoy\Cruddy\Console\GenerateSchemaCommand;

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

        include __DIR__."/../../routes.php";
        include __DIR__."/../../composers.php";
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
    {
        $this->registerMenu();
        $this->registerPermissions();
        $this->registerCruddy();
        $this->registerCommands();
    }

    /**
     * Register menu builder.
     *
     * @return void
     */
    public function registerMenu()
    {
        $this->app->bindShared('cruddy.menu', function ($app)
        {
            return new MenuBuilder($app['cruddy'], $app['html'], $app['url']);
        });
    }

    /**
     * Register permissions service.
     *
     * @return void
     */
    public function registerPermissions()
    {
        $this->app['cruddy.permissions'] = $this->app->share(function ($app)
        {
            return new PermissionsManager($app);
        });
    }

    /**
     * Register cruddy environment.
     *
     * @return void
     */
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

            $env = new Environment($config, $app['request'], $translator, $repository, $fields, $columns, $permissions, $app['events']);

            Entity::setEnvironment($env);

            return $this->registerAssets($env);
        });
    }

    /**
     * Register assets.
     *
     * @param \Kalnoy\Cruddy\Environment $env
     *
     * @return \Kalnoy\Cruddy\Environment
     */
    protected function registerAssets(Environment $env)
    {
        $baseDir = $this->app['config']->get('cruddy::assets', 'packages/kalnoy/cruddy');

        return $env->css($this->getCssFiles($baseDir))->js($this->getJsFiles($baseDir));
    }

    /**
     * Resolve asset paths.
     *
     * @param string $baseDir
     * @param array  $items
     *
     * @return array
     */
    protected function assets($baseDir, array $items)
    {
        $url = $this->app['url'];

        return array_map(function ($item) use ($url, $baseDir)
        {
            return $url->asset("{$baseDir}/{$item}");

        }, $items);
    }

    /**
     * Get the list of css files.
     *
     * @param string $baseDir
     *
     * @return array
     */
    protected function getCssFiles($baseDir)
    {
        return $this->assets($baseDir.'/css',
        [
            'styles.min.css',
        ]);
    }

    /**
     * Get the list of js files.
     *
     * @param string $baseDir
     *
     * @return array
     */
    protected function getJsFiles($baseDir)
    {
        $suffix = $this->app['config']->get('app.debug') ? '' : '.min';

        return $this->assets($baseDir.'/js', 
        [
            'ace/ace.js', 
            "vendor{$suffix}.js", 
            "app{$suffix}.js",
        ]);
    }

    /**
     * Register console commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        $this->app->bindShared('cruddy.command.schema', function ($app)
        {
            return new GenerateSchemaCommand($app['files']);
        });

        $this->commands('cruddy.command.schema');
    }
}