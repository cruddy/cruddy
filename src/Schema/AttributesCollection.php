<?php

namespace Kalnoy\Cruddy\Schema;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Contracts\ArrayableInterface;

class AttributesCollection extends BaseCollection {

    /**
     * Extract data from an item or a set of items.
     *
     * @param Model $model
     *
     * @return array
     */
    public function extract(Model $model)
    {
        $data = [];

        /** @var Attribute $attribute */
        foreach ($this->items as $key => $attribute)
        {
            $value = $attribute->extract($model);

            if ($value instanceof ArrayableInterface)
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

}