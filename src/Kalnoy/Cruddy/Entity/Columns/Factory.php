<?php namespace Kalnoy\Cruddy\Entity\Columns;

use Kalnoy\Cruddy\Entity\Attribute\Factory as AttributeFactory;

class Factory extends AttributeFactory {

    /**
     * Built-in column types.
     *
     * @var array
     */
    protected $types = array(
        'field' => 'Kalnoy\Cruddy\Entity\Columns\Types\Field',
        'computed' => 'Kalnoy\Cruddy\Entity\Columns\Types\Computed',
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