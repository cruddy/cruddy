<?php

namespace Kalnoy\Cruddy;

use Illuminate\Contracts\Events\Dispatcher;
use Kalnoy\Cruddy\Contracts\SearchProcessor;
use Kalnoy\Cruddy\Schema\AttributesCollection;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Kalnoy\Cruddy\Contracts\Schema as SchemaContract;
use Kalnoy\Cruddy\Schema\InstanceFactory;
use Kalnoy\Cruddy\Repo\ChainedSearchProcessor;

/**
 * The entity class that is responsible for operations on model.
 *
 * @since 1.0.0
 */
class Entity implements Jsonable, Arrayable {

    /**
     * @var Dispatcher
     */
    protected static $dispatcher;

    /**
     * @var Repository
     */
    protected $entities;

    /**
     * @var Lang
     */
    protected $lang;

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
        $model = $this->repository()->find($id);

        return $this->extract($model);
    }

    /**
     * Extract model fields.
     *
     * @param array|Model $model
     *
     * @return array
     */
    public function extract($model, AttributesCollection $collection = null)
    {
        if ( ! $model) return null;

        if (is_array($model) or $model instanceof Collection)
        {
            return $this->extractAll($model, $collection);
        }

        if ($collection === null) $collection = $this->fields();

        $attributes = $collection->extract($model);
        $meta = $this->getMetaDataForModel($model);

        return compact('attributes', 'meta');
    }

    /**
     * Extract fields of all models.
     *
     * @param array|Collection $items
     * @param AttributesCollection $collection
     *
     * @return array
     */
    public function extractAll($items, AttributesCollection $collection = null)
    {
        if ($items instanceof Collection)
        {
            $items = $items->all();
        }

        return array_map(function ($model) use ($collection)
        {
            return $this->extract($model, $collection);

        }, $items);
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
     * @return array
     */
    public function search(array $options)
    {
        $results = $this->repository()->search($options, $this->getSearchProcessor($options));

        if (array_get($options, 'simple'))
        {
            $results['items'] = $this->simplifyAll($results['items']);
        }
        else
        {
            $results['items'] = $this->extractAll($results['items'], $this->columns());
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
            $this->fields(),
            $this->columns(),
            $this->filters(),
        ]);

        if (isset($options['owner']))
        {
            $field = $this->entities->field($options['owner']);

            if ( ! $field instanceof SearchProcessor)
            {
                throw new \RuntimeException("Cannot use field [{$options['owner']}] as owner.");
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
     * @param array|Model $model
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

        return $this->getMetaDataForModel($model, true);
    }

    /**
     * @param Model $model
     * @param bool $simplified
     *
     * @return array
     */
    public function getMetaDataForModel(Model $model, $simplified = false)
    {
        $id = $model->getKey();

        return compact('id') + $this->schema->meta($model, $simplified);
    }

    /**
     * @param string $owner
     *
     * @return array
     */
    public function relations($owner = null)
    {
        return $this->fields()->relations($owner);
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
        if ( ! static::$dispatcher) return;

        static::$dispatcher->listen("entity.{$event}: {$id}", $callback);
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
        if ( ! isset(static::$dispatcher)) return null;

        $event = "entity.{$event}: {$this->id}";

        return static::$dispatcher->fire($event, $payload, $halt);
    }

    /**
     * @param Dispatcher $dispatcher
     */
    public static function setEventDispatcher(Dispatcher $dispatcher)
    {
        static::$dispatcher = $dispatcher;
    }

    /**
     * @return Dispatcher
     */
    public static function getEventDispatcher()
    {
        return static::$dispatcher;
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
        return $this->repository()->delete($ids);
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
        $lang = $this->getLang();

        if (false !== $pos = strpos($key, '::'))
        {
            if ($pos === 0) $key = substr($key, 2);

            return $lang->translate($key, $default);
        }

        $line = $lang->translate("{$this->id}.{$key}");

        if ($line !== null) return $line;

        return $lang->translate("entities.{$key}", $default);
    }

    /**
     * Get field collection.
     *
     * @return Schema\Fields\Collection
     */
    public function fields()
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
        $collection = new Schema\Fields\Collection($this);

        $schema = new InstanceFactory($this->getFieldsFactory(), $collection);

        $this->schema->fields($schema);

        return $collection;
    }

    /**
     * Get column collection.
     *
     * @return Schema\Columns\Collection
     */
    public function columns()
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
        $collection = new Schema\Columns\Collection($this);

        $schema = new InstanceFactory($this->getColumnsFactory(), $collection);

        $this->schema->columns($schema);

        return $collection;
    }

    /**
     * @return Schema\Filters\Collection
     */
    public function filters()
    {
        if ($this->filters === null) return $this->filters = $this->createFilters();

        return $this->filters;
    }

    /**
     * @return Schema\Filters\Collection
     */
    public function createFilters()
    {
        $collection = new Schema\Filters\Collection($this);

        $schema = new InstanceFactory($this->getFiltersFactory(), $collection);

        $this->schema->filters($schema);

        return $collection;
    }

    /**
     * Get the repository.
     *
     * @return \Kalnoy\Cruddy\Contracts\Repository
     */
    public function repository()
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
    public function validator()
    {
        if ($this->validator === null)
        {
            return $this->validator = $this->schema->validator();
        }

        return $this->validator;
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
        return [
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
        $result = $this->translate("::{$this->id}.title.{$plurality}");

        if ($result !== null) return $result;

        $result = $this->translate("::entities.titles.{$this->id}.{$plurality}");

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
        $model = $this->repository()->newModel();

        return [
            'id' => $this->id,
            'primary_key' => $model->getKeyName(),
            'soft_deleting' => false,
            'defaults' => $this->fields()->extract($model),
            'title' => $this->getTitle(),

            'fields' => $this->fields()->export(),
            'columns' => $this->columns()->export(),
            'filters' => $this->filters()->export(),

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
    protected function getPermissionsDriver()
    {
        return app('cruddy.permissions')->driver();
    }

    /**
     * @param Repository $entities
     */
    public function setEntitiesRepository(Repository $entities)
    {
        $this->entities = $entities;
    }

    /**
     * @return Repository
     */
    public function getEntitiesRepository()
    {
        return $this->entities;
    }

    /**
     * @param Lang $lang
     */
    public function setLang(Lang $lang)
    {
        $this->lang = $lang;
    }

    /**
     * @return Lang
     */
    public function getLang()
    {
        return $this->lang ?: app('cruddy.lang');
    }

}
