<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

class HasManyInline extends HasOneInline {

    /**
     * @inheritdoc
     *
     * @var string
     */
    protected $class = 'HasMany';

    /**
     * @inheritdoc
     *
     * @var bool
     */
    protected $multiple = true;
}