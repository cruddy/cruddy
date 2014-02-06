<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Kalnoy\Cruddy\Schema\Fields\BasicRelation;

/**
 * Handles belongs to many relation.
 */
class MorphToMany extends BasicRelation {

    /**
     * @inheritdoc
     *
     * @var bool
     */
    protected $multiple = true;
}