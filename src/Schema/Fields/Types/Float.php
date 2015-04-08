<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Kalnoy\Cruddy\Schema\Fields\BaseNumber;

/**
 * Float field.
 *
 * @since 1.0.0
 */
class Float extends BaseNumber {

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