<?php

namespace Kalnoy\Cruddy\Schema\Columns\Types;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Query\Builder;
use Kalnoy\Cruddy\Schema\Columns\BaseColumn;
use Kalnoy\Cruddy\Entity;
use Kalnoy\Cruddy\Schema\ComputedTrait;

/**
 * Computed column that extracts data using a closure.
 *
 *
 * @since 1.0.0
 */
class Computed extends BaseColumn {

    use ComputedTrait;

    /**
     * An order clause to support sorting.
     *
     * It might be a column name or an SQL expression like DB::raw('...').
     *
     * @var mixed
     */
    public $columnClause;

    /**
     * The name of the JavaScript class that is used to render this field.
     *
     * @return string
     */
    protected function modelClass()
    {
        return 'Cruddy.Columns.Computed';
    }

    /**
     * Init the column.
     *
     * @param Entity $entity
     * @param string $id
     * @param string|\Closure $accessor
     */
    public function __construct(Entity $entity, $id, $accessor = null)
    {
        parent::__construct($entity, $id);

        $this->accessor = $accessor;
    }

    /**
     * Set column clause to support order.
     *
     * @param string|\Illuminate\Database\Query\Expression $value
     *
     * @return $this
     */
    public function clause($value)
    {
        $this->columnClause = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function canOrder()
    {
        return isset($this->columnClause);
    }
}