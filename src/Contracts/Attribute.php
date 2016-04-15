<?php

namespace Kalnoy\Cruddy\Contracts;

use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * Base attribute interface.
 *
 * Attributes extract data, can order data lists.
 *
 * @since 1.0.0
 */
interface Attribute extends Entry
{
    /**
     * Get model value.
     *
     * @param mixed $model
     *
     * @return mixed
     */
    public function getModelValue($model);

    /**
     * Apply an order to the query builder.
     *
     * @param QueryBuilder $builder
     * @param string $direction
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