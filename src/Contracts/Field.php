<?php

namespace Kalnoy\Cruddy\Contracts;

/**
 * Base interface for all fields.
 *
 * @since 1.0.0
 */
interface Field
{
    /**
     * Process a value and convert it to a format consumable by a validator.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function parseInputValue($value);
    
    /**
     * Get model value.
     *
     * @param mixed $model
     *
     * @return mixed
     */
    public function getModelValue($model);
    
    /**
     * Set attribute on model from input.
     *
     * @param mixed $model
     * @param mixed $value
     *
     * @return $this
     */
    public function setModelValue($model, $value);

    /**
     * Get validation rules.
     * 
     * @return array
     */
    public function getRules();

}