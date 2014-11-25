<?php

namespace Kalnoy\Cruddy;

use Illuminate\Container\Container;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Illuminate\Config\Repository as Config;
use Illuminate\View\Factory;
use Kalnoy\Cruddy\Service\MenuBuilder;
use Kalnoy\Cruddy\Repo\BaseRepository;
use Kalnoy\Cruddy\Service\Permissions\PermissionsManager;
use Kalnoy\Cruddy\Console\GenerateSchemaCommand;
use Kalnoy\Cruddy\Console\CompileCommand;
use Kalnoy\Cruddy\Console\ClearCompiledCommand;
use Kalnoy\Cruddy\Service\ThumbnailFactory;
use Intervention\Image\ImageManager;

class CruddyServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

    /**
     * Assets build number.
     *
     * @var int
     */
<<<<<<< HEAD
    protected $build = 23;
=======
    protected $build = 21;
>>>>>>> master

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('kalnoy/cruddy');

        $this->registerRoutes($this->app['router'], $this->app['config']);
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
    {
        $this->registerAssets();
        $this->registerLang();
        $this->registerMenu();
        $this->registerPermissions();
        $this->registerFactories();
        $this->registerRepository();
        $this->registerCruddy();
        $this->registerCommands();
        $this->registerCompiler();
        $this->registerThumbnailFactory();
        $this->registerAliases();
        $this->registerViewComposer();
    }

    /**
     * Register cruddy lang object.
     */
    protected function registerLang()
    {
        $this->app->bindShared('cruddy.lang', function ($app)
        {
            return new Lang($app['translator']);
        });
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
            return new MenuBuilder($app['cruddy'], $app['cruddy.lang'], $app['html'], $app['url']);
        });
    }

    /**
     * Register permissions service.
     *
     * @return void
     */
    public function registerPermissions()
    {
        $this->app->bindShared('cruddy.permissions', function ($app)
        {
            return new PermissionsManager($app);
        });
    }

    /**
     * Register fields factory.
     */
    protected function registerFactories()
    {
        $this->app->bindShared('cruddy.fields', function ()
        {
            return new Schema\Fields\Factory;
        });

        $this->app->bindShared('cruddy.columns', function ()
        {
            return new Schema\Columns\Factory;
        });

        $this->app->bindShared('cruddy.filters', function ()
        {
            return new Schema\Filters\Factory;
        });
    }

    /**
     * Register entity repository.
     */
    public function registerRepository()
    {
        $this->app->bindShared('cruddy.repository', function (Container $app)
        {
            $config = $app->make('config');

            return new Repository($app, $config->get('cruddy::entities', []));
        });
    }

    /**
     * Register cruddy environment.
     *
     * @return void
     */
    protected function registerCruddy()
    {
        $this->app->bindShared('cruddy', function ($app)
        {
            $config = $app['config'];

            $permissions = $app['cruddy.permissions'];
            $lang = $app['cruddy.lang'];
            $repository = $app['cruddy.repository'];

            $env = new Environment($config, $repository, $permissions, $lang, $app['events']);

            Entity::setEnvironment($env);

            return $env;
        });
    }

    /**
     * Register assets.
     */
    protected function registerAssets()
    {
        $this->app->bindShared('cruddy.assets', function ($app)
        {
            $baseDir = $app['config']->get('cruddy::assets', 'packages/kalnoy/cruddy');

            $assets = new Assets;

            $assets->css($this->getCssFiles($baseDir));
            $assets->js($this->getJsFiles($baseDir));

            return $assets;
        });

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
            return $url->asset("{$baseDir}/{$item}").'?v='.$this->build;

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

        $this->app->bindShared('cruddy.command.compile', function ($app)
        {
            $app['cruddy'];

            return new CompileCommand($app['cruddy.compiler']);
        });

        $this->app->bindShared('cruddy.command.clearCompiled', function ($app)
        {
            return new ClearCompiledCommand($app['cruddy.compiler']);
        });

        $this->commands(
            'cruddy.command.schema',
            'cruddy.command.compile',
            'cruddy.command.clearCompiled'
        );
    }

    /**
     * Register schema compiler.
     */
    protected function registerCompiler()
    {
        $this->app->bindShared('cruddy.compiler', function ($app)
        {
            $basePath = storage_path('cruddy');

            return new Compiler($app['cruddy.repository'], $app['files'], $app['cruddy.lang'], $basePath);
        });
    }

    /**
     * Register thumbnail factory.
     */
    protected function registerThumbnailFactory()
    {
        $this->app->bindShared('cruddy.thumbs', function ($app)
        {
            return new ThumbnailFactory(new ImageManager, $app['cache']->driver());
        });
    }

    /**
     * Register cruddy aliases.
     */
    protected function registerAliases()
    {
        $baseNamespace = 'Kalnoy\Cruddy\\';

        $aliases = [
            'cruddy' => 'Environment',
            'cruddy.compiler' => 'Compiler',
            'cruddy.lang' => 'Lang',
            'cruddy.thumbs' => 'Service\ThumbnailFactory',
            'cruddy.repository' => 'Repository',
            'cruddy.permissions' => 'Service\Permissions\PermissionsManager',
            'cruddy.fields' => 'Schema\Fields\Factory',
            'cruddy.columns' => 'Schema\Columns\Factory',
            'cruddy.filters' => 'Schema\Filters\Factory',
            'cruddy.menu' => 'Service\MenuBuilder',
            'cruddy.assets' => 'Assets',
        ];

        foreach ($aliases as $key => $alias)
        {
            $this->app->alias($key, $baseNamespace.$alias);
        }
    }

    /**
     * @param Router $router
     * @param Config $config
     */
    protected function registerRoutes(Router $router, Config $config)
    {
        $before = $config->get('cruddy::auth_filter');
        $prefix = $config->get('cruddy::uri');
        $namespace = 'Kalnoy\Cruddy\Controllers';

        $router->group(compact('before', 'prefix', 'namespace'), function (Router $router)
        {
            $this->applyRoutingPattern($router);

            require __DIR__ . "/../../routes.php";
        });
    }

    /**
     * Fix #59
     *
     * @param Router $router
     */
    protected function applyRoutingPattern(Router $router)
    {
        $entities = app('cruddy.repository')->available();

        $router->pattern('cruddy_entity', '('.$entities.')');
    }

    /**
     * Register a composer for the layout.
     */
    protected function registerViewComposer()
    {
        $this->app->resolving('view', function (Factory $viewFactory)
        {
            $viewFactory->composer('cruddy::layout', 'Kalnoy\Cruddy\LayoutComposer');
        });
    }
}