<?php

namespace Kalnoy\Cruddy\Schema;

use Illuminate\Support\Collection;

/**
 * Base collection for any kind of attributes.
 *
 * @since 1.0.0
 */
class BaseCollection extends Collection {

    /**
     * Add attribute to the collection.
     *
     * @param AttributeInterface $item
     *
     * @return $this
     */
    public function add(AttributeInterface $item)
    {
        $this->items[$item->getId()] = $item;

        return $this;
    }

    /**
     * Extract data from an item or a set of items.
     *
     * @param \Illuminate\Database\Eloquent\Model|array $item
     *
     * @return array
     */
    public function extract($item)
    {
        if (is_array($item) || $item instanceof Collection)
        {
            return $this->extractAll($item);
        }

        $data = [];

        foreach ($this->items as $key => $attribute)
        {
            $value = $attribute->extract($item);

            if ($value instanceof ArraybleInterface)
            {
                $value = $value->toArray();
            }
            elseif (is_object($value))
            {
                $value = (string)$value;
            }

            $data[$key] = $value;
        }

        return $data;
    }

    /**
     * Extract values from a collection of items.
     *
     * @param array $items
     *
     * @return array
     */
    public function extractAll($items)
    {
        if ($items instanceof BaseCollection)
        {
            $items = $items->all();
        }

        return array_map(array($this, 'extract'), $items);
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

        return new static(array_intersect_key($this->items, $columns));
    }

}