<?php

namespace Kalnoy\Cruddy\Form\Fields;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Kalnoy\Cruddy\Contracts\Filter;
use Kalnoy\Cruddy\Form\Fields\BaseInput;

/**
 * Base number field class.
 *
 * Number fields use special filter, they also cast value to appropriate format
 * when both extracting and processing value.
 * 
 * @method $this digits(int $num)
 * @method $this digitsBetween(int $min, int $max)
 *
 * @package Kalnoy\Cruddy\Form\Fields
 */
abstract class BaseNumber extends BaseInput
{
    /**
     * The name of the JavaScript class that is used to render this field.
     *
     * @return string
     */
    public function getUIModelClass()
    {
        return 'Cruddy.Fields.Number';
    }

    /**
     * @inheritdoc
     */
    public function getModelValue($model)
    {
        $value = parent::getModelValue($model);

        return $this->castNullable($value);
    }

    /**
     * @param array $value
     *
     * @return mixed
     */
    protected function processValueBeforeSetting($value)
    {
        return $this->castNullable(parent::processValueBeforeSetting($value));
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function castNullable($value)
    {
        return is_null($value) ? $value : $this->cast($value);
    }

    /**
     * Cast value to a specific numeric type.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    abstract public function cast($value);

}