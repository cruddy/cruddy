<?php

namespace Kalnoy\Cruddy\Schema;

use Illuminate\Contracts\Support\Arrayable as ArrayableContract;
use Illuminate\Support\Collection;
use Kalnoy\Cruddy\Contracts\Entry;

/**
 * Base collection for any kind of attributes.
 *
 * @since 1.0.0
 */
class BaseCollection implements ArrayableContract
{
    /**
     * @var array
     */
    protected $items = [];

    /**
     * @var mixed
     */
    protected $container;

    /**
     * BaseCollection constructor.
     *
     * @param $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * Add entry.
     *
     * @param Entry $item
     *
     * @return $this
     */
    public function push(Entry $item)
    {
        $this->items[$item->getId()] = $item;

        return $this;
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function has($id)
    {
        return isset($this->items[$id]);
    }

    /**
     * Get an entry by id.
     *
     * @param string $id
     *
     * @return null|Entry
     */
    public function get($id)
    {
        return $this->has($id) ? $this->items[$id] : null;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array_values(array_map(function (Entry $entry) {
            return $entry->toArray();
        }, $this->items));
    }

    /**
     * @return mixed
     */
    public function getContainer()
    {
        return $this->container;
    }
}