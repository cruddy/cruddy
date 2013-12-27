<?php namespace Kalnoy\Cruddy\Fields\Types;

class Primary extends Text {

    /**
     * Get whether the field is visible.
     *
     * Primary field is hidden by default.
     *
     * @var bool
     */
    public $visible = false;
}