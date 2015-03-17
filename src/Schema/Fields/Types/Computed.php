<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Kalnoy\Cruddy\Schema\Fields\BaseField;
use Illuminate\Database\Eloquent\Model;

/**
 * Computed field.
 *
 * @method $this eager($relations)
 *
 * @property array|string $eager
 *
 * @since 1.0.0
 */
class Computed extends BaseField {

    /**
     * {@inheritdoc}
     */
    protected $class = 'Cruddy.Fields.Computed';

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
        if ( ! $model->exists) return null;

        if (is_string($this->accessor)) return $model->{$this->accessor}();

        $accessor = $this->accessor;

        return $accessor($model);
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

    /**
     * @return array
     */
    public function eagerLoads()
    {
        return $this->get('eager', []);
    }

}