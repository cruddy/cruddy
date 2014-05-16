<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

/**
 * Field to edit many inline models.
 * 
 * @since 1.0.0
 */
class HasManyInline extends HasOneInline {

    /**
     * {@inheritdoc}
     */
    protected $multiple = true;
}