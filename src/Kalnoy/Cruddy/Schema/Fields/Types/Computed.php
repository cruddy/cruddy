<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Kalnoy\Cruddy\Schema\Fields\BaseField;
use Illuminate\Database\Eloquent\Model;

/**
 * Computed field.
 */
class Computed extends BaseField {

    /**
     * @inheritdoc
     *
     * @var string
     */
    protected $class = 'Computed';

    /**
     * The accessor.
     *
     * @var string|\Closure
     */
    public $accessor;

    /**
     * @inheritdoc
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return mixed
     */
    public function extract(Model $model)
    {
        if (is_string($this->accessor)) return $model->{$this->accessor}();

        return $this->accessor($model);
    }

    /**
     * @inheritdoc
     *
     * @param string $action
     *
     * @return bool
     */
    public function isDisabled($action)
    {
        return true;
    }

    /**
     * @inheritdoc
     *
     * @return bool
     */
    public function isFillable()
    {
        return false;
    }

    /**
     * @inheritdoc
     *
     * @return bool
     */
    public function keep($value)
    {
        return false;
    }

}