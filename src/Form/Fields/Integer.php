<?php

namespace Kalnoy\Cruddy\Form\Fields;

/**
 * Integer field.
 *
 * @package Kalnoy\Cruddy\Form\Fields
 */
class Integer extends BaseNumber
{
    /**
     * @inheritdoc
     *
     * @return int
     */
    public function cast($value)
    {
        return (int)$value;
    }

    /**
     * @inheritdoc
     */
    public function getRules()
    {
        return array_merge(parent::getRules(), [ 'integer' ]);
    }

}