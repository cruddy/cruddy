<?php

namespace Kalnoy\Cruddy\Schema;

use Illuminate\Support\Collection;
use Illuminate\Support\Contracts\ArrayableInterface;
use Kalnoy\Cruddy\BaseForm;
use Kalnoy\Cruddy\Contracts\Entry;
use Kalnoy\Cruddy\Entity;

/**
 * Base collection for any kind of attributes.
 *
 * @since 1.0.0
 */
class BaseCollection extends Collection {

    /**
     * @var BaseForm
     */
    protected $entity;

    /**
     * @param BaseForm $entity
     * @param array $items
     */
    public function __construct(BaseForm $entity, array $items = [])
    {
        parent::__construct($items);

        $this->entity = $entity;
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
     * @return array
     */
    public function toArray()
    {
        return array_values(parent::toArray());
    }

    /**
     * @return BaseForm
     */
    public function getEntity()
    {
        return $this->entity;
    }

}