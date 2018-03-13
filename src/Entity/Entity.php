<?php

namespace Kalnoy\Cruddy\Entity;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;
use Kalnoy\Cruddy\Contracts;
use Kalnoy\Cruddy\Entity\DataSource\ColumnsCollection;
use Kalnoy\Cruddy\Entity\DataSource\DataSource;
use Kalnoy\Cruddy\Helpers;
use Kalnoy\Cruddy\Schema;

/**
 * The entity class that is responsible for operations on model.
 *
 * @package \Kalnoy\Cruddy\Entity
 */
abstract class Entity
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
     * Special attribute name for the model key.
     */
    const ID_PROPERTY = '__id';

    /**
     * @var Dispatcher
     */
    protected static $dispatcher;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var Repository
     */
    protected $entities;

    /**
     * @var Actions\Collection
     */
    private $actions;

    /**
     * @var array|Form[]
     */
    private $forms = [];

    /**
     * @var DataSource
     */
    private $mainDataSource;

    /**
     * @var DataSource
     */
    private $simpleDataSource;

    /**
     * The list of relations that will be eagerly loaded.
     *
     * @var array
     */
    public $eagerLoads = [ ];

    /**
     * The model class name.
     *
     * @var string
     */
    public $model;

    /**
     * The array of default attributes.
     *
     * @var array
     */
    public $defaults = [ ];

    /**
     * The attribute that is used to convert model to a string.
     *
     * @var string
     */
    public $titleAttribute;

    /**
     * The id of column that will be ordered by default.
     *
     * @var string
     */
    public $defaultOrderBy;

    /**
     * The number of items per page.
     *
     * Set this value to override default model's value.
     *
     * @var int
     */
    public $perPage;

    /**
     * The list of searchable attributes.
     *
     * @var array
     */
    public $searchable;

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
     * Specify fields on form.
     *
     * @param \Kalnoy\Cruddy\Entity\FieldsCollection $form
     */
    public function fields($form)
    {
        //
    }

    /**
     * Get custom validation rules.
     *
     * @param mixed $modelKey
     *
     * @return array
     */
    public function rules($modelKey)
    {
        return [];
    }

    /**
     * Specify layout of form.
     *
     * @param \Kalnoy\Cruddy\Form\Layout\Layout $layout
     */
    public function layout($layout)
    {
        //
    }

    /**
     * Specify the columns.
     *
     * @param \Kalnoy\Cruddy\Entity\DataSource\ColumnsCollection $columns
     */
    public function columns($columns)
    {
        //
    }

    /**
     * Specify filters.
     *
     * @param \Kalnoy\Cruddy\Entity\DataSource\FiltersCollection $filters
     */
    public function filters($filters)
    {
        //
    }

    /**
     * Set up actions.
     *
     * @param Actions\Collection $actions
     */
    public function actions($actions)
    {
        //
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

        return $this->formForCreate()->getFields()->modelData($model);
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
    public function toHTML($model)
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
    public function toUrl($model)
    {
        return null;
    }

    /**
     * Get a form for specified model.
     *
     * @param Model|string|null $model
     *
     * @return \Kalnoy\Cruddy\Entity\ModelForm
     */
    public function form($model)
    {
        if (is_string($model)) {
            $model = $this->find($model);
        } elseif (is_null($model)) {
            $model = $this->newModel();
        }

        $type = $model->exists ? self::UPDATE : self::CREATE;

        return new ModelForm($this->baseForm($type), $model);
    }

    /**
     * Get a base form of specified type.
     *
     * @param string $type
     *
     * @return \Kalnoy\Cruddy\Entity\Form
     */
    public function baseForm($type)
    {
        if (isset($this->forms[$type])) {
            return $this->forms[$type];
        }

        $form = new Form($this, $type);

        $form->fields([ $this, 'fields' ])
             ->layout([ $this, 'layout' ]);

        return $this->forms[$type] = $form;
    }

    /**
     * Get a base form for creating.
     *
     * @return \Kalnoy\Cruddy\Entity\Form
     */
    public function formForCreate()
    {
        return $this->baseForm(self::CREATE);
    }

    /**
     * Get a base form for updating.
     *
     * @return \Kalnoy\Cruddy\Entity\Form
     */
    public function formForUpdate()
    {
        return $this->baseForm(self::UPDATE);
    }

    /**
     * @return Builder
     */
    public function newQuery()
    {
        return $this->newModel()->newQuery();
    }

    /**
     * @return Builder
     */
    public function newIndexQuery()
    {
        return $this->newQuery();
    }

    /**
     * Find a model with given id.
     *
     * @param mixed $id
     *
     * @return Model
     */
    public function find($id)
    {
        return $this->newQuery()->findOrFail($id);
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
        return $this->getMainDataSource()->get($options);
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
        if ( ! isset(static::$dispatcher)) {
            return null;
        }

        $event = "entity.{$event}: {$this->id}";

        return static::$dispatcher->fire($event, $payload, $halt);
    }

    /**
     * @param Repository $entities
     *
     * @return $this
     */
    public function setEntitiesRepository(Repository $entities)
    {
        $this->entities = $entities;

        return $this;
    }

    /**
     * @param Model $model
     *
     * @return array
     */
    public function modelMeta(Model $model)
    {
        return [
            'title' => $this->toString($model),
            'links' => $this->links($model),
            'actions' => $this->getActions()->export($model),
        ];
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
        $ids = is_array($ids) ? $ids : func_get_args();

        return $this->newQuery()->findMany($ids)->each(function (Model $model) {
            $model->delete();
        })->count();
    }

    /**
     * Translate line.
     *
     * @param string $key
     * @param string $default
     *
     * @return string
     */
    public function translate($key, $default = null)
    {
        if ($this->id) {
            $line = Helpers::translate("{$this->id}.{$key}");

            if ($line !== null) {
                return $line;
            }
        }

        return Helpers::translate("entities.{$key}", $default);
    }

    /**
     * @return Repository
     */
    public function getEntitiesRepository()
    {
        return $this->entities;
    }

    /**
     * @return Actions\Collection
     */
    public function getActions()
    {
        if ($this->actions === null) {
            return $this->actions = $this->buildActions();
        }

        return $this->actions;
    }

    /**
     * @return Actions\Collection
     */
    protected function buildActions()
    {
        $collection = new Actions\Collection;

        $this->actions($collection);

        return $collection;
    }

    /**
     * @return DataSource
     */
    public function getMainDataSource()
    {
        if (is_null($this->mainDataSource)) {
            return $this->mainDataSource = $this->buildMainDataSource();
        }

        return $this->mainDataSource;
    }

    /**
     * @return DataSource
     */
    protected function buildMainDataSource()
    {
        return (new DataSource($this))
            ->columns([ $this, 'columns' ])
            ->filters([ $this, 'filters' ])
            ->paginateBy($this->perPage)
            ->eagerLoads($this->eagerLoads)
            ->searchable($this->searchable)
            ->orderBy($this->defaultOrderBy);
    }

    /**
     * @return DataSource
     */
    public function getSimpleDataSource()
    {
        if (is_null($this->simpleDataSource)) {
            return $this->simpleDataSource = $this->buildSimpleDataSource();
        }

        return $this->simpleDataSource;
    }

    /**
     * @return DataSource
     */
    protected function buildSimpleDataSource()
    {
        return (new DataSource($this))
            ->eagerLoads($this->eagerLoads)
            ->paginateBy($this->perPage)
            ->searchable($this->searchable)
            ->orderBy($this->defaultOrderBy)
            ->columns(function (ColumnsCollection $columns) {
                $columns->attr('id')
                        ->modelAttribute($this->newModel()->getKeyName());

                $columns->compute('body', function ($model) {
                    return $this->toHTML($model);
                });
            });
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
        $result = Helpers::translate("{$this->id}.title.{$plurality}");

        if ($result !== null) return $result;

        $result = Helpers::translate("entities.titles.{$this->id}.{$plurality}");

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
     * @return Contracts\Permissions
     */
    protected function getPermissionsDriver()
    {
        return app('cruddy.permissions');
    }

    /**
     * @return Model
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
        return [
            'id' => $this->id,
            'model_class' => $this->getModelClass(),
            'controller_class' => $this->getControllerClass(),
            'title' => $this->getTitle(),

            'order_by' => $this->defaultOrderBy,

            'defaults' => $this->defaultAttributes(),
            'create_form' => $this->formForCreate()->getConfig(),
            'update_form' => $this->formForUpdate()->getConfig(),

            'data_source' => $this->getMainDataSource()->getConfig(),
        ];
    }

    /**
     * @return \Kalnoy\Cruddy\Entity\FieldsFactory
     */
    protected function getFiltersFactory()
    {
        return app('cruddy.filters');
    }

    /**
     * @return \Kalnoy\Cruddy\Entity\DataSource\ColumnsFactory
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

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

}
