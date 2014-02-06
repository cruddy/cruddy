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
     * @param \Illuminate\Database\Eloquent\Model   $model
     * @param array                                 $input
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function fill(Eloquent $model, array $input)
    {
        return $model->fill($input);
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
     * @inheritdoc
     *
     * @param int $id
     *
     * @return \Illuminate\Database\Eloquent\Model
     *
     * @throws \Kalnoy\Cruddy\ModelNotFoundException
     */
    public function find($id)
    {
        $model = $this->newQuery(false)->find($id);

        if ($model === null) throw new ModelNotFoundException;

        return $model;
    }

    /**
     * @inheritdoc
     *
     * @param array $options
     *
     * @return \Illuminate\Pagination\Paginator
     */
    public function search(array $options, SearchProcessorInterface $processor = null)
    {
        $builder = $this->newQuery();
        $query = $builder->getQuery();

        if ($processor) $processor->search($builder, $options);

        $total = $query->getPaginationCount();

        $page = array_get($options, 'page', 1);
        $perPage = array_get($options, 'per_page', $this->model->getPerPage());

        $query->forPage($page, $perPage);

        return $this->paginator->make($builder->get()->all(), $total, $perPage);
    }

    /**
     * @inheritdoc
     *
     * @param array $input
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $input)
    {
        return $this->save($this->newModel(), $input);
    }

    /**
     * @inheritdoc
     *
     * @param int    $id
     * @param array  $input
     *
     * @return \Illuminate\Database\Eloquent\Model
     *
     * @throws \Kalnoy\Cruddy\ModelNotFoundException
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
     * @throws \Exception
     * @throws \Kalnoy\Cruddy\ModelNotSavedException
     */
    protected function save(Eloquent $instance, array $input)
    {
        $this->sync($instance, $input)->upload($input);
        
        // This is needed to ensure that all needed attributes are set even for
        // related models. Don't worry, attributes are cleaned.
        Eloquent::unguard();

        try
        {

            if (false === $this->fill($instance, $input)->save())
            {
                throw new ModelNotSavedException("Could not save instance of {get_class($instance)}.");
            }
        }

        catch (Exception $e)
        {
            Eloquent::reguard();

            $this->cancelUploads();

            throw $e;
        }

        Eloquent::reguard();

        return $this->fireAfterSave($instance);
    }

    /**
     * Sync relationships.
     *
     * @param Eloquent $instance
     * @param array    $input
     *
     * @return $this
     */
    protected function sync(Eloquent $instance, array &$input)
    {
        foreach ($input as $key => $value)
        {
            $relationId = \camel_case($key);

            if (!method_exists($instance, $relationId)) continue;

            $relation = $instance->$relationId();

            if ($relation instanceof Relation)
            {
                $this->syncRelation($instance, $relation, $relationId, $key, $value);

                // Unset this attribute to prevent sending non-attribute values to the database
                // when user did not set $fillable attribute on the model.
                unset($input[$key]);
            }
        }

        return $this;
    }

    /**
     * Sync one given relationship.
     *
     * @param Eloquent $instance
     * @param Relation $relation
     * @param string   $relationId
     * @param string   $key
     * @param array    $data
     */
    protected function syncRelation(Eloquent $instance, Relation $relation, $relationId, $key, $data)
    {
        $method = "sync".class_basename($relation);

        if (method_exists($this, $method))
        {
            $this->$method($instance, $relationId, $key, $data);
        }
    }

    /**
     * Sync BelongsToMany relationship.
     *
     * @param Eloquent $instance
     * @param string   $relationId
     * @param string   $key
     * @param array    $data
     *
     * @return $this
     */
    protected function syncBelongsToMany(Eloquent $instance, $relationId, $key, $data)
    {
        $data = is_array($data) ? $data : [];

        return $this->afterSave(function ($instance) use ($relationId, $key, $data)
        {
            $instance->$relationId()->sync($data);

            unset($instance->$key);
        });
    }

    /**
     * Sync MorphToMany relationship.
     *
     * @param   Eloquent  $instance
     * @param   string    $relationId
     * @param   string    $key
     * @param   array     $data
     *
     * @return  $this
     */
    protected function syncMorphToMany(Eloquent $instance, $relationId, $key, $data)
    {
        return $this->syncBelongsToMany($instance, $relationId, $key, $data);
    }

    /**
     * Sync BelongsTo relationship.
     *
     * @param Eloquent $instance
     * @param string   $relationId
     * @param string   $key
     * @param int      $data
     *
     * @return $this
     */
    protected function syncBelongsTo(Eloquent $instance, $relationId, $key, $data)
    {
        $foreignKey = $instance->$relationId()->getForeignKey();

        $instance->setAttribute($foreignKey, $data ?: null);

        unset($instance->$key);

        return $this;
    }

    /**
     * Sync HasOne relationship.
     *
     * @param Eloquent $instance
     * @param string   $relationId
     * @param string   $key
     * @param int      $data
     *
     * @return $this
     */
    protected function syncHasOne(Eloquent $instance, $relationId, $key, $data)
    {
        return $this->syncHasOneOrMany($instance, $relationId, $key, $data);
    }

    /**
     * Sync HasMany relationship.
     *
     * @param Eloquent $instance
     * @param string   $relationId
     * @param string   $key
     * @param array    $data
     *
     * @return $this
     */
    protected function syncHasMany(Eloquent $instance, $relationId, $key, $data)
    {
        return $this->syncHasOneOrMany($instance, $relationId, $key, $data);
    }

    /**
     * Sync HasOneOrMany relationship.
     *
     * TODO: Consider removing this entirely since HasOne and HasMany are supported by related properties.
     *
     * @param Eloquent $instance
     * @param string   $relationId
     * @param string   $key
     * @param mixed    $ids
     *
     * @return $this
     */
    protected function syncHasOneOrMany(Eloquent $instance, $relationId, $key, $ids)
    {
        $exists = $instance->exists;

        return $this->afterSave(function ($instance) use ($relationId, $key, $ids, $exists)
        {
            $relation = $instance->$relationId();
            $related = $relation->getRelated();
            $foreignKey = $relation->getPlainForeignKey();
            $relatedKey = $related->getKeyName();

            $ids = is_array($ids) ? $ids : [];

            if ($exists)
            {
                $attached = $relation->lists($relatedKey);

                $attach = array_diff($ids, $attached);
                $detach = array_diff($attached, $ids);

                if (count($detach) > 0)
                {
                    $relation->whereIn($relatedKey, $detach)->update([ $foreignKey => null ]);
                }
            }
            else
            {
                $attach = $ids;
            }

            if (count($attach) > 0)
            {
                $related->newQuery()->whereIn($relatedKey, $attach)
                                    ->update([ $foreignKey => $relation->getParentKey() ]);
            }

            unset($instance->$key);
        });
    }

    /**
     * Add a callback that will be fired after model is saved.
     *
     * @param callable $callback
     *
     * @return $this
     */
    protected function afterSave(Callable $callback)
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
    protected function fireAfterSave(Eloquent $instance)
    {
        foreach ($this->postSave as $callback)
        {
            $callback($instance);
        }

        $this->postSave = [];

        return $instance;
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
    protected function upload(array &$input)
    {
        foreach ($this->files as $attr => $file)
        {
            if (isset($input[$attr])) $input[$attr] = $file->upload($input[$attr]);
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
     * @inheritdoc
     *
     * @param  int|array $ids
     *
     * @return int
     */
    public function delete($ids)
    {
        $ids = is_array($ids) ? $ids : func_get_args();

        $key = $this->model->getKeyName();

        $count = 0;

        foreach ($this->newQuery()->whereIn($key, $ids) as $item)
        {
            if ($item->delete()) $count++;
        }

        return $count;
    }

    /**
     * @inheritdoc
     *
     * @param string $key
     *
     * @return bool
     */
    public function isFillable($key)
    {
        return $this->model->isFillable($key);
    }

}