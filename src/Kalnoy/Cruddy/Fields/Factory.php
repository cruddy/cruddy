<?php namespace Kalnoy\Cruddy\Fields;

use Kalnoy\Cruddy\AttributeFactory;

class Factory extends AttributeFactory {

    /**
     * Built-in field types.
     *
     * @var array
     */
    protected $types = array(
        'primary' => 'Kalnoy\Cruddy\Fields\Types\Primary',
        'string' => 'Kalnoy\Cruddy\Fields\Types\Text',
        'text' => 'Kalnoy\Cruddy\Fields\Types\TextArea',
        'email' => 'Kalnoy\Cruddy\Fields\Types\Email',
        'password' => 'Kalnoy\Cruddy\Fields\Types\Password',
        'datetime' => 'Kalnoy\Cruddy\Fields\Types\DateTime',
        'time' => 'Kalnoy\Cruddy\Fields\Types\Time',
        'date' => 'Kalnoy\Cruddy\Fields\Types\Date',
        'bool' => 'Kalnoy\Cruddy\Fields\Types\Boolean',
        'relation' => 'Kalnoy\Cruddy\Fields\Types\Relation',
    );

    /**
     * This type will be used if user haven't specified any.
     *
     * @var string
     */
    protected $defaultType = 'string';

    /**
     * Create a new collection.
     *
     * @param  array  $items
     *
     * @return Collection
     */
    public function newCollection(array $items = array())
    {
        return new Collection($items);
    }
}