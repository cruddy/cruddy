<?php

namespace Kalnoy\Cruddy;

use Illuminate\Support\ServiceProvider;
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
    protected $build = 5;

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
        $this->registerAssets();
        $this->registerLang();
        $this->registerMenu();
        $this->registerPermissions();
        $this->registerFields();
        $this->registerColumns();
        $this->registerRepository();
        $this->registerCruddy();
        $this->registerCommands();
        $this->registerCompiler();
        $this->registerThumbnailFactory();
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
    protected function registerFields()
    {
        $this->app->bindShared('cruddy.fields', function ($app)
        {
            return new Schema\Fields\Factory;
        });
    }

    /**
     * Register columns factory.
     */
    protected function registerColumns()
    {
        $this->app->bindShared('cruddy.columns', function ($app)
        {
            return new Schema\Columns\Factory;
        });
    }

    public function registerRepository()
    {
        $this->app->bindShared('cruddy.repository', function ($app)
        {
            return new Repository($app, $app['config']->get('cruddy::entities', []));
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

            $fields = $app['cruddy.fields'];
            $columns = $app['cruddy.columns'];
            $permissions = $app['cruddy.permissions'];
            $lang = $app['cruddy.lang'];
            $repository = $app['cruddy.repository'];

            $env = new Environment($config, $repository, $fields, $columns, $permissions, $lang, $app['events']);

            Entity::setEnvironment($env);

            BaseRepository::setFiles($app['files']);
            BaseRepository::setPaginationFactory($app['paginator']);

            return $env;
        });
    }

    /**
     * Register assets.
     *
     * @param \Kalnoy\Cruddy\Environment $env
     *
     * @return \Kalnoy\Cruddy\Environment
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
            return new Compiler($app['cruddy.repository'], $app['files'], $app['cruddy.lang']);
        });
    }

    /**
     * Register thumbnail factory.
     */
    protected function registerThumbnailFactory()
    {
        $this->app->bindShared('Kalnoy\Cruddy\Service\ThumbnailFactory', function ($app)
        {
            return new ThumbnailFactory(new ImageManager, $app['cache']->driver());
        });
    }
}