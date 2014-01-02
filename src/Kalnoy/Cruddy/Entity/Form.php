<?php namespace Kalnoy\Cruddy\Entity;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Validation\Validator;

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
     * Init a model form.
     *
     * @param Model $model
     */
    public function __construct(Eloquent $model, $validator)
    {
        $this->model = $model;
        $this->validator = $validator;
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

        $instance = $this->model->newInstance($data);

        if (!$instance->save()) return false;

        $this->sync($instance, $data);

        return $instance;
    }

    /**
     * Update an existing model.
     *
     * @param  int $id
     * @param  array $data
     *
     * @return bool
     */
    public function update(Eloquent $instance, array $data)
    {
        if (!$this->validate($data)) return false;

        if ($instance->fill($data)->save() === false) return false;

        $this->sync($instance, $data);

        return $instance;
    }

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

    protected function syncRelation(Eloquent $instance, Relation $relation, $key, $data)
    {
        $method = "sync".class_basename($relation);

        if (method_exists($this, $method))
        {
            $this->$method($instance, $key, $data);
        }
    }

    protected function syncBelongsToMany(Eloquent $instance, $key, $data)
    {
        $data = $data === false ? array() : $data;

        $instance->$key()->sync($data);

        unset($instance->$key);
    }

    protected function syncBelongsTo(Eloquent $instance, $key, $data)
    {
        $relation = $instance->$key();
        $foreignKey = $relation->getForeignKey();

        if ($instance->getAttribute($foreignKey) == $data) return;

        $parent = $relation->getRelated()->findOrFail($data);

        $relation->associate($parent);
        $instance->save();
    }

    protected function syncHasOne(Eloquent $instance, $key, $data)
    {
        $this->syncHasOneOrMany($instance, $key, $data);
    }

    protected function syncHasMany(Eloquent $instance, $key, $data)
    {
        $this->syncHasOneOrMany($instance, $key, $data);
    }

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