<?php

namespace Kalnoy\Cruddy\Service\Validation;

/**
 * Validable interface.
 * 
 * The objects of that interface are used to validate input for specified action.
 * 
 * @since 1.0.0
 */
interface ValidableInterface {

    /**
     * Create action.
     */
    const CREATE = 'create';

    /**
     * Update action.   
     */
    const UPDATE = 'update';

    /**
     * Validate an input for specific action which is either `create` or `update`.
     *
     * @param string $action
     * @param array  $input
     * @param array  $labels
     *
     * @return bool
     */
    public function validFor($action, array $input, array $labels);

    /**
     * Get validation errors.
     *
     * @return array
     */
    public function errors();
}