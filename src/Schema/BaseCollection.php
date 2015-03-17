<?php

namespace Kalnoy\Cruddy\Schema;

use Illuminate\Support\Collection;
use Illuminate\Support\Contracts\ArrayableInterface;
use Kalnoy\Cruddy\Contracts\Entry;
use Kalnoy\Cruddy\Entity;

/**
 * Base collection for any kind of attributes.
 *
 * @since 1.0.0
 */
class BaseCollection extends Collection {

    /**
     * @var Entity
     */
    protected $entity;

    /**
     * @param Entity $entity
     */
    public function __construct(Entity $entity, array $items = [])
    {
        $this->entity = $entity;

        parent::__construct($items);
    }

    /**
     * Add attribute to the collection.
     *
     * @param Entry $item
     *
     * @return $this
     */
    public function add(Entry $item)
    {
        $this->items[$item->getId()] = $item;

        return $this;
    }

    /**
     * Get new collection that contains only items specified in an array.
     *
     * @param array $columns
     *
     * @return BaseCollection
     */
    public function only(array $columns)
    {
        if ($columns == array('*')) return $this;

        $columns = array_combine($columns, $columns);

        return new static($this->entity, array_intersect_key($this->items, $columns));
    }

    /**
     * @return array
     */
    public function export()
    {
        return array_values($this->toArray());
    }

    /**
     * @return Entity
     */
    public function getEntity()
    {
        return $this->entity;
    }

}