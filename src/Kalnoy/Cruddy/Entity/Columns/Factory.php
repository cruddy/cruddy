<?php namespace Kalnoy\Cruddy\Entity\Columns;

use Kalnoy\Cruddy\Entity\Attribute\Factory as AttributeFactory;
use Kalnoy\Cruddy\Entity\Entity;

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

    protected $generate = ['primary'];

    /**
     * Generate a primary column that is required.
     *
     * @param Entity     $entity
     * @param Collection $collection
     */
    public function generatePrimary(Entity $entity, Collection $collection)
    {
        $primaryKey = $entity->form()->instance()->getKeyName();

        if (!$collection->has($primaryKey))
        {
            $column = $this->create($entity, 'field', $primaryKey, ['visible' => false]);

            $collection->put($primaryKey, $column);
        }
    }

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