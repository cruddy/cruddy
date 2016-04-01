<?php

namespace Kalnoy\Cruddy;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Routing\Registrar;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\View\Factory;
use Kalnoy\Cruddy\Service\MenuBuilder;
use Kalnoy\Cruddy\Service\PermitsEverything;
use Kalnoy\Cruddy\Service\ThumbnailFactory;
use Intervention\Image\ImageManager;
use Illuminate\Routing\Router;

class CruddyServiceProvider extends ServiceProvider
{

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
    protected $build = 26;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'cruddy');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'cruddy');

        $this->publishes([  __DIR__.'/../public' => public_path('cruddy') ], 'public');

        $this->publishes([ __DIR__.'/../resources/views' => base_path('resources/views/vendor/cruddy') ], 'views');

        $this->publishes([ __DIR__.'/../config/cruddy.php' => config_path('cruddy.php') ], 'config');

        $this->registerRoutes($this->app['router'], $this->app['config']);

        Entity::setEventDispatcher($this->app->make('events'));
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
        $this->app->singleton('cruddy.lang', function (Container $app) {
            return new Lang($app->make('translator'));
        });
    }

    /**
     * Register menu builder.
     *
     * @return void
     */
    public function registerMenu()
    {
        $this->app->singleton('cruddy.menu', function (Application $app) {
            $builder = new MenuBuilder($app->make('cruddy'));

            $builder->setUrlGenerator($app->make('url'));
            $builder->setTranslator($app->make('translator'));
            $builder->setContainer($app);
            $builder->setRequest($app->rebinding('request', function ($request) use ($builder) {
                $builder->setRequest($request);
            }));

            return $builder;
        });
    }

    /**
     * Register permissions service.
     *
     * @return void
     */
    public function registerPermissions()
    {
        $this->app->singleton('cruddy.permissions', function ($app) {
            $driver = $app['config']->get('cruddy.permissions');

            return $driver ? $app[$driver] : new PermitsEverything;
        });
    }

    /**
     * Register fields factory.
     */
    protected function registerFactories()
    {
        $this->app->singleton('cruddy.fields', Schema\Fields\Factory::class);
        $this->app->singleton('cruddy.columns', Schema\Columns\Factory::class);
        $this->app->singleton('cruddy.filters', Schema\Filters\Factory::class);
    }

    /**
     * Register entity repository.
     */
    public function registerRepository()
    {
        $this->app->singleton('cruddy.repository', function (Container $app) {
            $config = $app->make('config');

            return new Repository($app, $config->get('cruddy.entities', [ ]));
        });
    }

    /**
     * Register cruddy environment.
     *
     * @return void
     */
    protected function registerCruddy()
    {
        $this->app->singleton('cruddy', function (Container $app) {
            $permissions = $app->make('cruddy.permissions');
            $repository = $app->make('cruddy.repository');
            $lang = $app->make('cruddy.lang');

            return new Environment($repository, $permissions, $lang);
        });
    }

    /**
     * Register assets.
     */
    protected function registerAssets()
    {
        $this->app->singleton('cruddy.assets', function ($app) {
            $baseDir = 'cruddy';

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
     * @param array $items
     *
     * @return array
     */
    protected function assets($baseDir, array $items)
    {
        /** @var UrlGenerator $url */
        $url = $this->app->make('url');

        return array_map(function ($item) use ($url, $baseDir) {
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
        return $this->assets($baseDir.'/css', [ 'styles.min.css' ]);
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
        $ext = config('app.debug') ? '.js' : '.min.js';

        return $this->assets($baseDir.'/js', [ 'vendor'.$ext, 'app'.$ext ]);
    }

    /**
     * Register console commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        $this->commands([
                            Console\MakeEntityCommand::class,
                            Console\CompileCommand::class,
                            Console\ClearCompiledCommand::class,
                        ]);
    }

    /**
     * Register schema compiler.
     */
    protected function registerCompiler()
    {
        $this->app->singleton('cruddy.compiler', function (Container $app) {
            $basePath = storage_path('cruddy');

            $repository = $app->make('cruddy.repository');
            $files = $app->make('files');
            $lang = $app->make('cruddy.lang');

            return new Compiler($repository, $files, $lang, $basePath);
        });
    }

    /**
     * Register thumbnail factory.
     */
    protected function registerThumbnailFactory()
    {
        $this->app->singleton('cruddy.thumbs', function (Container $app) {
            return new ThumbnailFactory(new ImageManager, $app->make('cache')
                                                              ->driver());
        });
    }

    /**
     * Register cruddy aliases.
     */
    protected function registerAliases()
    {
        $aliases = [
            'cruddy' => Environment::class,
            'cruddy.compiler' => Compiler::class,
            'cruddy.lang' => Lang::class,
            'cruddy.thumbs' => Service\ThumbnailFactory::class,
            'cruddy.repository' => Repository::class,
            'cruddy.permissions' => Contracts\Permissions::class,
            'cruddy.menu' => Service\MenuBuilder::class,
            'cruddy.assets' => Assets::class,
        ];

        foreach ($aliases as $key => $alias) {
            $this->app->alias($key, $alias);
        }
    }

    /**
     * @param Router $router
     * @param Config $config
     */
    protected function registerRoutes(Router $router, Config $config)
    {
        $this->applyRoutingPattern($router);

        $group = [
            'middleware' => $config->get('cruddy.middleware'),
            'prefix' => $config->get('cruddy.uri', 'backend'),
            'namespace' => 'Kalnoy\Cruddy\Http\Controllers',
        ];

        $router->group($group, function (Router $router) {
            require __DIR__.'/Http/routes.php';
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
        $this->app->resolving('view', function (Factory $viewFactory) {
            $viewFactory->composer('cruddy::layout', LayoutComposer::class);
        });
    }

}
