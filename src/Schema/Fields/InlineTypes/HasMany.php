<?php

namespace Kalnoy\Cruddy\Schema\Fields\InlineTypes;
use Kalnoy\Cruddy\Schema\Fields\InlineTypes\HasOne;

/**
 * Field to edit many inline models.
 *
 * @since 1.0.0
 */
class HasMany extends HasOne {

    /**
     * {@inheritdoc}
     */
    protected $multiple = true;
}