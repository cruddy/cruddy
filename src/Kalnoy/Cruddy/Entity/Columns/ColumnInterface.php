<?php namespace Kalnoy\Cruddy\Entity\Columns;

use Illuminate\Database\Query\Builder;
use Kalnoy\Cruddy\Entity\Attribute\AttributeInterface;

interface ColumnInterface extends AttributeInterface {

    /**
     * Get whether the column can be sorted.
     *
     * @return bool
     */
    public function isSortable();

    /**
     * Get whether column can filter data.
     *
     * @return bool
     */
    public function isFilterable();

    /**
     * Get whether column can search using "search everything" feature.
     *
     * @return bool
     */
    public function isSearchable();

    /**
     * Apply an order to the query builder.
     *
     * @param  Builder $builder
     * @param          $direction
     *
     * @return $this
     */
    public function applyOrder(Builder $builder, $direction);

    /**
     * Apply constraints to the query builder.
     *
     * @param  Builder $query
     * @param  mixed   $data
     * @param string   $boolean
     *
     * @return $this
     */
    public function applyConstraints(Builder $query, $data, $boolean = 'and');
}