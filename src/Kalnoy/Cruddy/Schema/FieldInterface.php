<?php

namespace Kalnoy\Cruddy\Schema;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * Base interface for all fields.
 * 
 * @since 1.0.0
 */
interface FieldInterface extends AttributeInterface {

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
     * Extract data from a model for column.
     * 
     * @param \Illuminate\Database\Eloquent\Model $model
     * 
     * @return mixed
     */
    public function extractForColumn(Eloquent $model);

    /**
     * Process a value and convert it to a format consumable by a validator
     * and a repository.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function process($value);

    /**
     * Get whether value can stay in the input.
     *
     * @return bool
     */
    public function keep($value);

    /**
     * Get whether the field is allowed to be sent to the repository.
     *
     * @param string $action
     *
     * @return bool
     */
    public function sendToRepository($action);

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
     * Get filter type.
     *
     * @return string
     */
    public function getFilterType();

}