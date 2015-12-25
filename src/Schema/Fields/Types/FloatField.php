<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Kalnoy\Cruddy\Schema\Fields\BaseNumber;

/**
 * FloatField field.
 *
 * @since 1.0.0
 */
class FloatField extends BaseNumber {

    /**
     * {@inheritdoc}
     *
     * @return float
     */
    protected function cast($value)
    {
        return (float)$value;
    }

}