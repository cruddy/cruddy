<?php

namespace Kalnoy\Cruddy\Schema\Columns\Types;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Query\Builder;
use Kalnoy\Cruddy\Schema\Columns\BaseColumn;
use Kalnoy\Cruddy\Entity;

class Computed extends BaseColumn {

    /**
     * @inheritdoc
     *
     * @var string
     */
    protected $class = 'Computed';

    /**
     * The Closure that will receive a model to resolve a value.
     *
     * @var \Closure
     */
    protected $value;

    /**
     * An order clause to support sorting.
     *
     * It might be a column name or an SQL expression like DB::raw('...').
     *
     * @var mixed
     */
    public $columnClause;

    /**
     * Init column.
     *
     * @param \Kalnoy\Cruddy\Entity  $entity
     * @param string                 $id
     * @param \Closure               $value
     */
    public function __construct(Entity $entity, $id, \Closure $value)
    {
        parent::__construct($entity, $id);

        $this->value = $value;
    }

    /**
     * Set column clause to support order and search.
     *
     * @param string|\Illuminate\Database\Expression $value
     *
     * @return $this
     */
    public function clause($value)
    {
        $this->columnClause = $value;

        return $this;
    }

    /**
     * @inheritdoc
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return mixed
     */
    public function extract(Eloquent $model)
    {
        $method = $this->value;

        return $method($model, $this->entity);
    }

    /**
     * @inheritdoc
     *
     * @param \Illuminate\Database\Query\Builder $builder
     * @param string                             $direction
     *
     * @return $this
     */
    public function order(Builder $builder, $direction)
    {
        if ($this->columnClause !== null)
        {
            $builder->orderBy($this->columnClause, $direction);
        }

        return $this;
    }

    /**
     * @inheritdoc
     *
     * @return bool
     */
    public function canOrder()
    {
        return isset($this->columnClause);
    }
}