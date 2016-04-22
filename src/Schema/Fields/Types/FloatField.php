<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Kalnoy\Cruddy\Schema\Fields\BaseNumber;

/**
 * FloatField field.
 *
 * @since 1.0.0
 */
class FloatField extends BaseNumber
{
    /**
     * {@inheritdoc}
     *
     * @return float
     */
    public function cast($value)
    {
        return (float)$value;
    }

    /**
     * @inheritDoc
     */
    public function getRules($modelKey)
    {
        return array_merge(parent::getRules($modelKey), [ 'numeric' ]);
    }

}