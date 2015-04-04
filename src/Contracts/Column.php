<?php

namespace Kalnoy\Cruddy\Contracts;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Kalnoy\Cruddy\Contracts\Attribute;

/**
 * Column
 */
interface Column extends Attribute {

    /**
     * @return string
     */
    public function getDefaultOrderDirection();

}