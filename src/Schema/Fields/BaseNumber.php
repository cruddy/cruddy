<?php

namespace Kalnoy\Cruddy\Schema\Fields;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Kalnoy\Cruddy\Contracts\Filter;

/**
 * Base number field class.
 *
 * Number fields use special filter, they also cast value to appropriate format
 * when both extracting and processing value.
 *
 * @since 1.0.0
 */
abstract class BaseNumber extends BaseInput implements Filter
{
    /**
     * The name of the JavaScript class that is used to render this field.
     *
     * @return string
     */
    protected function getModelClass()
    {
        return 'Cruddy.Fields.Number';
    }

    /**
     * {@inheritdoc}
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
    protected function processInputValue($value)
    {
        return $this->castNullable(parent::processInputValue($value));
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
     * Cast value to a specific type.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    abstract public function cast($value);

    /**
     * {@inheritdoc}
     */
    public function applyFilterConstraint(QueryBuilder $builder, $data)
    {
        if (empty($data)) return;

        if (is_numeric($data)) {
            $operator = '=';
        } else {
            if (strlen($data) < 2) return;

            $operator = substr($data, 0, 1);
            $data = substr($data, 1);
        }

        $builder->where($this->id, $operator, $this->cast($data));
    }

}