<?php

namespace Kalnoy\Cruddy\Schema\Fields\InlineTypes;

/**
 * Morph many inline editing.
 *
 * @since 1.0.0
 */
class MorphMany extends MorphOne {

    /**
     * {@inheritdoc}
     */
    protected $multiple = true;

}