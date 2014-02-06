<?php namespace Kalnoy\Cruddy;

use Illuminate\Support\Contracts\JsonableInterface;
use Illuminate\Http\Request;
use Illuminate\Config\Repository as Config;
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
     * The list of resolved entities.
     *
     * @var \Kalnoy\Cruddy\Entity[]
     */
    protected $resolved = [];

    public function __construct(
        Config $config, Request $request, TranslatorInterface $translator,
        SchemaRepository $schemas, FieldFactory $fields, ColumnFactory $columns,
        PermissionsManager $permissions)
    {
        $this->config = $config;
        $this->request = $request;
        $this->translator = $translator;
        $this->schemas = $schemas;
        $this->fields = $fields;
        $this->columns = $columns;
        $this->permissions = $permissions;
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

        $entity = $this->resolved[$id] = new Entity($this, $schema, $id);

        return $entity->init();
    }

    /**
     * Render a menu.
     *
     * @return string
     */
    public function menu()
    {
        $menu = new Service\Menu($this);

        return $menu->render($this->config('menu'));
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
    public function field($macro, $callback)
    {
        $this->fields->register($macro, $callback);

        return $this;
    }

    /**
     * Register new column type.
     *
     * @param string $macro
     * @param string|Callable $callback
     *
     * @return $this
     */
    public function column($macro, $callback)
    {
        $this->columns->register($macro, $callback);

        return $this;
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

        ], $options);
    }
}