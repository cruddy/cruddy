<?php

namespace Kalnoy\Cruddy\Form;

use Kalnoy\Cruddy\Service\BaseFactory;

/**
 * Field FieldsFactory class.
 *
 * @package Kalnoy\Cruddy\Form
 */
class FieldsFactory extends BaseFactory
{
    /**
     * @var array
     */
    protected $types = [
        'string' => Fields\StringInput::class,
        'text' => Fields\Text::class,
        'email' => Fields\Email::class,
        'password' => Fields\Password::class,
        
        'datetime' => Fields\DateTime::class,
        'time' => Fields\Time::class,
        'date' => Fields\Date::class,
        
        'boolean' => Fields\Boolean::class,
        'bool' => Fields\Boolean::class,
        
        'file' => Fields\File::class,
        'image' => Fields\Image::class,
        
        'int' => Fields\Integer::class,
        'integer' => Fields\Integer::class,
        'float' => Fields\FloatInput::class,
        
        'compute' => Fields\Computed::class,
        'computed' => Fields\Computed::class,
        
        'enum' => Fields\Enum::class,
        'slug' => Fields\Slug::class,
    ];
}