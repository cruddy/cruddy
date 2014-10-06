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
use RuntimeException;

/**
 * Base repository class.
 *
 * @since 1.0.0
 */
abstract class BaseRepository implements RepositoryInterface {

    /**
     * Filesystem object.
     *
     * @var Filesystem
     */
    protected static $file;

    /**
     * Paginator factory.
     *
     * @var PaginationFactory
     */
    protected static $paginator;

    /**
     * @var FileUploader[]
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
     * @var Eloquent
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
     * Fill the model attributes.
     *
     * @param Eloquent $model
     * @param array    $input
     *
     * @return Eloquent
     */
    protected function fill(Eloquent $model, array $input)
    {
        foreach ($input as $key => $value)
        {
            if ($this->isFillable($key))
            {
                $model->setAttribute($key, $this->transform($key, $value));
            }
        }

        return $model;
    }

    /**
     * Transform given attribute before setting it on model.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return mixed
     */
    protected function transform($key, $value)
    {
        if ($this->isFile($key)) return $this->upload($key, $value);

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
        if ( ! self::$paginator)
        {
            throw new RuntimeException("Cannot search items because paginator is not set.");
        }

        $builder = $this->newQuery();
        $query = $builder->getQuery();

        if ($processor) $processor->constraintBuilder($builder, $options);

        $total = $query->getPaginationCount();

        $page = array_get($options, 'page', 1);
        $perPage = array_get($options, 'per_page', $this->getPerPage());

        $query->forPage($page, $perPage);

        return self::$paginator->make($builder->get()->all(), $total, $perPage);
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
     * @param Eloquent $instance
     * @param array    $input
     *
     * @throws Exception
     *
     * @return Eloquent
     */
    protected function save(Eloquent $instance, array $input)
    {
        $this->resetPostSaveCallbacks();

        $this->syncRelations($instance, $input);

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
     * @param Eloquent $instance
     * @param array    $input
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
     * @param Eloquent $instance
     * @param string   $key
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
     * @param string $key
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
     * @param Eloquent                                         $instance
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
     * @param Eloquent $instance
     * @param string   $key
     * @param array    $data
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
     * @param Eloquent $instance
     * @param string   $key
     * @param array    $data
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
     * @param Eloquent $instance
     * @param string   $key
     * @param int      $data
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
     * @param Eloquent $instance
     *
     * @return Eloquent
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
        return $this->files[$attribute] = new FileUploader(self::$file);
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

    /**
     * Set pagination factory.
     *
     * @param PaginationFactory $factory
     */
    public static function setPaginationFactory(PaginationFactory $factory)
    {
        self::$paginator = $factory;
    }

    /**
     * Set filesystem object.
     *
     * @param Filesystem $files
     */
    public static function setFiles(Filesystem $files)
    {
        self::$file = $files;
    }

}