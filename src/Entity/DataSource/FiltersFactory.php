<?php

namespace Kalnoy\Cruddy\Entity\DataSource;

use Kalnoy\Cruddy\Service\BaseFactory;

class FiltersFactory extends BaseFactory
{
    /**
     * @var array
     */
    protected $types = [
        'enum' => Filters\Enum::class,
        'bool' => Filters\Boolean::class,
        'boolean' => Filters\Boolean::class,
        'entity' => Filters\Entity::class,
    ];
}