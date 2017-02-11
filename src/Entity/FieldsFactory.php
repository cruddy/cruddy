<?php

namespace Kalnoy\Cruddy\Entity;

use Kalnoy\Cruddy\Form\FieldsFactory as BaseFactory;

/**
 * Class FieldsFactory
 *
 * @package Kalnoy\Cruddy\Entity
 */
class FieldsFactory extends BaseFactory
{
    /**
     * @var string
     */
    protected $parentFactory = 'cruddy.form.fields';

    /**
     * @var array
     */
    protected $types = [
        'belongsTo' => Fields\BelongsTo::class,
        'belongsToMany' => Fields\BelongsToMany::class,
        'morphToMany' => Fields\BelongsToMany::class,

        'hasOne' => Fields\HasOne::class,
        'hasMany' => Fields\HasMany::class,
        'morphOne' => Fields\HasOne::class,
        'morphMany' => Fields\HasMany::class,
    ];
}