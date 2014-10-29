<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

/**
 * Time editing field.
 *
 * @since 1.0.0
 */
class Time extends DateTime {

    /**
     * {@inheritdoc}
     */
    protected $type = 'time';

    /**
     * {@inheritdoc}
     */
    protected $class = 'Cruddy.Fields.Time';
}