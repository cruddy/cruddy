<?php namespace Kalnoy\Cruddy;

use Illuminate\Support\Contracts\JsonableInterface;
use Illuminate\Http\Request;
use Illuminate\Config\Repository as Config;
use Illuminate\Events\Dispatcher;
use Symfony\Component\Translation\TranslatorInterface;
use Kalnoy\Cruddy\Schema\Fields\Factory as FieldFactory;
use Kalnoy\Cruddy\Schema\Columns\Factory as ColumnFactory;
use Kalnoy\Cruddy\Service\Permissions\PermissionsManager;
use Kalnoy\Cruddy\Schema\Repository as SchemaRepository;

class Environment implements JsonableInterface {

    /**
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * The request.
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * The translator.
     *
     * @var \Symfony\Component\Translation\TranslatorInterface;
     */
    protected $translator;

    /**
     * The schemas repository.
     *
     * @var \Kalnoy\Cruddy\Schema\Repository
     */
    protected $schemas;

    /**
     * The field factory.
     *
     * @var \Kalnoy\Cruddy\Schema\Fields\Factory
     */
    protected $fields;

    /**
     * The column factory.
     *
     * @var \Kalnoy\Cruddy\Schema\Columns\Factory
     */
    protected $columns;

    /**
     * @var \Kalnoy\Cruddy\Service\Permissions\PermissionsManager
     */
    protected $permissions;

    /**
     * Event dispatcher.
     *
     * @var \Illuminate\Events\Dispatcher
     */
    protected $dispatcher;

    /**
     * The list of css files.
     *
     * @var array
     */
    protected $css = [];

    /**
     * The list of js files.
     *
     * @var array
     */
    protected $js = [];

    /**
     * Some UI text lines for JavaScript.
     *
     * @var array
     */
    protected $lang = [];

    /**
     * The list of resolved entities.
     *
     * @var \Kalnoy\Cruddy\Entity[]
     */
    protected $resolved = [];

    public function __construct(
        Config $config, Request $request, TranslatorInterface $translator,
        SchemaRepository $schemas, FieldFactory $fields, ColumnFactory $columns,
        PermissionsManager $permissions, Dispatcher $dispatcher)
    {
        $this->config = $config;
        $this->request = $request;
        $this->translator = $translator;
        $this->schemas = $schemas;
        $this->fields = $fields;
        $this->columns = $columns;
        $this->permissions = $permissions;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Resolve an entity.
     *
     * @param $id
     *
     * @return \Kalnoy\Cruddy\Entity
     */
    public function entity($id)
    {
        if (isset($this->resolved[$id])) return $this->resolved[$id];

        $schema = $this->schemas->resolve($id);

        $this->resolved[$id] = $entity = $schema->entity($id);

        return $entity;
    }

    /**
     * Get configuration option from cruddy configuration file.
     *
     * @param      $key
     * @param null $default
     *
     * @return mixed
     */
    public function config($key, $default = null)
    {
        return $this->config->get("cruddy::{$key}", $default);
    }

    /**
     * Translate key.
     *
     * @param string $key
     * @param string $default
     *
     * @return string
     */
    public function translate($key, $default = null)
    {
        $line = $this->translator->trans($key);

        return $line === $key ? $default : $line;
    }

    /**
     * Register new field type.
     *
     * @param string $macro
     * @param string|Callable $callback
     *
     * @return $this
     */
    public function registerField($macro, $callback)
    {
        $this->fields->register($macro, $callback);

        return $this;
    }

    /**
     * Find a field with given id.
     *
     * @param string $id
     *
     * @return \Kalnoy\Cruddy\Schema\Fields\BaseField
     */
    public function field($id)
    {
        list($entity, $field) = explode('.', $id, 2);

        $entity = $this->entity($entity);
        $field = $entity->getFields()->get($field);

        if ( ! $field) throw new RuntimeException("The field with an id of [{$id}] is not found.");

        return $field;
    }

    /**
     * Register new column type.
     *
     * @param string $macro
     * @param string|Callable $callback
     *
     * @return $this
     */
    public function registerColumn($macro, $callback)
    {
        $this->columns->register($macro, $callback);

        return $this;
    }

    /**
     * Extend permissions manager.
     *
     * @param string $driver
     * @param \Closure $callback
     *
     * @return $this
     */
    public function extendPermissions($driver, $callback)
    {
        $this->permissions->extend($driver, $callback);

        return $this;
    }

    /**
     * Add extra css files.
     *
     * @param string|array $uri
     *
     * @return $this
     */
    public function css($uri)
    {
        $uri = is_array($uri) ? $uri : func_get_args();

        $this->css = array_merge($this->css, $uri);

        return $this;
    }

    /**
     * Add extra js files.
     *
     * @param string|array $uri
     *
     * @return $this
     */
    public function js($uri)
    {
        $uri = is_array($uri) ? $uri : func_get_args();

        $this->js = array_merge($this->js, $uri);

        return $this;
    }

    /**
     * Add some lines for JavaScript ui.
     *
     * @param array $items
     *
     * @return $this
     */
    public function lang(array $items)
    {
        $this->lang += array_map(function ($string)
        {
            return \Kalnoy\Cruddy\try_trans($string);

        }, $items);

        return $this;
    }

    /**
     * Render scripts.
     *
     * @return string
     */
    public function scripts()
    {
        return implode("\r\n", array_map(function ($uri)
        {
            return "<script src='{$uri}'></script>";

        }, $this->js));
    }

    /**
     * Render styles.
     *
     * @return string
     */
    public function styles()
    {
        return implode("\r\n", array_map(function ($uri)
        {
            return "<link rel='stylesheet' href='{$uri}'>";

        }, $this->css));
    }

    /**
     * Get whether the action for an entity is permitted.
     *
     * @param string $action
     * @param \Kalnoy\Cruddy\Entity $entity
     *
     * @return bool
     */
    public function isPermitted($action, Entity $entity)
    {
        return $this->permissions->isPermitted($action, $entity);
    }

    /**
     * Get field factory.
     *
     * @return \Kalnoy\Cruddy\Schema\Fields\Factory
     */
    public function getFieldFactory()
    {
        return $this->fields;
    }

    /**
     * Get column factory.
     *
     * @return \Kalnoy\Cruddy\Schema\Columns\Factory
     */
    public function getColumnFactory()
    {
        return $this->columns;
    }

    /**
     * Permissions object.
     *
     * @return \Kalnoy\Cruddy\Service\Permissions\PermissionsManager
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * Get schema's repository.
     *
     * @return \Kalnoy\Cruddy\Schema\Repository
     */
    public function getSchemaRepository()
    {
        return $this->schemas;
    }

    /**
     * Resolve and convert all entities to array.
     *
     * @return array
     */
    protected function getAllEntities()
    {
        $classes = $this->schemas->getClasses();
        $data = [];

        foreach ($classes as $key => $value)
        {
            $data[] = $this->entity($key)->toArray();
        }

        return $data;
    }

    /**
     * Get built-in UI strings.
     *
     * @return array
     */
    protected function getDefaultLang()
    {
        $keys = array_keys(include __DIR__.'/../../lang/en/js.php');

        $strings = array_map(function ($key)
        {
            return $this->translator->trans("cruddy::js.{$key}");

        }, $keys);

        return array_combine($keys, $strings);
    }

    /**
     * @inheritdoc
     * 
     * @param int $options
     *
     * @return string
     */
    public function toJSON($options = 0)
    {
        return json_encode(
        [
            'locale' => $this->config->get('app.locale'),
            'uri' => $this->config('uri'),
            'root' => $this->request->root(),
            'ace_theme' => $this->config('ace_theme', 'chrome'),
            'entities' => $this->getAllEntities(),
            'lang' => $this->getDefaultLang() + $this->lang,

        ], $options);
    }

    /**
     * Get event dispatcher.
     *
     * @return \Illuminate\Events\Dispatcher
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }
}