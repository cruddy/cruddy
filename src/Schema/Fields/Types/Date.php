<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

/**
 * Date field.
 *
 * @since 1.0.0
 */
class Date extends DateTime {

    /**
     * {@inheritdoc}
     */
    protected $type = 'date';

    /**
     * {@inheritdoc}
     */
    protected $class = 'Cruddy.Fields.Date';
}