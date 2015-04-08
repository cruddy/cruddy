<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

/**
 * Date field.
 *
 * @since 1.0.0
 */
class Date extends DateTime {

    /**
     * The name of the JavaScript class that is used to render this field.
     *
     * @return string
     */
    protected function modelClass()
    {
        return 'Cruddy.Fields.Date';
    }
}