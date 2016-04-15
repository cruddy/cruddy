<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

/**
 * Time editing field.
 *
 * @since 1.0.0
 */
class Time extends DateTime
{
    /**
     * @return string
     */
    protected function getModelClass()
    {
        return 'Cruddy.Fields.Time';
    }

}