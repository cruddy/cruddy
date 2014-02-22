<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

/**
 * Morph many inline editing.
 */
class MorphManyInline extends MorphOneInline {

    /**
     * @inheritdoc
     *
     * @var bool
     */
    protected $multiple = true;

}