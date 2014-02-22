<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

class HasManyInline extends HasOneInline {

    /**
     * @inheritdoc
     *
     * @var bool
     */
    protected $multiple = true;
}