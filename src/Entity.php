<?php

namespace Kalnoy\Cruddy;

use Illuminate\Contracts\Events\Dispatcher;
use Kalnoy\Cruddy\Contracts\Permissions;
use Kalnoy\Cruddy\Contracts\SearchProcessor;
use Kalnoy\Cruddy\Repo\Stub;
use Kalnoy\Cruddy\Schema\AttributesCollection;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Kalnoy\Cruddy\Contracts\Schema as SchemaContract;
use Kalnoy\Cruddy\Schema\InstanceFactory;
use Kalnoy\Cruddy\Repo\ChainedSearchProcessor;
use Kalnoy\Cruddy\Service\Validation\FluentValidator;

/**
 * The entity class that is responsible for operations on model.
 *
 * @since 1.0.0
 */
abstract class Entity implements Jsonable, Arrayable {

    /**
     * Action for creating new items.
     */
    const CREATE = 'create';

    /**
     * Action for viewing an item.
     */
    const READ = 'read';

    /**
     * Action for updating items.
     */
    const UPDATE = 'update';

    /**
     * Action for deleting items.
     */
    const DELETE = 'delete';

    /**
     * The state of model when it is new.
     */
    const WHEN_NEW = self::CREATE;

    /**
     * The state of model when it is exists.
     */
    const WHEN_EXISTS = self::UPDATE;

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
     * The id.
     *
     * @var string
     */
    protected $id;

    /**
     * @var Schema\Actions\Collection
     */
    private $actions;

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
    private $filtersCollection;

    /**
     * @var Contracts\Repository
     */
    private $repo;

    /**
     * @var Contracts\Validator
     */
    private $validator;

    /**
     * @var Schema\Layout\Layout
     */
    private $layout;

    /**
     * The list of relations that will be eagerly loaded.
     *
     * @var array
     */
    protected $eagerLoads = [];

    /**
     * The model class name.
     *
     * @var string
     */
    protected $model;

    /**
     * The array of default attributes.
     *
     * @var array
     */
    protected $defaults = [];

    /**
     * The list of complex filters.
     *
     * @var array
     */
    protected $filters = [];

    /**
     * The attribute that is used to convert model to a string.
     *
     * @var string
     */
    protected $titleAttribute;

    /**
     * The id of column that will be ordered by default.
     *
     * @var string
     */
    protected $defaultOrder;

    /**
     * The number of items per page.
     *
     * Set this value to override default model's value.
     *
     * @var int
     */
    protected $perPage;

    /**
     * The path to the Backbone view class that will display the entity.
     *
     * @var string
     */
    protected $view = 'Cruddy.Entity.Page';

    /**
     * Specify the fields.
     *
     * @param Schema\Fields\InstanceFactory $schema
     */
    abstract protected function fields($schema);

    /**
     * Specify the columns.
     *
     * @param Schema\Columns\InstanceFactory $schema
     */
    protected function columns($schema) {}

    /**
     * Specify filters.
     *
     * @param Schema\Filters\InstanceFactory $schema
     */
    protected function filters($schema)
    {
        foreach ($this->filters as $field)
        {
            $schema->usingField($field);
        }
    }

    /**
     * Get default attributes.
     *
     * @return array
     */
    protected function defaults()
    {
        return $this->defaults;
    }

    /**
     * Specify what files repository uploads.
     *
     * @param Repo\BaseRepository $repo
     */
    protected function files($repo) {}

    /**
     * Set up validator.
     *
     * @param FluentValidator $validate
     */
    protected function rules($validate) {}

    /**
     * Set up actions.
     *
     * @param Schema\Actions\Collection $actions
     */
    protected function actions($actions) {}

    /**
     * Define the layout.
     *
     * @param Schema\Layout\Layout $l
     */
    protected function layout($l) {}

    /**
     * Get links for model that will be displayed in data table and form.
     *
     * @param Model $model
     *
     * @return array
     */
    protected function links($model)
    {
        $result = [];

        if ($value = $this->toUrl($model))
        {
            $result[app('cruddy.lang')->translate('cruddy::js.view_external')] = $value;
        }

        return $result;
    }

    /**
     * Convert the model to string.
     *
     * @param Model $model
     *
     * @return string
     */
    public function toString($model)
    {
        return $this->titleAttribute ? $model->getAttribute($this->titleAttribute) : $model->getKey();
    }

    /**
     * Get the url to the model on main site.
     *
     * @param Model $model
     *
     * @return string
     */
    protected function toUrl($model) {}

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
     * @param array|Model $model
     * @param AttributesCollection $collection
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

        if ($collection === null) $collection = $this->getFields();

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
        $results = $this->getRepository()->search($options, $this->getSearchProcessor($options));

        if (array_get($options, 'simple'))
        {
            $results['items'] = $this->simplifyAll($results['items']);
        }
        else
        {
            $results['items'] = $this->extractAll($results['items'], $this->getColumns());
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
        $meta['id'] = $model->getKey();
        $meta['title'] = $this->toString($model);

        if ( ! $simplified)
        {
            $meta['links'] = $this->links($model);
            $meta['actions'] = $this->getActions()->export($model);
        }

        return $meta;
    }

    /**
     * Get a list of eagerly loaded relations, optionally prefixed with owner relation.
     *
     * @param string|null $owner
     *
     * @return array
     */
    public function eagerLoads($owner = null)
    {
        if (is_null($owner)) return $this->eagerLoads;

        return array_map(function ($item) use ($owner)
        {
            return $owner.'.'.$item;

        }, $this->eagerLoads);
    }

    /**
     * Get a list of relations of the model.
     *
     * @param string $owner
     *
     * @return array
     */
    public function relations($owner = null)
    {
        return $this->getFields()->relations($owner);
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
     * @return Schema\Actions\Collection
     */
    public function getActions()
    {
        if ($this->actions === null) return $this->actions = $this->createActions();

        return $this->actions;
    }

    /**
     * @return Schema\Actions\Collection
     */
    protected function createActions()
    {
        $collection = new Schema\Actions\Collection;

        $this->actions($collection);

        return $collection;
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
        $collection = new Schema\Fields\Collection($this);

        $schema = new Schema\Fields\InstanceFactory($this->getFieldsFactory(), $collection);

        $this->fields($schema);

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
        $collection = new Schema\Columns\Collection($this);

        $schema = new Schema\Columns\InstanceFactory($this->getColumnsFactory(), $collection);

        $this->columns($schema);

        return $collection;
    }

    /**
     * @return Schema\Filters\Collection
     */
    public function getFilters()
    {
        if ($this->filtersCollection === null) return $this->filtersCollection = $this->createFilters();

        return $this->filtersCollection;
    }

    /**
     * @return Schema\Filters\Collection
     */
    public function createFilters()
    {
        $collection = new Schema\Filters\Collection($this);

        $schema = new Schema\Filters\InstanceFactory($this->getFiltersFactory(), $collection);

        $this->filters($schema);

        return $collection;
    }

    /**
     * Get the repository.
     *
     * @return Contracts\Repository
     */
    public function getRepository()
    {
        if ($this->repo === null)
        {
            return $this->repo = $this->createRepository();
        }

        return $this->repo;
    }

    /**
     * @return Stub
     */
    public function createRepository()
    {
        $repo = new Stub($this->model, $this->defaults());

        $repo->perPage = $this->perPage;

        $this->files($repo);

        return $repo;
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
            return $this->validator = $this->createValidator();
        }

        return $this->validator;
    }

    /**
     * @return FluentValidator
     */
    public function createValidator()
    {
        $validator = new FluentValidator;

        $this->rules($validator);

        return $validator;
    }

    /**
     * @return Schema\Layout\Layout
     */
    public function getLayout()
    {
        if ($this->layout === null) return $this->layout = $this->createLayout();

        return $this->layout;
    }

    /**
     * @return Schema\Layout\Layout
     */
    protected function createLayout()
    {
        $layout = new Schema\Layout\Layout;

        $this->layout($layout);

        return $layout;
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

        $keys = [ self::CREATE, self::READ, self::UPDATE, self::DELETE ];

        $values = array_map(function ($key) use ($permissions)
        {
            return $permissions->isPermitted($key, $this);

        }, $keys);

        return array_combine($keys, $values);
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
     * @return array
     */
    public function toArray()
    {
        $model = $this->getRepository()->newModel();

        return [
            'id' => $this->id,
            'primary_key' => $model->getKeyName(),
            'soft_deleting' => false,
            'defaults' => $this->getFields()->extract($model),
            'title' => $this->getTitle(),
            'order_by' => $this->defaultOrder,
            'view' => $this->view,

            'fields' => $this->getFields()->export(),
            'columns' => $this->getColumns()->export(),
            'filters' => $this->getFilters()->export(),
            'layout' => $this->getLayout()->isEmpty() ? null : $this->getLayout()->compile(),
        ];
    }

    /**
     * @param int $options
     *
     * @return string
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
        return app('cruddy.permissions');
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

}
