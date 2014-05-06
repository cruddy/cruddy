<?php

namespace Kalnoy\Cruddy;

use RuntimeException;
use Illuminate\Support\Collection;
use Illuminate\Support\Contracts\JsonableInterface;
use Illuminate\Support\Contracts\ArrayableInterface;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Kalnoy\Cruddy\ModelNotFoundException;
use Kalnoy\Cruddy\Schema\SchemaInterface;
use Kalnoy\Cruddy\Schema\InstanceFactory;
use Kalnoy\Cruddy\Schema\InlineRelationInterface;
use Kalnoy\Cruddy\Service\Validation\ValidationException;
use Kalnoy\Cruddy\Repo\SearchProcessorInterface;
use Kalnoy\Cruddy\Repo\ChainedSearchProcessor;

class Entity implements JsonableInterface, ArrayableInterface {

    /**
     * Cruddy environment.
     *
     * @var \Kalnoy\Cruddy\Environment
     */
    protected $env;

    /**
     * The schema.
     *
     * @var \Kalnoy\Cruddy\Schema\SchemaInterface
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
     * @var \Kalnoy\Cruddy\Schema\Fields\Collection
     */
    protected $fields;

    /**
     * The column list.
     *
     * @var \Kalnoy\Cruddy\Schema\Columns\Collection
     */
    protected $columns;

    /**
     * The repository.
     *
     * @var \Kalnoy\Cruddy\Repo\RepositoryInterface
     */
    protected $repo;

    /**
     * The validator.
     *
     * @var \Kalnoy\Cruddy\Service\Validation\ValidableInterface
     */
    protected $validator;

    /**
     * The list of related entities.
     *
     * @var \Kalnoy\Cruddy\Schema\InlineRelationInterface[]
     */
    protected $related = [];

    /**
     * Init entity.
     *
     * @param \Kalnoy\Cruddy\Environment            $env
     * @param \Kalnoy\Cruddy\Schema\SchemaInterface $schema
     * @param string                                $id
     */
    public function __construct(Environment $env, SchemaInterface $schema, $id)
    {
        $this->env = $env;
        $this->schema = $schema;
        $this->id = $id;
    }

    /**
     * Init entity.
     *
     * @return $this
     */
    public function init()
    {
        $this->repo = $this->schema->repository();

        $this->createFields();

        return $this;
    }

    /**
     * Find an item with given id.
     *
     * @param mixed $id
     *
     * @return array
     *
     * @throws \Kalnoy\Cruddy\ModelNotFoundException
     */
    public function find($id)
    {
        $model = $this->repo->find($id);

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

        $attributes = $this->fields->extract($model);
        $title = $this->schema->toString($model);

        return compact('attributes', 'title');
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
        $results = $this->repo->search($options, $this->getSearchProcessor($options));

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
     * @return \Kalnoy\Cruddy\Repo\SearchProcessorInterface
     */
    protected function getSearchProcessor(array $options)
    {
        $processor = new ChainedSearchProcessor([ $this->getFields(), $this->getColumns() ]);

        if (isset($options['owner']))
        {
            $field = $this->env->field($options['owner']);

            if ( ! $field instanceof SearchProcessorInterface)
            {
                throw new RuntimeException("The field [{$options['owner']}] is not a search processor.");
            }

            $processor->add($field);
        }

        return $processor;
    }

    /**
     * Convert all items to simple representation.
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
     * Convert item to a simple representation.
     *
     * @param array|\Illuminate\Database\Eloquent\Model $model
     *
     * @return array
     */
    public function simplify($model)
    {
        if (is_array($model) or $model instanceof Collection)
        {
            return $this->simplifyAll($model);
        }

        return ['id' => $model->getKey(), 'title' => $this->schema->toString($model)];
    }

    /**
     * Create a new model.
     *
     * @param array $input
     *
     * @return array
     *
     * @throws \Kalnoy\Cruddy\Service\Validation\ValidationException
     */
    public function create(array $input)
    {
        return $this->save('create', $input, function ($data)
        {
            return $this->repo->create($data);
        });
    }

    /**
     * Update a model.
     *
     * @param mixed $id
     * @param array  $input
     *
     * @return array
     * 
     * @throws \Kalnoy\Cruddy\Service\Validation\ValidationException
     * @throws \Kalnoy\Cruddy\ModelNotFoundException
     */
    public function update($id, array $input)
    {
        return $this->save('update', $input, function ($data) use ($id)
        {
            return $this->repo->update($id, $data);
        });
    }

    /**
     * Save an item and all of its related items.
     *
     * @param string $action
     * @param array   $input
     * @param \Closure $callback
     *
     * @return array
     */
    protected function save($action, array $input, \Closure $callback)
    {
        list($data, $relatedData) = $this->process($action, $input);

        $model = $callback($data);

        $this->saveRelated($model, $relatedData);

        return $this->extract($model);
    }

    /**
     * Perform validation and return processed data.
     *
     * @param string $action
     * @param array  $input
     *
     * @return array
     *
     * @throws \Kalnoy\Cruddy\Service\Validation\ValidationException
     */
    public function process($action, array $input)
    {
        $processed = $this->fields->process($input);
        $labels    = $this->fields->validationLabels();
        $validator = $this->getValidator();
        $errors = [];

        if ( ! $validator->validFor($action, $processed, $labels))
        {
            $errors = $validator->errors();
        }

        $relatedData = $this->processRelated($input, $errors);

        if ( ! empty($errors)) throw new ValidationException($errors);

        return [ $this->fields->cleanInput($action, $processed), $relatedData ];
    }

    /**
     * Validate related entities.
     *
     * @param array $input
     * @param array $errors
     *
     * @return array
     */
    protected function processRelated(array $input, array &$errors)
    {
        $data = [];

        foreach ($this->related as $id => $item)
        {
            if ( ! isset($input[$id])) continue;

            try
            {
                $data[$id] = $item->processInput($input[$id]);
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
    public function saveRelated(Eloquent $model, array $data)
    {
        foreach ($data as $id => $itemData)
        {
            $this->related[$id]->save($model, $itemData);
        }
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
        return $this->repo->delete($ids);
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
        if (false !== $pos = strpos($key, '::'))
        {
            if ($pos === 0) $key = substr($key, 2);

            return $this->env->translate($key, $default);
        }

        $line = $this->env->translate("{$this->id}.{$key}");

        if ($line !== null) return $line;

        return $this->env->translate("entities.{$key}", $default);
    }

    /**
     * Add inline relation.
     *
     * @param \Kalnoy\Cruddy\Schema\InlineRelationInterface $relation
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
     * @return \Kalnoy\Cruddy\Schema\Fields\Collection
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Create field collection.
     *
     * @return $this
     */
    protected function createFields()
    {
        $factory = $this->env->getFieldFactory();
        $collection = $this->fields = new Schema\Fields\Collection;

        $schema = new InstanceFactory($factory, $this, $collection);

        $this->schema->fields($schema);

        return $this;
    }

    /**
     * Get column collection.
     *
     * @return \Kalnoy\Cruddy\Schema\Columns\Collection
     */
    public function getColumns()
    {
        if ($this->columns === null) return $this->columns = $this->createColumns();

        return $this->columns;
    }

    /**
     * Create column collection.
     *
     * @return \Kalnoy\Cruddy\Schema\Columns\Collection
     */
    public function createColumns()
    {
        $factory = $this->env->getColumnFactory();
        $collection = new Schema\Columns\Collection;

        $schema = new InstanceFactory($factory, $this, $collection);

        $this->schema->columns($schema);

        $keyName = $this->repo->newModel()->getKeyName();

        if ( ! $collection->has($keyName))
        {
            $field = $this->fields->get($keyName);

            $collection->add(with(new Schema\Columns\Types\Proxy($this, $keyName, $field))->hide());
        }

        return $collection;
    }

    /**
     * Get the repository.
     *
     * @return \Kalnoy\Cruddy\Repo\RepositoryInterface
     */
    public function getRepository()
    {
        return $this->repo;
    }

    /**
     * Get the validator.
     *
     * @return \Kalnoy\Cruddy\Service\Validation\ValidableInterface
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
     * Get cruddy environment object.
     *
     * @return \Kalnoy\Cruddy\Environment
     */
    public function getEnv()
    {
        return $this->env;  
    }

    /**
     * Get entity's schema.
     *
     * @return \Kalnoy\Cruddy\Schema\SchemaInterface
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
     * @param singular|plural $plurality
     *
     * @return string
     */
    protected function makeTitle($plurality)
    {
        $result = $this->translate("title.{$plurality}");

        if ($result !== null) return $result;

        $func = "\str_{$plurality}";

        return ucfirst(prettify_string($func($this->id)));
    }

    /**
     * Get permissions as array.
     *
     * @return array
     */
    public function getPermissions()
    {
        $permissions = $this->env->getPermissions()->driver();
        $actions = ['view', 'update', 'create', 'delete'];
        $data = [];

        foreach ($actions as $action)
        {
            $data[$action] = $permissions->isPermitted($action, $this);
        }

        return $data;
    }

    /**
     * @inheritdoc
     *
     * @return array
     */
    public function toArray()
    {
        $this->getColumns();

        $model = $this->repo->newModel();

        return
        [
            'id' => $this->id,
            'soft_deleting' => $model->isSoftDeleting(),
            'defaults' => $this->fields->extract($model),
            'title' => $this->getTitle(),
            'permissions' => $this->getPermissions(),

            'fields' => array_values($this->fields->toArray()),
            'columns' => array_values($this->columns->toArray()),
            'related' => array_keys($this->related),


        ] + $this->schema->toArray();
    }

    /**
     * @inheritdoc
     *
     * @param int $options
     *
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }
}