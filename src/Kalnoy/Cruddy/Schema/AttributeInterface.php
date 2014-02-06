<?php

namespace Kalnoy\Cruddy\Schema;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Contracts\ArrayableInterface;

/**
 * AttributeInterface
 */
interface AttributeInterface extends ArrayableInterface {
    
    /**
     * No filtering is supported.
     */
    const FILTER_NONE = 'none';

    /**
     * Filtering is based on string value.
     */
    const FILTER_STRING = 'string';

    /**
     * Filtering is more complex and requires additional data.
     */
    const FILTER_COMPLEX = 'complex';

    /**
     * Extract model's value.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return mixed
     */
    public function extract(Eloquent $item);

    /**
     * Modify eloquent query before requesting any data.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return $this
     */
    public function modifyQuery(EloquentBuilder $builder);

    /**
     * Apply an order to the query builder.
     *
     * @param \Illuminate\Database\Query\Builder $builder
     * @param string                             $direction
     *
     * @return $this
     */
    public function order(QueryBuilder $builder, $direction);

    /**
     * Apply constraints to the query builder.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param mixed                              $data
     *
     * @return $this
     */
    public function filter(QueryBuilder $query, $data);

    /**
     * Get whether column can order data.
     *
     * @return bool
     */
    public function canOrder();

    /**
     * Get filter type.
     *
     * @return string
     */
    public function getFilterType();

    /**
     * Get the attribute identifier.
     *
     * @return string
     */
    public function getId();
}