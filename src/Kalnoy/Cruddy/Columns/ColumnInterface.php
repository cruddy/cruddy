<?php namespace Kalnoy\Cruddy\Columns;

use Illuminate\Database\Eloquent\Builder;
use Kalnoy\Cruddy\AttributeInterface;

interface ColumnInterface extends AttributeInterface {

    /**
     * Get whether the column can be sorted.
     *
     * @return bool
     */
    function isSortable();

    function isFilterable();

    /**
     * Apply an order to the query builder.
     *
     * @param  Builder $builder
     *
     * @return void
     */
    function applyOrder(Builder $builder, $direction);

    /**
     * Apply constraints to the query builder.
     *
     * @param  Builder $query
     * @param  mixed   $data
     *
     * @return void
     */
    function applyConstraints(Builder $query, $data);
}