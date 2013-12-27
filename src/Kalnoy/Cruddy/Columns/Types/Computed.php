<?php namespace Kalnoy\Cruddy\Columns\Types;

use Kalnoy\Cruddy\Columns\AbstractColumn;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Builder;

class Computed extends AbstractColumn {

    /**
     * The Closure that will receive a model to resolve a value.
     *
     * @var Closure
     */
    public $value;

    /**
     * An order clause to support sorting.
     *
     * It might be a column name or an SQL expression like DB::raw('...').
     *
     * @var mixed
     */
    public $order_clause;

    public $filter;

    public function value(Eloquent $model)
    {
        return $this->evaluate($this->value, $model);
    }

    public function applyOrder(Builder $builder, $direction)
    {
        if ($this->order_clause !== null)
        {
            $builder->orderBy($this->order_clause, $direction);
        }

        return $this;
    }

    public function applyConstraints(Builder $builder, $data)
    {
        if ($this->filter instanceof \Closure)
        {
            $closure = $this->filter->bindTo($this);

            $closure($builder, $data);
        }

        return $this;
    }

    public function isSortable()
    {
        return $this->order_clause !== null;
    }

    public function isFilterable()
    {
        return $this->filter !== null;
    }

    public function getJavaScriptClass()
    {
        return 'Computed';
    }
}