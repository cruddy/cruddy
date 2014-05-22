<?php

namespace Kalnoy\Cruddy\Repo;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Pagination\Factory as PaginationFactory;
use Illuminate\Database\Eloquent\Relations\Relation;
use Kalnoy\Cruddy\ModelNotFoundException;
use Kalnoy\Cruddy\ModelNotSavedException;
use Kalnoy\Cruddy\Service\FileUploader;
use Exception;

/**
 * Base repository class.
 * 
 * @since 1.0.0
 */
abstract class BaseRepository implements RepositoryInterface {

    /**
     * Filesystem object.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $file;

    /**
     * Paginator factory.
     *
     * @var \Illuminate\Pagination\Factory
     */
    protected $paginator;

    /**
     * @var \Kalnoy\Cruddy\Service\FileUploader[]
     */
    protected $files = [];

    /**
     * The handlers that are called after the model is saved.
     * 
     * @var \Callable[]
     */
    protected $postSave = [];

    /**
     * The mode instance.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * Init repo.
     *
     * @param \Illuminate\Filesystem\Filesystem $file
     * @param \Illuminate\Pagination\Factory    $paginator
     */
    public function __construct(Filesystem $file = null, PaginationFactory $paginator = null)
    {
        $this->file = $file ?: \app('files');
        $this->paginator = $paginator ?: \app('paginator');
        $this->model = $this->newModel();
    }

    /**
     * Fill the model attributes.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param array                               $input
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function fill(Eloquent $model, array $input)
    {
        $this->uploadFiles($input);

        return $model->fill($this->cleanInput($input));
    }

    /**
     * Clean input from unwanted keys.
     * 
     * Default implementation just removes relations from the input.
     *
     * @param array $input
     *
     * @return array
     */
    protected function cleanInput(array $input)
    {
        foreach ($input as $key => $value)
        {
            if ($this->isRelation($key))
            {
                unset($input[$key]);
            }
        }

        return $input;
    }

    /**
     * Get new query.
     *
     * @param bool $excludeDeleted
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function newQuery($excludeDeleted = true)
    {
        return $this->model->newQuery($excludeDeleted);
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        $model = $this->newQuery(false)->find($id);

        if ($model === null) throw new ModelNotFoundException;

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    public function search(array $options, SearchProcessorInterface $processor = null)
    {
        $builder = $this->newQuery();
        $query = $builder->getQuery();

        if ($processor) $processor->search($builder, $options);

        $total = $query->getPaginationCount();

        $page = array_get($options, 'page', 1);
        $perPage = array_get($options, 'per_page', $this->getPerPage());

        $query->forPage($page, $perPage);

        return $this->paginator->make($builder->get()->all(), $total, $perPage);
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
     * {@inheritdoc}
     */
    public function create(array $input)
    {
        return $this->save($this->newModel(), $input);
    }

    /**
     * {@inheritdoc}
     */
    public function update($id, array $input)
    {
        $model = $this->find($id);

        return $this->save($model, $input);
    }

    /**
     * Save a model.
     *
     * @param \Illuminate\Database\Eloquent\Model $instance
     * @param array                               $input
     *
     * @return \Illuminate\Database\Eloquent\Model
     * 
     * @throws \Kalnoy\Cruddy\ModelNotSavedException
     */
    protected function save(Eloquent $instance, array $input)
    {
        $this->resetPostSaveCallbacks();

        $this->syncRelations($instance, $input);

        // We will temporarly unguard eloquent models to be sure that every
        // attribute is set.
        Eloquent::unguard();

        try
        {
            if (false === $this->fill($instance, $input)->save())
            {
                $className = get_class($instance);

                throw new ModelNotSavedException("Could not save an instance of [{$className}].");
            }
        }

        catch (Exception $e)
        {
            Eloquent::reguard();

            $this->cancelUploads();

            throw $e;
        }
        
        Eloquent::reguard();

        // Now when the instance is saved, we can run post-save events
        $this->firePostSaveCallbacks($instance);

        return $instance;
    }

    /**
     * Sync relationships.
     *
     * @param \Illuminate\Database\Eloquent\Model $instance
     * @param array                               $input
     *
     * @return $this
     */
    protected function syncRelations(Eloquent $instance, array $input)
    {
        foreach ($input as $key => $value)
        {
            if ($relation = $this->getRelation($instance, $key))
            {
                $this->syncRelation($instance, $relation, $key, $value);
            }
        }

        return $this;
    }

    /**
     * Get relationship query.
     *
     * @param \Illuminate\Database\Eloquent\Model $instance
     * @param string                              $key
     *
     * @return null|\Illuminate\Database\Eloquent\Relations\Relation
     */
    protected function getRelation(Eloquent $instance, $key)
    {
        if ( ! method_exists($instance, $key)) return null;

        $relation = $instance->$key();

        if ( ! $relation instanceof Relation) return null;

        return $relation;
    }

    /**
     * Get whether an attribute on model is a relation.
     *
     * @param \Illuminate\Database\Eloquent\Model $instance
     * @param string                              $key
     *
     * @return bool
     */
    protected function isRelation($key)
    {
        return $this->getRelation($this->model, $key) !== null;
    }

    /**
     * Sync one given relationship.
     *
     * @param \Illuminate\Database\Eloquent\Model              $instance
     * @param \Illuminate\Database\Eloquent\Relations\Relation $relation
     * @param string                                           $key
     * @param array                                            $data
     */
    protected function syncRelation(Eloquent $instance, Relation $relation, $key, $data)
    {
        $method = 'sync'.class_basename($relation);

        if (method_exists($this, $method))
        {
            $this->$method($instance, $key, $data);
        }
    }

    /**
     * Sync BelongsToMany relationship.
     *
     * @param \Illuminate\Database\Eloquent\Model $instance
     * @param string                              $key
     * @param array                               $data
     *
     * @return $this
     */
    protected function syncBelongsToMany(Eloquent $instance, $key, $data)
    {
        $data = is_array($data) ? $data : [];

        return $this->addPostSaveCallback(function ($instance) use ($key, $data)
        {
            $instance->$key()->sync($data);

            // We'll unset the relation since it might be outdated
            unset($instance->$key);
        });
    }

    /**
     * Sync MorphToMany relationship.
     *
     * @param \Illuminate\Database\Eloquent\Model $instance
     * @param string                              $key
     * @param array                               $data
     *
     * @return  $this
     */
    protected function syncMorphToMany(Eloquent $instance, $key, $data)
    {
        return $this->syncBelongsToMany($instance, $key, $data);
    }

    /**
     * Sync BelongsTo relationship.
     *
     * @param \Illuminate\Database\Eloquent\Model $instance
     * @param string                              $key
     * @param int                                 $data
     *
     * @return $this
     */
    protected function syncBelongsTo(Eloquent $instance, $key, $data)
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
     * @param \Illuminate\Database\Eloquent\Model $instance
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function firePostSaveCallbacks(Eloquent $instance)
    {
        foreach ($this->postSave as $callback)
        {
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
        $this->postSave = [];
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
        return $this->files[$attribute] = new FileUploader($this->file);
    }

    /**
     * Upload files if any.
     *
     * @param array $input
     *
     * @return $this
     */
    protected function uploadFiles(array &$input)
    {
        foreach ($this->files as $attr => $file)
        {
            if (isset($input[$attr]))
            {
                $input[$attr] = $file->upload($input[$attr]);
            }
        }

        return $this;
    }

    /**
     * Cancel all uploads.
     *
     * @return $this
     */
    protected function cancelUploads()
    {
        foreach ($this->files as $uploader)
        {
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

        $key = $this->model->getKeyName();

        $count = 0;

        foreach ($this->newQuery()->whereIn($key, $ids)->get() as $item)
        {
            if ($item->delete()) $count++;
        }

        return $count;
    }

}