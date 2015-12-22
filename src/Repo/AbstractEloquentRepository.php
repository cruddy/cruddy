<?php

namespace Kalnoy\Cruddy\Repo;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Kalnoy\Cruddy\Contracts\Repository;
use Kalnoy\Cruddy\Contracts\SearchProcessor;
use Kalnoy\Cruddy\ModelNotFoundException;
use Kalnoy\Cruddy\ModelNotSavedException;
use Kalnoy\Cruddy\Service\FileUploader;
use Exception;

/**
 * Base repository class.
 *
 * @since 1.0.0
 */
abstract class AbstractEloquentRepository implements Repository
{
    /**
     * @var FileUploader[]
     */
    protected $files = [ ];

    /**
     * The handlers that are called after the model is saved.
     *
     * @var \Callable[]
     */
    protected $postSave = [ ];

    /**
     * The mode instance.
     *
     * @var Model
     */
    protected $model;

    /**
     * Init the repo.
     */
    public function __construct()
    {
        $this->model = $this->newModel();
    }

    /**
     * @return Model
     */
    abstract public function newModel();

    /**
     * Fill the model attributes.
     *
     * @param Model $model
     * @param array $input
     *
     * @return Model
     */
    protected function fill(Model $model, array $input)
    {
        foreach ($input as $key => $value) {
            if ($this->isFillable($key)) {
                $model->setAttribute($key, $this->transform($key, $value));
            }
        }

        return $model;
    }

    /**
     * Transform given attribute before setting it on model.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return mixed
     */
    protected function transform($key, $value)
    {
        if ($this->isFile($key)) {
            return $this->upload($key, $value);
        }

        return $value;
    }

    /**
     * Get whether the attribute is fillable.
     *
     * @param string $key
     *
     * @return bool
     */
    protected function isFillable($key)
    {
        return ! $this->isRelation($key);
    }

    /**
     * Get new query.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function newQuery()
    {
        return $this->model->newQueryWithoutScopes();
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        $model = $this->newQuery()->find($id);

        if ($model === null) {
            throw new ModelNotFoundException;
        }

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    public function search(array $options, SearchProcessor $processor = null)
    {
        $builder = $this->newQuery();

        if ($processor) {
            $processor->constraintBuilder($builder, $options);
        }

        return $this->paginate($builder, $options);
    }

    /**
     * Get per page items count.
     *
     * @return int
     */
    public function getPerPage()
    {
        return $this->model->getPerPage();
    }

    /**
     * Save a model.
     *
     * @param Model $instance
     * @param array $input
     * @param callable $extra
     *
     * @throws Exception
     * @return Model
     */
    public function save(Model $instance, array $input, Closure $extra = null)
    {
        $this->resetPostSaveCallbacks();

        $this->setRelations($instance, $input);

        try {
            $this->fill($instance, $input);

            if ($extra) $extra($instance);

            if (false === $instance->save()) {
                $className = get_class($instance);

                throw new ModelNotSavedException("Could not save an instance of [{$className}].");
            }
        }

        catch (Exception $e) {
            $this->cancelUploads();

            throw $e;
        }

        // Now when the instance is saved, we can run post-save events that are
        // defined by relations
        $this->firePostSaveCallbacks($instance);

        return $instance;
    }

    /**
     * Sync relationships.
     *
     * @param Model $instance
     * @param array $input
     *
     * @return $this
     */
    protected function setRelations(Model $instance, array $input)
    {
        foreach ($input as $key => $value) {
            if ($relation = $this->getRelationObject($instance, $key)) {
                $this->setRelation($instance, $relation, $key, $value);
            }
        }

        return $this;
    }

    /**
     * Get relationship query.
     *
     * @param Model $instance
     * @param string $key
     *
     * @return null|\Illuminate\Database\Eloquent\Relations\Relation
     */
    protected function getRelationObject(Model $instance, $key)
    {
        if ( ! method_exists($instance, $key)) return null;

        $relation = $instance->$key();

        if ( ! $relation instanceof Relation) return null;

        return $relation;
    }

    /**
     * Get whether an attribute on model is a relation.
     *
     * @param string $key
     *
     * @return bool
     */
    protected function isRelation($key)
    {
        return $this->getRelationObject($this->model, $key) !== null;
    }

    /**
     * Sync one given relationship.
     *
     * @param Model $instance
     * @param \Illuminate\Database\\Relations\Relation $relation
     * @param string $key
     * @param array $data
     */
    protected function setRelation(Model $instance, Relation $relation, $key,
                                   $data
    ) {
        $method = 'set'.class_basename($relation);

        if (method_exists($this, $method)) {
            $this->$method($instance, $key, $data);
        }
    }

    /**
     * Sync BelongsToMany relationship.
     *
     * @param Model $instance
     * @param string $key
     * @param array $data
     *
     * @return $this
     */
    protected function setBelongsToMany(Model $instance, $key, $data)
    {
        $data = is_array($data) ? $data : [ ];

        return $this->addPostSaveCallback(function ($instance) use ($key, $data
        ) {
            $instance->$key()->sync($data);

            // We'll unset the relation since it might be outdated
            unset($instance->$key);
        });
    }

    /**
     * Sync MorphToMany relationship.
     *
     * @param Model $instance
     * @param string $key
     * @param array $data
     *
     * @return  $this
     */
    protected function setMorphToMany(Model $instance, $key, $data)
    {
        return $this->setBelongsToMany($instance, $key, $data);
    }

    /**
     * Sync BelongsTo relationship.
     *
     * @param Model $instance
     * @param string $key
     * @param int $data
     *
     * @return $this
     */
    protected function setBelongsTo(Model $instance, $key, $data)
    {
        $foreignKey = $instance->$key()->getForeignKey();

        $instance->setAttribute($foreignKey, $data ?: null);

        unset($instance->$key);

        return $this;
    }

    /**
     * Add a callback that will be fired after model is saved.
     *
     * @param callable $callback
     *
     * @return $this
     */
    protected function addPostSaveCallback(Callable $callback)
    {
        $this->postSave[] = $callback;

        return $this;
    }

    /**
     * Fire post save callbacks.
     *
     * @param Model $instance
     *
     * @return Model
     */
    protected function firePostSaveCallbacks(Model $instance)
    {
        foreach ($this->postSave as $callback) {
            $callback($instance);
        }
    }

    /**
     * Reset post save events.
     *
     * @return void
     */
    protected function resetPostSaveCallbacks()
    {
        $this->postSave = [ ];
    }

    /**
     * Tell repository that specified attribute is a file.
     *
     * @param string $attribute
     *
     * @return \Kalnoy\Cruddy\Service\FileUploader
     */
    public function uploads($attribute)
    {
        return $this->files[$attribute] = app(FileUploader::class);
    }

    /**
     * Upload a file.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return mixed
     */
    protected function upload($key, $value)
    {
        return $this->files[$key]->upload($value);
    }

    /**
     * Get whether specified key is a file that needs to be uploaded.
     *
     * @param string $key
     *
     * @return bool
     */
    protected function isFile($key)
    {
        return isset($this->files[$key]);
    }

    /**
     * Cancel all uploads.
     *
     * @return $this
     */
    protected function cancelUploads()
    {
        foreach ($this->files as $uploader) {
            $uploader->cancel();
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($ids)
    {
        $ids = is_array($ids) ? $ids : func_get_args();

        if (empty($ids)) return 0;

        $key = $this->model->getKeyName();

        $count = 0;

        foreach ($this->newQuery()->whereIn($key, $ids)->get() as $item) {
            if ($item->delete()) $count++;
        }

        return $count;
    }

    /**
     * @param $builder
     * @param array $options
     *
     * @return array
     */
    protected function paginate(Builder $builder, array $options)
    {
        $query = $builder->getQuery();
        $total = $query->getCountForPagination();

        $perPage = array_get($options, 'per_page', $this->getPerPage());
        $lastPage = (int)ceil($total / $perPage);
        $page = max(1, min($lastPage, (int)array_get($options, 'page', 1)));

        $query->forPage($page, $perPage);

        /** @var \Illuminate\Support\Collection $items */
        $items = $builder->get();

        $from = ($page - 1) * $perPage + 1;
        $to = $from + $items->count() - 1;

        return compact('total', 'page', 'perPage', 'lastPage', 'from', 'to', 'items');
    }

    /**
     * @return void
     */
    public function startTransaction()
    {
        $this->newModel()->getConnection()->beginTransaction();
    }

    /**
     * @return void
     */
    public function commitTransaction()
    {
        $this->newModel()->getConnection()->commit();
    }

}