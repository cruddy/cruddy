<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Kalnoy\Cruddy\Schema\Fields\BaseNumber;

/**
 * Integer field.
 *
 * @since 1.0.0
 */
class Integer extends BaseNumber {

    /**
     * {@inheritdoc}
     *
     * @return int
     */
    protected function cast($value)
    {
        return (int)$value;
    }

}