<?php

namespace Kalnoy\Cruddy;

use RuntimeException;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Kalnoy\Cruddy\Schema\SchemaInterface;
use Kalnoy\Cruddy\Schema\InstanceFactory;
use Kalnoy\Cruddy\Schema\InlineRelationInterface;
use Kalnoy\Cruddy\Service\Validation\ValidationException;
use Kalnoy\Cruddy\Repo\SearchProcessorInterface;
use Kalnoy\Cruddy\Repo\ChainedSearchProcessor;

/**
 * The entity class that is responsible for operations on model.
 *
 * @since 1.0.0
 */
class Entity implements Jsonable, Arrayable {

    /**
     * Cruddy environment.
     *
     * @var Environment
     */
    protected static $env;

    /**
     * The schema.
     *
     * @var Schema\SchemaInterface
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
     * The repository.
     *
     * @var Repo\RepositoryInterface
     */
    private $repo;

    /**
     * The validator.
     *
     * @var Service\Validation\ValidableInterface
     */
    private $validator;

    /**
     * The list of related entities.
     *
     * @var Schema\InlineRelationInterface[]
     */
    protected $related = [];

    /**
     * The list of all actions.
     *
     * @var array
     */
    protected static $actions = [ 'view', 'update', 'create', 'delete' ];

    /**
     * Init entity.
     *
     * @param Schema\SchemaInterface $schema
     */
    public function __construct(SchemaInterface $schema)
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
     * @param array|\Illuminate\Support\Collection $items
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
     * @return Repo\SearchProcessorInterface
     */
    protected function getSearchProcessor(array $options)
    {
        $processor = new ChainedSearchProcessor([ $this->getFields(), $this->getColumns() ]);

        if (isset($options['owner']) && static::$env !== null)
        {
            $field = static::$env->field($options['owner']);

            if ( ! $field instanceof SearchProcessorInterface)
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
     * Create new model and return extracted attributes.
     *
     * @param array $attributes
     *
     * @return array
     */
    public function create(array $attributes)
    {
        return $this->processAndSave(compact('attributes'));
    }

    /**
     * Update a model and return its extracted attributes.
     *
     * @param mixed $id
     * @param array $attributes
     *
     * @return array
     */
    public function update($id, array $attributes)
    {
        return $this->processAndSave(compact('id', 'attributes'));
    }

    /**
     * Save an item and all of its related items.
     *
     * @param array $data
     *
     * @return \Illuminate\Database\Eloquent\Model
     *
     * @throws ModelNotFoundException
     * @throws ModelNotSavedException
     */
    public function save(array $data)
    {
        $repo = $this->getRepository();

        extract($data);

        $action = $this->actionFromData($data);

        $eventResult = $this->fireEvent('saving', [ $action, $attributes ]);

        // If saving event returned non-null result, we'll throw a ModelNotSavedException
        // with that result.
        if ( ! is_null($eventResult))
        {
            throw new ModelNotSavedException($eventResult);
        }

        $data = $this->getFields()->cleanInput($action, $attributes);

        if (isset($extra)) $data += $extra;

        switch ($action)
        {
            case 'create': $model = $repo->create($data); break;
            case 'update': $model = $repo->update($id, $data); break;
        }

        $this->saveRelated($model, $related);

        $this->fireEvent('saved', [ $action, $model ], false);

        return $model;
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
    protected function fireEvent($event, array $payload, $halt = true)
    {
        if ( ! isset(static::$env)) return null;

        $event = "entity.{$event}: {$this->id}";

        return static::$env->getDispatcher()->fire($event, $payload, $halt);
    }

    /**
     * Perform validation and return processed data that can be then saved.
     *
     * @param array  $input
     *
     * @return array
     *
     * @throws Service\Validation\ValidationException
     */
    public function process(array $input)
    {
        extract($input);

        $action = $this->actionFromData($input);

        // We will process an input by a collection of fields to remove any
        // garbage
        $attributes = $this->getFields()->process($attributes);

        // Now we will validate those attributes
        $errors = $this->validate($action, $attributes);

        // And now time to process related items if any from raw input
        $related = $this->processRelated($action, $input['attributes'], $errors);

        if ( ! empty($errors)) throw new ValidationException($errors);

        return compact('id', 'attributes', 'related');
    }

    /**
     * Validate input.
     *
     * @param string $action
     * @param array  $attributes
     *
     * @return array
     */
    public function validate($action, array $attributes)
    {
        $labels    = $this->getFields()->validationLabels();
        $validator = $this->getValidator();

        if ( ! $validator->validFor($action, $attributes, $labels))
        {
            return $validator->errors();
        }

        return [];
    }

    /**
     * Get an action from the data.
     *
     * @param mixed $data
     *
     * @return string
     */
    public function actionFromData($data)
    {
        return empty($data['id']) ? 'create' : 'update';
    }

    /**
     * Process and validate related items.
     *
     * If corresponding input key doesn't exists, nothing will happen with the
     * related items (i.e. they will not be removed).
     *
     * @param string $action
     * @param array  $input
     * @param array  $errors
     *
     * @return array
     */
    protected function processRelated($action, array $input, array &$errors)
    {
        $data = [];

        foreach ($this->related as $id => $item)
        {
            if ( ! isset($input[$id])) continue;

            try
            {
                if ( ! $item->isDisabled($action))
                {
                    $data[$id] = $item->processInput($input[$id]);
                }
            }

            catch (ValidationException $e)
            {
                $errors[$id] = $e->getErrors();
            }
        }

        return $data;
    }

    /**
     * Save related items.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param array                               $data
     *
     * @return void
     */
    protected function saveRelated(Eloquent $model, array $data)
    {
        foreach ($data as $id => $item)
        {
            $this->related[$id]->save($model, $item);
        }
    }

    /**
     * Process, save and return extracted attributes of the model.
     *
     * If $input contains `id` attribute it is condisdered that model is exists
     * and Cruddy will try to update it; it will create a new model otherwise.
     *
     * @param array $input
     *
     * @return array
     */
    public function processAndSave($input)
    {
        return $this->extract($this->save($this->process($input)));
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
     * Add inline relation.
     *
     * @param Schema\InlineRelationInterface $relation
     *
     * @return $this
     */
    public function relates(InlineRelationInterface $relation)
    {
        $this->related[$relation->getId()] = $relation;

        return $this;
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
        $factory = static::$env->getFieldFactory();

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
        $factory = static::$env->getColumnFactory();

        $collection = new Schema\Columns\Collection;

        $schema = new InstanceFactory($factory, $this, $collection);

        $this->schema->columns($schema);

        return $this->ensurePrimaryColumn($collection);
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
     * @return Repo\RepositoryInterface
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
     * @return Service\Validation\ValidableInterface
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
     * @return Schema\SchemaInterface
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
     * Get list of related entities.
     *
     * @return array
     */
    public function getRelatedEntities()
    {
        return array_map(function ($item)
        {
            return $item->getReference();

        }, $this->related);
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

        return ucfirst(prettify_string($func($this->id)));
    }

    /**
     * Get permissions for every action.
     *
     * @return array
     */
    public function getPermissions()
    {
        $permissions = static::$env->getPermissions()->driver();

        $data = [];

        foreach (static::$actions as $action)
        {
            $data[$action] = $permissions->isPermitted($action, $this);
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        $fields = $this->getFields();

        $model = $this->getRepository()->newModel();

        return
        [
            'id' => $this->id,
            'soft_deleting' => false,
            'defaults' => $fields->extract($model),
            'title' => $this->getTitle(),

            'fields' => array_values($fields->toArray()),
            'columns' => array_values($this->getColumns()->toArray()),
            'related' => array_keys($this->related),

        ] + $this->schema->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

}