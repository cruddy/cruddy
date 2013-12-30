<?php namespace Kalnoy\Cruddy\Entity\Fields;

use Illuminate\Database\Eloquent\Model as Eloquent;

interface EditableInterface {

    /**
     * Get whether the field can be actually edited.
     *
     * @param  Eloquent $model
     *
     * @return bool
     */
    function isEditable(Eloquent $model);

    /**
     * Process the value before it is sent to the model's repository.
     *
     * The value is discarded if result is null.
     *
     * @param mixed $value
     */
    function process($value);
}