<?php

namespace Kalnoy\Cruddy\Form\Fields;

/**
 * Date field.
 *
 * @package Kalnoy\Cruddy\Form\Fields
 */
class Date extends DateTime
{
    /**
     * @inheritdoc
     */
    public function getUIModelClass()
    {
        return 'Cruddy.Fields.Date';
    }
}