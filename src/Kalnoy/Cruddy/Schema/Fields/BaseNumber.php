<?php

namespace Kalnoy\Cruddy\Schema\Fields;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * Base number field class.
 * 
 * Number fields use special filter, they also cast value to appropriate format
 * when both extracting and processing value.
 * 
 * @since 1.0.0
 */
abstract class BaseNumber extends BaseField {

    /**
     * {@inheritdoc}
     */
    protected $class = 'Number';

    /**
     * {@inheritdoc}
     */
    protected $canOrder = true;

    /**
     * {@inheritdoc}
     */
    protected $filterType = self::FILTER_COMPLEX;

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
    public function filter(QueryBuilder $builder, $data)
    {
        extract($data);

        if ($val !== '') $builder->where($this->id, $op, $val);

        return $this;
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