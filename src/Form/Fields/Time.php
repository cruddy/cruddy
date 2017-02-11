<?php

namespace Kalnoy\Cruddy\Form\Fields;

/**
 * Time editing field.
 *
 * @package \Kalnoy\Cruddy\Form\Fields
 */
class Time extends DateTime
{
    /**
     * @return string
     */
    public function getUIModelClass()
    {
        return 'Cruddy.Fields.Time';
    }

}