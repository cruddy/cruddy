<?php

namespace Kalnoy\Cruddy;

use RuntimeException;
use Illuminate\Support\Collection;
use Illuminate\Support\Contracts\JsonableInterface;
use Illuminate\Support\Contracts\ArrayableInterface;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Kalnoy\Cruddy\Contracts\Schema as SchemaContract;
use Kalnoy\Cruddy\Schema\InstanceFactory;
use Kalnoy\Cruddy\Contracts\InlineRelation;
use Kalnoy\Cruddy\Service\Validation\ValidationException;
use Kalnoy\Cruddy\Contracts\SearchProcessor;
use Kalnoy\Cruddy\Repo\ChainedSearchProcessor;

/**
 * The entity class that is responsible for operations on model.
 *
 * @since 1.0.0
 */
class Entity implements JsonableInterface, ArrayableInterface {

    /**
     * Cruddy environment.
     *
     * @var Environment
     */
    protected static $env;

    /**
     * The schema.
     *
     * @var SchemaContract
     */
    protected $schema;

    /**
     * The id.
     *
     * @var string
     */
    protected $id;

    /**
     * The field list.
     *
     * @var Schema\Fields\Collection
     */
    private $fields;

    /**
     * The column list.
     *
     * @var Schema\Columns\Collection
     */
    private $columns;

    /**
     * @var Schema\Filters\Collection
     */
    private $filters;

    /**
     * The repository.
     *
     * @var \Kalnoy\Cruddy\Contracts\Repository
     */
    private $repo;

    /**
     * The validator.
     *
     * @var Contracts\Validator
     */
    private $validator;

    /**
     * The list of all actions.
     *
     * @var array
     */
    protected static $actions = [ 'view', 'update', 'create', 'delete' ];

    /**
     * Init entity.
     *
     * @param SchemaContract $schema
     */
    public function __construct(SchemaContract $schema)
    {
        $this->schema = $schema;
    }

    /**
     * Find an item with given id.
     *
     * @param mixed $id
     *
     * @return array
     *
     * @throws ModelNotFoundException
     */
    public function find($id)
    {
        $model = $this->getRepository()->find($id);

        return $this->extract($model);
    }

    /**
     * Extract model fields.
     *
     * @param array|\Illuminate\Database\Eloquent\Model $model
     *
     * @return array
     */
    public function extract($model)
    {
        if ( ! $model) return null;

        if (is_array($model) or $model instanceof Collection)
        {
            return $this->extractAll($model);
        }

        $attributes = $this->getFields()->extract($model);
        $title = $this->schema->toString($model);
        $extra = $this->schema->extra($model, false);

        return compact('attributes', 'title', 'extra');
    }

    /**
     * Extract fields of all models.
     *
     * @param array|Collection $items
     *
     * @return array
     */
    public function extractAll($items)
    {
        if ($items instanceof Collection)
        {
            $items = $items->all();
        }

        return array_map([ $this, 'extract' ], $items);
    }

    /**
     * Search items.
     *
     * Available options:
     *
     * - `page` -- the page number
     * - `per_page` -- the number of items per page. The default value is taken
     *     from the model
     * - `order` -- the array of key-value pairs where key is a column id and value
     *     is order direction:
     *
     *     ```php
     *     ['name' => 'asc']
     *     ```
     * - `keywords` -- the keywords to search by
     * - `filters` -- the filter data
     * - `simple` -- whether to return simple result set that includes only two
     *     values per item: `id` that is primary key and `title` that is an item converted
     *     to the string.
     * - `owner` -- the id of the field that is used to process query.
     *
     * @param array $options
     *
     * @return \Illuminate\Pagination\Paginator
     */
    public function search(array $options)
    {
        $results = $this->getRepository()->search($options, $this->getSearchProcessor($options));

        if (array_get($options, 'simple'))
        {
            $results->setItems($this->simplifyAll($results->getItems()));
        }
        else
        {
            $results->setItems($this->getColumns()->extractAll($results->getItems()));
        }

        return $results;
    }

    /**
     * Get a search processor for a repo.
     *
     * @param array $options
     *
     * @return Contracts\SearchProcessor
     */
    protected function getSearchProcessor(array $options)
    {
        $processor = new ChainedSearchProcessor([
            $this->getFields(),
            $this->getColumns(),
            $this->getFilters(),
        ]);

        if (isset($options['owner']) && static::$env !== null)
        {
            $field = static::$env->field($options['owner']);

            if ( ! $field instanceof SearchProcessor)
            {
                throw new RuntimeException("The field [{$options['owner']}] is not a search processor.");
            }

            $processor->add($field);
        }

        return $processor;
    }

    /**
     * Convert all items to simple representation which is used by an entity dropdown.
     *
     * @param array $items
     *
     * @return array
     */
    public function simplifyAll($items)
    {
        if ($items instanceof Collection)
        {
            $items = $items->all();
        }

        return array_map([$this, 'simplify'], $items);
    }

    /**
     * Convert item to a simple representation which is used by an entity dropdown.
     *
     * @param array|\Illuminate\Database\Eloquent\Model $model
     *
     * @return array
     */
    public function simplify($model)
    {
        if ( ! $model) return null;

        if (is_array($model) or $model instanceof Collection)
        {
            return $this->simplifyAll($model);
        }

        $id = $model->getKey();
        $title = $this->schema->toString($model);
        $extra = $this->schema->extra($model, true);

        return compact('id', 'title', 'extra');
    }

    /**
     * Register saving event.
     *
     * @param string $id
     * @param mixed $callback
     *
     * @return void
     */
    public static function saving($id, $callback)
    {
        static::registerEvent($id, 'saving', $callback);
    }

    /**
     * Register saved event.
     *
     * @param string $id
     * @param mixed $callback
     *
     * @return void
     */
    public static function saved($id, $callback)
    {
        static::registerEvent($id, 'saved', $callback);
    }

    /**
     * Register entity event handler.
     *
     * @param string $id
     * @param string $event
     * @param mixed $callback
     *
     * @return void
     */
    public static function registerEvent($id, $event, $callback)
    {
        if ( ! static::$env) return;

        static::$env->getDispatcher()->listen("entity.{$event}: {$id}", $callback);
    }

    /**
     * Fire entity event.
     *
     * @param string $event
     * @param array  $payload
     * @param bool   $halt
     *
     * @return mixed
     */
    public function fireEvent($event, array $payload, $halt = true)
    {
        if ( ! isset(static::$env)) return null;

        $event = "entity.{$event}: {$this->id}";

        return static::$env->getDispatcher()->fire($event, $payload, $halt);
    }

    /**
     * Delete model or a set of models.
     *
     * @param int|array $ids
     *
     * @return int
     */
    public function delete($ids)
    {
        return $this->getRepository()->delete($ids);
    }

    /**
     * Translate line.
     *
     * If key isn't namespaced, looks for a key under entity's id namespace,
     * then under `entities` namespace. Othwerwise, just translates line as is.
     *
     * @param string $key
     * @param string $default
     *
     * @return string
     */
    public function translate($key, $default = null)
    {
        if ( ! static::$env) return $default;

        if (false !== $pos = strpos($key, '::'))
        {
            if ($pos === 0) $key = substr($key, 2);

            return static::$env->translate($key, $default);
        }

        $line = static::$env->translate("{$this->id}.{$key}");

        if ($line !== null) return $line;

        return static::$env->translate("entities.{$key}", $default);
    }

    /**
     * Get field collection.
     *
     * @return Schema\Fields\Collection
     */
    public function getFields()
    {
        if ($this->fields === null) return $this->fields = $this->createFields();

        return $this->fields;
    }

    /**
     * Create field collection.
     *
     * @return Schema\Fields\Collection
     */
    protected function createFields()
    {
        $factory = $this->getFieldsFactory();

        $collection = new Schema\Fields\Collection;

        $schema = new InstanceFactory($factory, $this, $collection);

        $this->schema->fields($schema);

        return $collection;
    }

    /**
     * Get column collection.
     *
     * @return Schema\Columns\Collection
     */
    public function getColumns()
    {
        if ($this->columns === null) return $this->columns = $this->createColumns();

        return $this->columns;
    }

    /**
     * Create column collection.
     *
     * @return Schema\Columns\Collection
     */
    public function createColumns()
    {
        $factory = $this->getColumnsFactory();

        $collection = new Schema\Columns\Collection;

        $schema = new InstanceFactory($factory, $this, $collection);

        $this->schema->columns($schema);

        return $this->ensurePrimaryColumn($collection);
    }

    /**
     * @return Schema\Filters\Collection
     */
    public function getFilters()
    {
        if ($this->filters === null) return $this->filters = $this->createFilters();

        return $this->filters;
    }

    /**
     * @return Schema\Filters\Collection
     */
    public function createFilters()
    {
        $factory = $this->getFiltersFactory();

        $collection = new Schema\Filters\Collection;

        $schema = new InstanceFactory($factory, $this, $collection);

        $this->schema->filters($schema);

        return $collection;
    }

    /**
     * Ensure that primary column is exists.
     *
     * @param Schema\Columns\Collection $collection
     *
     * @return Schema\Columns\Collection
     */
    protected function ensurePrimaryColumn($collection)
    {
        $keyName = $this->getRepository()->newModel()->getKeyName();

        if ( ! $collection->has($keyName))
        {
            $field = $this->getFields()->get($keyName);

            $column = new Schema\Columns\Types\Proxy($this, $keyName, $field);

            $collection->add($column->hide());
        }

        return $collection;
    }

    /**
     * Get the repository.
     *
     * @return \Kalnoy\Cruddy\Contracts\Repository
     */
    public function getRepository()
    {
        if ($this->repo === null)
        {
            return $this->repo = $this->schema->repository();
        }

        return $this->repo;
    }

    /**
     * Get the validator.
     *
     * @return Contracts\Validator
     */
    public function getValidator()
    {
        if ($this->validator === null)
        {
            return $this->validator = $this->schema->validator();
        }

        return $this->validator;
    }

    /**
     * Get cruddy environment instance.
     *
     * @return Environment
     */
    public static function getEnvironment()
    {
        return static::$env;
    }

    /**
     * Set environment instance.
     *
     * @param Environment $env
     */
    public static function setEnvironment(Environment $env)
    {
        static::$env = $env;
    }

    /**
     * Get entity's schema.
     *
     * @return SchemaContract
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * Get entity's id.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set an id.
     *
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get list of plural and singular forms of title.
     *
     * @return array
     */
    public function getTitle()
    {
        return
        [
            'plural' => $this->makeTitle('plural'),
            'singular' => $this->makeTitle('singular'),
        ];
    }

    /**
     * Get plural form of title.
     *
     * @return string
     */
    public function getPluralTitle()
    {
        return $this->makeTitle('plural');
    }

    /**
     * Get singular form of title.
     *
     * @return string
     */
    public function getSingularTitle()
    {
        return $this->makeTitle('singular');
    }

    /**
     * Make title for specified plurality.
     *
     * @param string $plurality
     *
     * @return string
     */
    protected function makeTitle($plurality)
    {
        $result = $this->translate("title.{$plurality}");

        if ($result !== null) return $result;

        $func = "str_{$plurality}";

        return Helpers::labelFromId($func($this->id));
    }

    /**
     * Get permissions for every action.
     *
     * @return array
     */
    public function getPermissions()
    {
        $permissions = $this->getPermissionsDriver();

        $data = [];

        foreach (static::$actions as $action)
        {
            $data[$action] = $permissions->isPermitted($action, $this);
        }

        return $data;
    }

    /**
     * @param $action
     *
     * @return bool
     */
    public function isPermitted($action)
    {
        return $this->getPermissionsDriver()->isPermitted($action, $this);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        $model = $this->getRepository()->newModel();

        return
        [
            'id' => $this->id,
            'soft_deleting' => false,
            'defaults' => $this->getFields()->extract($model),
            'title' => $this->getTitle(),

            'fields' => $this->getFields()->export(),
            'columns' => $this->getColumns()->export(),
            'filters' => $this->getFilters()->export(),
            'related' => $this->getInlineFieldsIds(),

        ] + $this->schema->toArray() + [ 'view' => 'Cruddy.Entity.Page' ];
    }

    /**
     * {@inheritdoc}
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * @return Schema\Filters\Factory
     */
    protected function getFiltersFactory()
    {
        return app('cruddy.filters');
    }

    /**
     * @return Schema\Columns\Factory
     */
    protected function getColumnsFactory()
    {
        return app('cruddy.columns');
    }

    /**
     * @return Schema\Fields\Factory
     */
    protected function getFieldsFactory()
    {
        return app('cruddy.fields');
    }

    /**
     * @return Contracts\Permissions
     */
    private function getPermissionsDriver()
    {
        return app('cruddy.permissions')->driver();
    }

    /**
     * @return array
     */
    protected function getInlineFieldsIds()
    {
        $result = [];

        foreach ($this->getFields() as $id => $field)
        {
            if ($field instanceof InlineRelation) $result[] = $id;
        }

        return $result;
    }

}
