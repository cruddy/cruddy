<?php

namespace Kalnoy\Cruddy\Schema;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

class AttributesCollection extends BaseCollection
{
    /**
     * Extract data from an item or a set of items.
     *
     * @param mixed $model
     *
     * @return array
     */
    public function extract($model)
    {
        $data = [ ];

        /** @var Attribute $attribute */
        foreach ($this->items as $key => $attribute) {
            $value = $attribute->extract($model);

            if ($value instanceof Arrayable) {
                $value = $value->toArray();
            } elseif (is_object($value)) {
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
        if ($items instanceof Collection) {
            $items = $items->all();
        }

        return array_map(array( $this, 'extract' ), $items);
    }

}