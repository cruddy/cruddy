<?php

namespace Kalnoy\Cruddy\Schema;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * FieldInterface
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
     * Process an input value and convert it to a valid format.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function process($value);

    /**
     * Get whether the field is computed and should not be nor validated or sent to
     * the repository.
     *
     * @return bool
     */
    public function isComputed();

    /**
     * Get whether the field is disabled for specified action.
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