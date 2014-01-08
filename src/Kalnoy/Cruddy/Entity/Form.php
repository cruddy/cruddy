<?php namespace Kalnoy\Cruddy\Entity;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;
use Kalnoy\Cruddy\Service\FileUploader;
use Exception;

class Form implements FormInterface {

    /**
     * A model instance.
     *
     * @var Eloquent
     */
    protected $model;

    /**
     * Validators factory.
     *
     * @var Validator
     */
    protected $validator;

    /**
     * @var FileUploader[]
     */
    protected $files;

    /**
     * @var Callable[]
     */
    protected $postSave = [];

    /**
     * Init a model form.
     *
     * @param Eloquent  $model
     * @param Validator $validator
     * @param array     $files
     */
    public function __construct(Eloquent $model, Validator $validator, array $files)
    {
        $this->model = $model;
        $this->validator = $validator;
        $this->files = $files;
    }

    /**
     * Get a model instance.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function instance()
    {
        return $this->model;
    }

    /**
     * Create a new model in the database.
     *
     * @param  array  $data
     *
     * @return false|Eloquent
     */
    public function create(array $data)
    {
        if (!$this->validate($data)) return false;

        return $this->save($this->model->newInstance(), $data);
    }

    /**
     * Update an existing model.
     *
     * @param \Illuminate\Database\Eloquent\Model $instance
     * @param  array                              $data
     *
     * @return false|Eloquent
     */
    public function update(Eloquent $instance, array $data)
    {
        if (!$this->validate($data)) return false;

        return $this->save($instance, $data);
    }

    /**
     * Save a model.
     *
     * TODO: Move this out to repository.
     *
     * @param Eloquent $instance
     * @param array    $input
     *
     * @throws \Exception
     * @return bool|Eloquent
     */
    protected function save(Eloquent $instance, array $input)
    {
        $this->sync($instance, $input)->upload($input);

        try
        {
            if (false === $instance->fill($input)->save())
            {
                $this->cancelUploads();

                return false;
            }
        }

        catch (Exception $e)
        {
            $this->cancelUploads();

            throw $e;
        }

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
            $relationId = Str::camel($key);

            if (!method_exists($instance, $relationId)) continue;

            $relation = $instance->$relationId();

            if ($relation instanceof Relation)
            {
                $this->syncRelation($instance, $relation, $relationId, $key, $value);

                // Unset this attribute to prevent sending non-attribute values to the database
                // when user did not set $fillable attribute on a model.
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
        $data = $data === false ? [] : $data;

        return $this->afterSave(function ($instance) use ($relationId, $key, $data)
        {
            $instance->$relationId()->sync($data);

            unset($instance->$key);
        });
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

            $ids = (array)$ids;

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
     * Delete a model or a set of models.
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
        foreach ($this->model->newInstance()->whereIn($key, $ids) as $item)
        {
            if ($item->delete()) $count++;
        }

        return $count;
    }

    /**
     * Validate data.
     *
     * @param  array  $data
     *
     * @return bool
     */
    protected function validate(array $data)
    {
        $this->validator->setData($data);

        return $this->validator->passes();
    }

    /**
     * Get error messages.
     *
     * @return null|\Illuminate\Support\MessageBag
     */
    public function errors()
    {
        return $this->validator->errors();
    }
}