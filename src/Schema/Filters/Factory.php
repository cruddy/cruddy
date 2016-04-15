<?php

namespace Kalnoy\Cruddy\Schema\Filters;

use Kalnoy\Cruddy\Contracts\Filter;
use Kalnoy\Cruddy\Schema\BaseFactory;

class Factory extends BaseFactory
{
    /**
     * @var array
     */
    protected $macros = [
        'usingField' => Types\Proxy::class,
    ];

}