<?php

namespace Kalnoy\Cruddy\Schema\Fields;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * Base number class.
 */
abstract class BaseNumber extends BaseField {

    /**
     * @inheritdoc
     *
     * @var string
     */
    protected $class = 'Number';

    /**
     * @inheritdoc
     *
     * @var bool
     */
    protected $canOrder = true;

    /**
     * @inheritdoc
     *
     * @var string
     */
    protected $filterType = self::FILTER_COMPLEX;

    /**
     * Whether the number is decimal.
     *
     * @var bool
     */
    protected $isDecimal = false;

    /**
     * @inheritdoc
     *
     * @param \Illuminate\Database\Query\Builder $builder
     * @param asc|desc                           $direction
     *
     * @return $this
     */
    public function order(QueryBuilder $builder, $direction)
    {
        $builder->orderBy($this->id, $direction);

        return $this;
    }

    /**
     * @inheritdoc
     *
     * @param \Illuminate\Database\Query\Builder $builder
     * @param mixed                              $data
     *
     * @return $this
     */
    public function filter(QueryBuilder $builder, $data)
    {
        extract($data);

        if ($val !== '') $builder->where($this->id, $op, $val);

        return $this;
    }

    /**
     * @inheritdoc
     *
     * @return array
     */
    public function toArray()
    {
        return
        [
            'is_decimal' => $this->isDecimal,

        ] + parent::toArray();
    }

}