<?php

namespace Kalnoy\Cruddy\Schema\Fields;

use Kalnoy\Cruddy\Schema\BaseCollection;

class Collection extends BaseCollection {

    /**
     * Process input before validation.
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
            if (isset($input[$key]) && !$field->skip($value = $input[$key]))
            {
                $result[$key] = $field->process($value);
            }
        }

        return $result;
    }

    /**
     * Filter input to pass it to the repository.
     *
     * @param array $input
     *
     * @return array
     */
    public function filterInput(array $input)
    {
        $result = [];

        foreach ($input as $key => $value)
        {
            if ($this->items[$key]->isFillable()) $result[$key] = $value;
        }

        return $result;
    }
}