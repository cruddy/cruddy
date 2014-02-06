<?php

namespace Kalnoy\Cruddy\Schema\Fields;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Kalnoy\Cruddy\Schema\ColumnInterface;

/**
 * BaseTextField implements {@see \Kalnoy\Cruddy\Schema\ColumnInterface}.
 */
class BaseTextField extends BaseField {

    /**
     * @inheritdoc
     *
     * @var string
     */
    protected $class = 'Input';

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
    protected $filterType = self::FILTER_STRING;

    /**
     * The HTML <input> type attribute value.
     *
     * @var string
     */
    protected $inputType = 'text';

    /**
     * Process value.
     *
     * @param  string $value
     *
     * @return string
     */
    public function process($value)
    {
        return trim($value);
    }

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
        $builder->orWhere($this->id, 'like', '%'.$data.'%');

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
            'input_type' => $this->inputType,

        ] + parent::toArray();
    }

}