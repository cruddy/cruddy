<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Kalnoy\Cruddy\Schema\Fields\BaseNumber;

/**
 * Integer field.
 */
class Integer extends BaseNumber {

    /**
     * @inheritdoc
     *
     * @param mixed $value
     *
     * @return int
     */
    protected function cast($value)
    {
        return (int)$value;
    }

}