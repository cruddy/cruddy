<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Kalnoy\Cruddy\Schema\Fields\BaseField;
use Illuminate\Database\Eloquent\Model;

/**
 * Computed field.
 * 
 * @since 1.0.0
 */
class Computed extends BaseField {

    /**
     * {@inheritdoc}
     */
    protected $class = 'Computed';

    /**
     * The accessor.
     *
     * @var string|\Closure
     */
    public $accessor;

    /**
     * {@inheritdoc}
     */
    public function extract(Model $model)
    {
        if (is_string($this->accessor)) return $model->{$this->accessor}();

        return {$this->accessor}($model);
    }

    /**
     * {@inheritdoc}
     */
    public function isDisabled($action)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isFillable()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function keep($value)
    {
        return false;
    }

}