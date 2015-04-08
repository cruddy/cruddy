<?php

namespace Kalnoy\Cruddy\Schema\Fields\InlineTypes;

/**
 * Morph many inline editing.
 *
 * @since 1.0.0
 */
class MorphMany extends MorphOne {

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