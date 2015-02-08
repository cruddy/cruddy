<?php

namespace Kalnoy\Cruddy\Contracts;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Contracts\ArrayableInterface;

/**
 * Base attribute interface.
 *
 * Attributes extract data, can order data lists.
 *
 * @since 1.0.0
 */
interface Attribute extends Entry {

    /**
     * Get model's corresponding value.
     *
     * @param Eloquent $model
     *
     * @return mixed
     */
    public function extract(Eloquent $model);

    /**
     * Modify eloquent query before requesting any data.
     *
     * @param EloquentBuilder $builder
     *
     * @return void
     */
    public function modifyQuery(EloquentBuilder $builder);

    /**
     * Apply an order to the query builder.
     *
     * @param QueryBuilder $builder
     * @param string       $direction
     *
     * @return $this
     */
    public function order(QueryBuilder $builder, $direction);

    /**
     * Get whether attribute can order data.
     *
     * @return bool
     */
    public function canOrder();
}