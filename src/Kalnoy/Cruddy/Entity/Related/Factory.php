<?php namespace Kalnoy\Cruddy\Entity\Related;

use Kalnoy\Cruddy\Entity\Attribute\Factory as AttributeFactory;

class Factory extends AttributeFactory {

    /**
     * Built-in field types.
     *
     * @var array
     */
    protected $types = array(
        'one' => 'Kalnoy\Cruddy\Entity\Related\Types\One',
        'morphOne' => 'Kalnoy\Cruddy\Entity\Related\Types\MorphOne',
    );

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