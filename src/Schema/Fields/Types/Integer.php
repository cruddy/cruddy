<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Kalnoy\Cruddy\Schema\Fields\BaseNumber;

/**
 * Integer field.
 *
 * @since 1.0.0
 */
class Integer extends BaseNumber
{
    /**
     * {@inheritdoc}
     *
     * @return int
     */
    public function cast($value)
    {
        return (int)$value;
    }

    /**
     * @inheritDoc
     */
    public function getRules($modelKey)
    {
        return array_merge(parent::getRules($modelKey), [ 'integer' ]);
    }

}