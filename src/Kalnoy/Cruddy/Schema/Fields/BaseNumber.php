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
abstract class BaseNumber extends BaseInput implements Filter {

    /**
     * {@inheritdoc}
     */
    protected $class = 'Cruddy.Fields.Number';

    /**
     * Whether the number is decimal.
     *
     * @var bool
     */
    protected $isDecimal = false;

    /**
     * {@inheritdoc}
     *
     * If value is empty, null is returned.
     */
    public function process($value)
    {
        $value = trim($value);

        return $value === '' ? null : $this->cast($value);
    }

    /**
     * {@inheritdoc}
     */
    public function extract(Eloquent $model)
    {
        $value = parent::extract($model);

        return $value === null ? $value : $this->cast($value);
    }

    /**
     * Cast value to a specific type.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    abstract protected function cast($value);

    /**
     * {@inheritdoc}
     */
    public function order(QueryBuilder $builder, $direction)
    {
        $builder->orderBy($this->id, $direction);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function applyFilterConstraint(QueryBuilder $builder, $data)
    {
        if (empty($data)) return;

        if (is_numeric($data))
        {
            $operator = '=';
            $value = $data;
        }
        else
        {
            $operator = substr($data, 0, 1);
            $value = $this->cast(substr($data, 1));
        }

        if ($value and $operator) $builder->where($this->id, $operator, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return
        [
            'is_decimal' => $this->isDecimal,

        ] + parent::toArray();
    }

}