<?php

namespace Kalnoy\Cruddy;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Fluent;
use Kalnoy\Cruddy\Contracts\KeywordsFilter;
use Kalnoy\Cruddy\Contracts\SearchProcessor;
use Kalnoy\Cruddy\Repo\BasicEloquentRepository;
use Kalnoy\Cruddy\Schema\AttributesCollection;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Kalnoy\Cruddy\Repo\ChainedSearchProcessor;
use Kalnoy\Cruddy\Contracts\Field;
use Kalnoy\Cruddy\Schema\Columns\Types\Proxy;
use Kalnoy\Cruddy\Schema\Fields\BaseRelation;
use Kalnoy\Cruddy\Service\Validation\FluentValidator;

/**
 * The entity class that is responsible for operations on model.
 *
 * @since 1.0.0
 */
abstract class Entity extends BaseForm implements SearchProcessor
{
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
     * @var Schema\Actions\Collection
     */
    private $actions;

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
     * The list of relations that will be eagerly loaded.
     *
     * @var array
     */
    protected $eagerLoads = [ ];

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
    protected $defaults = [ ];

    /**
     * The list of complex filters.
     *
     * @var array
     */
    protected $filters = [ ];

    /**
     * The list of searchable fields.
     *
     * @var array
     */
    protected $searchable;

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
        foreach ($this->filters as $field) {
            $schema->usingField($field);
        }
    }

    /**
     * Get default attributes.
     *
     * @return array
     */
    protected function defaultAttributes()
    {
        $model = $this->newModel();

        $model->setRawAttributes($this->defaults);

        return $this->getFields()->getModelData($model);
    }

    /**
     * Set up actions.
     *
     * @param Schema\Actions\Collection $actions
     */
    protected function actions($actions) {}

    /**
     * @param FluentValidator $validate
     */
    protected function rules($validate) {}

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
     * Get links for model that will be displayed in data table and form.
     *
     * @param Model $model
     *
     * @return array
     */
    protected function links($model)
    {
        $result = [ ];

        if ($value = $this->toUrl($model)) {
            $label = app('cruddy.lang')->translate('cruddy::js.view_external');

            $result[$label] = $value;
        }

        return $result;
    }

    /**
     * Convert the model to string.
     *
     * This method should return only text without any HTML tags.
     * Use `toHtml` if you need to specify markup.
     *
     * @param Model $model
     *
     * @return string
     */
    public function toString($model)
    {
        return $this->titleAttribute
            ? $model->getAttribute($this->titleAttribute)
            : $model->getKey();
    }

    /**
     * Get HTML representation for the simplified view (i.e. drop down).
     *
     * @param Model $model
     *
     * @return string
     */
    protected function toHTML($model)
    {
        return $this->toString($model);
    }

    /**
     * Get the url to the model on main site.
     *
     * @param Model $model
     *
     * @return string
     */
    public function toUrl($model) {}

    /**
     * Find an item with given id.
     *
     * @param mixed $id
     *
     * @return mixed
     */
    public function find($id)
    {
        return $this->getRepository()->find($id);
    }

    /**
     * @param $model
     * @param array $input
     *
     * @return $this
     */
    public function save($model, array $input)
    {
        $fields = $this->getFields();
        $repo = $this->getRepository();

        $fields->fillModel(Field::MODE_BEFORE_SAVE, $model, $input);

        $repo->startTransaction();

        if (false === $this->fireEvent('saving', [ $model ]) ||
            ! $repo->save($model)
        ) {
            throw new ModelNotSavedException;
        }

        $fields->fillModel(Field::MODE_AFTER_SAVE, $model, $input);

        $this->fireEvent('saved', [ $model ], false);

        $repo->commitTransaction();

        return $this;
    }

    /**
     * @param string $action
     * @param array $input
     *
     * @return array
     */
    public function validate($action, array $input)
    {
        $fields = $this->getFields();

        $result = [];

        $validator = $this->getValidator();
        $labels = $fields->getValidationLabels();

        $this->getFields()->parseInput($input);

        if ( ! $validator->validFor($action, $input, $labels)) {
            $result = $validator->errors();
        }

        return array_merge_recursive($result, $fields->validate($input));
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
    public function index(array $options)
    {
        $results = $this->getRepository()
                        ->search($options, $this->getSearchProcessor($options));

        if (Arr::get($options, 'simple')) {
            $results['items'] = $this->simplifyModelList($results['items']);
        } else {
            $results['items'] = $this->getModelListData($results['items'],
                                                        $this->getColumns());
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
        $processor = new ChainedSearchProcessor([ $this,
                                                  $this->getFields(),
                                                  $this->getColumns(),
                                                  $this->getFilters() ]);

        if (isset($options['owner'])) {
            $field = $this->entities->field($options['owner']);

            if ( ! $field instanceof SearchProcessor) {
                throw new \RuntimeException("Cannot use field [{$options['owner']}] as owner.");
            }

            $processor->append($field);
        }

        return $processor;
    }

    /**
     * @inheritDoc
     */
    public function constraintBuilder(Builder $builder, array $input)
    {
        $simple = Arr::get($input, 'simple', false);

        if ($relations = $this->eagerLoads(null, ! $simple)) {
            $builder->with($relations);
        }
    }

    /**
     * Get a list of relations that should be eagerly loaded.
     *
     * @param string $scope
     * @param bool $deep
     *
     * @return array
     */
    public function eagerLoads($scope = null, $deep = false)
    {
        $result = $this->eagerLoads;

        if ($deep) {
            foreach ($this->getColumns() as $column) {
                if ( ! $column instanceof Proxy) continue;

                $field = $column->getField();

                if ( ! $field instanceof BaseRelation) {
                    continue;
                }

                $result = array_merge($result, $field->eagerLoads());
            }

            $result = array_unique($result);
        }

        if (is_null($scope)) return $result;

        return array_map(function ($item) use ($scope) {
            return $scope.'.'.$item;
        }, $result);
    }

    /**
     * Fire entity event.
     *
     * @param string $event
     * @param array $payload
     * @param bool $halt
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
     * @inheritDoc
     *
     * @param Model $model
     */
    public function getModelAttributeValue($model, $attribute)
    {
        return $model->getAttributeValue($attribute);
    }

    /**
     * @inheritDoc
     *
     * @param Model $model
     */
    public function setModelAttributeValue($model, $value, $attribute)
    {
        $model->setAttribute($attribute, $value);
    }

    /**
     * Extract model fields.
     *
     * @param mixed $model
     * @param AttributesCollection $collection
     *
     * @return array
     */
    public function getModelData($model, AttributesCollection $collection = null)
    {
        if ( ! $model) return null;

        if (is_array($model) || $model instanceof Collection) {
            return $this->getModelListData($model, $collection);
        }

        $attributes = parent::getModelData($model, $collection);
        $meta = $this->modelMeta($model);

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
    public function getModelListData($items, AttributesCollection $collection = null)
    {
        if ($items instanceof Collection) {
            $items = $items->all();
        }

        return array_map(function ($model) use ($collection) {
            return $this->getModelData($model, $collection);
        }, $items);
    }

    /**
     * Convert item to a simple representation which is used by an entity dropdown.
     *
     * @param array|Model $model
     *
     * @return array
     */
    public function simplifyModel($model)
    {
        if ( ! $model) return null;

        if (is_array($model) || $model instanceof Collection) {
            return $this->simplifyModelList($model);
        }

        return $this->simplifiedModel($model);
    }

    /**
     * Convert all items to simple representation which is used by an entity dropdown.
     *
     * @param array $list
     *
     * @return array
     */
    public function simplifyModelList($list)
    {
        if ($list instanceof Collection) {
            $list = $list->all();
        }

        return array_map([ $this, 'simplifyModel' ], $list);
    }

    /**
     * Get simplified representation for the model.
     *
     * Used by drop downs, grid.
     *
     * @param Model $model
     *
     * @return array
     */
    public function simplifiedModel(Model $model)
    {
        return [
            'id' => $model->getKey(),
            'body' => $this->toHTML($model),
        ];
    }

    /**
     * @param Model $model
     *
     * @return array
     */
    public function modelMeta(Model $model)
    {
        return [
            'id' => $model->getKey(),
            'title' => $this->toString($model),
            'links' => $this->links($model),
            'actions' => $this->getActions()->export($model),
            'simplified' => $this->simplifiedModel($model),
        ];
    }

    /**
     * Get a list of relations of the model.
     *
     * @param string $scope
     *
     * @return array
     */
    public function relations($scope = null)
    {
        $result = [ ];

        foreach ($this->getFields() as $field) {
            if ($field instanceof BaseRelation) {
                $result = array_merge($result, $field->relations($scope));
            }
        }

        return $result;
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
     * @inheritDoc
     */
    protected function createFields()
    {
        $collection = parent::createFields();

        $collection->setSearchableFields($this->searchable);

        return $collection;
    }

    /**
     * @return Schema\Actions\Collection
     */
    public function getActions()
    {
        if ($this->actions === null) {
            return $this->actions = $this->createActions();
        }

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
        $collection = new Schema\Columns\Collection($this, $this->defaultOrder);

        $schema = new Schema\Columns\InstanceFactory($this->getColumnsFactory(),
                                                     $collection);

        $this->columns($schema);

        return $collection;
    }

    /**
     * @return Schema\Filters\Collection
     */
    public function getFilters()
    {
        if ($this->filtersCollection === null) {
            return $this->filtersCollection = $this->createFilters();
        }

        return $this->filtersCollection;
    }

    /**
     * @return Schema\Filters\Collection
     */
    public function createFilters()
    {
        $collection = new Schema\Filters\Collection($this);

        $schema = new Schema\Filters\InstanceFactory($this->getFiltersFactory(),
                                                     $collection);

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
        if ($this->repo === null) {
            return $this->repo = $this->createRepository();
        }

        return $this->repo;
    }

    /**
     * @return BasicEloquentRepository
     */
    public function createRepository()
    {
        $repo = new BasicEloquentRepository($this->model);

        $repo->perPage = $this->perPage;

        return $repo;
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

        $values = array_map(function ($key) use ($permissions) {
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
     * @return mixed
     */
    public function newModel()
    {
        return new $this->model;
    }

    /**
     * @return string
     */
    protected function getModelClass()
    {
        return 'Cruddy.Entity.Entity';
    }

    /**
     * @return string
     */
    protected function getControllerClass()
    {
        return 'Cruddy.Entity.Page';
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $model = $this->newModel();

        return [
            'defaults' => $this->defaultAttributes(),

            'primary_key' => $model->getKeyName(),
            'soft_deleting' => false,
            'order_by' => $this->defaultOrder,

            'columns' => $this->getColumns()->toArray(),
            'filters' => $this->getFilters()->toArray(),

        ] + parent::toArray();
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
     * @param $model
     *
     * @return string
     */
    public function getActionFromModel($model)
    {
        return $model->exists ? self::UPDATE : self::CREATE;
    }

}
