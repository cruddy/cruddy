<?php

namespace Kalnoy\Cruddy\Contracts;

/**
 * Base interface for all fields.
 *
 * @since 1.0.0
 */
interface Field extends Attribute {

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
     * @param mixed $model
     *
     * @return mixed
     */
    public function extractForColumn($model);

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
     * @return string
     */
    public function getLabel();

    /**
     * Get whether the field is disabled for specified action.
     *
     * @param $action
     *
     * @return bool
     */
    public function isDisabled($action);

}