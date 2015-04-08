<?php

namespace Kalnoy\Cruddy\Schema\Fields\InlineTypes;

/**
 * Field to edit many inline models.
 *
 * @since 1.0.0
 */
class HasMany extends HasOne {

    /**
     * Get whether the relations works with a collection of models.
     *
     * @return bool
     */
    public function isMultiple()
    {
        return true;
    }

}