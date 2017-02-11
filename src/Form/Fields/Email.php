<?php

namespace Kalnoy\Cruddy\Form\Fields;

/**
 * Email input field.
 *
 * @package Kalnoy\Cruddy\Form\Fields
 */
class Email extends BaseInput
{
    /**
     * @inheritdoc
     */
    public function getInputType()
    {
        return 'email';
    }

    /**
     * @inheritdoc
     */
    public function getRules()
    {
        return array_merge(parent::getRules(), [ 'email' ]);
    }
}