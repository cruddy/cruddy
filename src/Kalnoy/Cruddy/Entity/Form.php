<?php namespace Kalnoy\Cruddy\Entity;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Validation\Validator;
use Kalnoy\Cruddy\FileUploader;

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
     * @return bool
     */
    public function create(array $data)
    {
        if (!$this->validate($data)) return false;

        $this->upload($data);

        $instance = $this->model->newInstance($data);

        if (!$instance->save()) return false;

        $this->sync($instance, $data);

        return $instance;
    }

    /**
     * Update an existing model.
     *
     * @param \Illuminate\Database\Eloquent\Model $instance
     * @param  array                              $data
     *
     * @return bool
     */
    public function update(Eloquent $instance, array $data)
    {
        if (!$this->validate($data)) return false;

        $this->upload($data);

        if ($instance->fill($data)->save() === false) return false;

        $this->sync($instance, $data);

        return $instance;
    }

    /**
     * Sync relationships.
     *
     * @param Eloquent $instance
     * @param array    $data
     */
    protected function sync(Eloquent $instance, array $data)
    {
        foreach ($data as $key => $value)
        {
            if (!method_exists($instance, $key)) continue;

            $relation = $instance->$key();

            if ($relation instanceof Relation)
            {
                $this->syncRelation($instance, $relation, $key, $value);
            }
        }
    }

    /**
     * Sync one given relationship.
     *
     * @param Eloquent $instance
     * @param Relation $relation
     * @param          $key
     * @param          $data
     */
    protected function syncRelation(Eloquent $instance, Relation $relation, $key, $data)
    {
        $method = "sync".class_basename($relation);

        if (method_exists($this, $method))
        {
            $this->$method($instance, $key, $data);
        }
    }

    /**
     * Sync BelongsToMany relationship.
     *
     * @param Eloquent $instance
     * @param          $key
     * @param          $data
     */
    protected function syncBelongsToMany(Eloquent $instance, $key, $data)
    {
        $data = $data === false ? array() : $data;

        $instance->$key()->sync($data);

        unset($instance->$key);
    }

    /**
     * Sync BelongsTo relationship.
     *
     * @param Eloquent $instance
     * @param          $key
     * @param          $data
     */
    protected function syncBelongsTo(Eloquent $instance, $key, $data)
    {
        $relation = $instance->$key();
        $foreignKey = $relation->getForeignKey();

        if ($instance->getAttribute($foreignKey) == $data) return;

        $parent = $relation->getRelated()->findOrFail($data);

        $relation->associate($parent);
        $instance->save();
    }

    /**
     * Sync HasOne relationship.
     *
     * @param Eloquent $instance
     * @param          $key
     * @param          $data
     */
    protected function syncHasOne(Eloquent $instance, $key, $data)
    {
        $this->syncHasOneOrMany($instance, $key, $data);
    }

    /**
     * Sync HasMany relationship.
     *
     * @param Eloquent $instance
     * @param          $key
     * @param          $data
     */
    protected function syncHasMany(Eloquent $instance, $key, $data)
    {
        $this->syncHasOneOrMany($instance, $key, $data);
    }

    /**
     * Sync HasOneOrMany relationship.
     *
     * TODO: Consider removing this entirely since HasOne and HasMany are supported by related properties.
     *
     * @param Eloquent $instance
     * @param          $key
     * @param          $ids
     */
    protected function syncHasOneOrMany(Eloquent $instance, $key, $ids)
    {
        $relation = $instance->$key();
        $foreignKey = $relation->getPlainForeignKey();
        $relatedKey = $relation->getRelated()->getKeyName();

        $oldIds = $relation->lists($relatedKey);
        $ids = (array)$ids;

        $attach = array_diff($ids, $oldIds);
        if (count($attach) > 0)
        {
            $instance->$key()
                ->whereIn($relatedKey, $attach)
                ->update(array($foreignKey => $relation->getParentKey()));
        }

        $detach = array_diff($oldIds, $ids);
        if (count($detach) > 0)
        {
            $instance->$key()
                ->whereIn($relatedKey, $detach)
                ->update(array($foreignKey => null));
        }

        unset($instance->$key);
    }

    /**
     * Upload files if any.
     *
     * @param array $input
     */
    protected function upload(array &$input)
    {
        foreach ($this->files as $attr => $file)
        {
            if (isset($input[$attr])) $input[$attr] = $file->upload($input[$attr]);
        }
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