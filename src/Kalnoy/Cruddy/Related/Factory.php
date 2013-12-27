<?php namespace Kalnoy\Cruddy\Related;

use Kalnoy\Cruddy\AttributeFactory;

class Factory extends AttributeFactory {

    /**
     * Built-in field types.
     *
     * @var array
     */
    protected $types = array(
        'one' => 'Kalnoy\Cruddy\Related\Types\One',
    );

    /**
     * This type will be used if user haven't specified any.
     *
     * @var string
     */
    protected $defaultType = 'one';

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