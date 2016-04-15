<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 04.04.2015
 * Time: 10:43
 */

namespace Kalnoy\Cruddy\Schema;

/**
 * Class Computed
 *
 * @method $this eager($relations)
 * @property array|string $eager
 *
 * @package Kalnoy\Cruddy\Schema
 */
trait ComputedTrait
{
    /**
     * The accessor.
     *
     * @var string|\Closure
     */
    protected $accessor;

    /**
     * @param mixed $model
     *
     * @return mixed
     */
    public function getModelValue($model)
    {
        if ( ! $model->exists) return null;

        if (is_null($this->accessor)) {
            $this->accessor = 'get'.studly_case($this->id);
        }

        if (is_string($this->accessor)) {
            return $model->{$this->accessor}();
        }

        return call_user_func($this->accessor, $model);
    }

    /**
     * @return array
     */
    public function eagerLoads()
    {
        return (array)$this->eager;
    }

}