<?php namespace Kalnoy\Cruddy\Columns;

use Kalnoy\Cruddy\AttributeFactory;

class Factory extends AttributeFactory {

    /**
     * Built-in column types.
     *
     * @var array
     */
    protected $types = array(
        'field' => 'Kalnoy\Cruddy\Columns\Types\Field',
        'computed' => 'Kalnoy\Cruddy\Columns\Types\Computed',
    );

    /**
     * This type will be used if user haven't specified any.
     *
     * @var string
     */
    protected $defaultType = 'field';

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