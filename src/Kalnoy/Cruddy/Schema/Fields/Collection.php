<?php

namespace Kalnoy\Cruddy\Schema\Fields;

use Kalnoy\Cruddy\Schema\BaseCollection;

class Collection extends BaseCollection {

    /**
     * Process input.
     *
     * @param array $input
     *
     * @return array
     */
    public function process(array $input)
    {
        $result = [];

        foreach ($this->items as $key => $field)
        {
            if ($field->isFillable() && isset($input[$key]) && !$field->skip($value = $input[$key]))
            {
                $result[$key] = $field->process($value);
            }
        }

        return $result;
    }
}