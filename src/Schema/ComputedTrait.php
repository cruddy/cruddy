<?php

namespace Kalnoy\Cruddy\Schema;

use Illuminate\Support\Str;

/**
 * Class Computed
 *
 * @package Kalnoy\Cruddy\Schema
 */
trait ComputedTrait
{
    /**
     * The accessor.
     *
     * @var string|callback
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
            $this->accessor = 'get'.Str::studly($this->id);
        }

        if (is_string($this->accessor) && 
            strpos($this->accessor, '::') === false
        ) {
            return $model->{$this->accessor}();
        }

        return call_user_func($this->accessor, $model);
    }
}