<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Kalnoy\Cruddy\Schema\Fields\BaseNumber;

/**
 * Float field.
 */
class Float extends BaseNumber {

    /**
     * @inheritdoc
     *
     * @param mixed $value
     *
     * @return int
     */
    protected function cast($value)
    {
        return (float)$value;
    }

}