<?php

namespace Kalnoy\Cruddy\Form\Fields;

/**
 * FloatField field.
 *
 * @package Kalnoy\Cruddy\Form\Fields
 */
class FloatInput extends BaseNumber
{
    /**
     * @inheritdoc
     *
     * @return float
     */
    public function cast($value)
    {
        return (float)$value;
    }

    /**
     * @inheritdoc
     */
    public function getRules()
    {
        return array_merge(parent::getRules(), [ 'numeric' ]);
    }

}