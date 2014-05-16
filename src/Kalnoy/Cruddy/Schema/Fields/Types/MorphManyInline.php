<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

/**
 * Morph many inline editing.
 * 
 * @since 1.0.0
 */
class MorphManyInline extends MorphOneInline {

    /**
     * {@inheritdoc}
     */
    protected $multiple = true;

}