<?php

namespace Kalnoy\Cruddy\Entity\DataSource;

use Kalnoy\Cruddy\Service\BaseFactory;

/**
 * Class ColumnsFactory
 *
 * @package Kalnoy\Cruddy\Entity\DataSource
 */
class ColumnsFactory extends BaseFactory
{
    /**
     * @var array
     */
    protected $types = [
        'attr' => Columns\Attribute::class,
        'attribute' => Columns\Attribute::class,

        'compute' => Columns\Computed::class,
        'computed' => Columns\Computed::class,

        'enum' => Columns\Enum::class,
        'entity' => Columns\EntityColumn::class,
        'bool' => Columns\Boolean::class,
        'boolean' => Columns\Boolean::class,
    ];
}