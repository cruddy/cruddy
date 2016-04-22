<?php

namespace Kalnoy\Cruddy\Contracts;

/**
 * Base interface for all fields.
 *
 * @since 1.0.0
 */
interface Field extends Attribute
{
    /**
     * The value will not be set on model.
     */
    const MODE_NONE = 0;

    /**
     * The value should be set before saving the model.
     */
    const MODE_BEFORE_SAVE = 1;

    /**
     * The value should be set after the model has been saved.
     */
    const MODE_AFTER_SAVE = 2;

    /**
     * Extract data from a model for column.
     *
     * @param mixed $model
     *
     * @return mixed
     */
    public function getModelValueForColumn($model);

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
     * Get model value setting mode.
     *
     * @return int
     */
    public function getSettingMode();

    /**
     * Process a value and convert it to a format consumable by a validator.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function parseInputValue($value);

    /**
     * @return string
     */
    public function getLabel();

    /**
     * Get whether the field is disabled for specified model.
     *
     * @param $model
     *
     * @return bool
     */
    public function isDisabled($model);

    /**
     * @param mixed $modelKey
     *
     * @return array
     */
    public function getRules($modelKey);

}