<?php namespace Kalnoy\Cruddy\Schema\Fields\Types;

class Time extends DateTime {

    /**
     * @inheritdoc
     *
     * @var string
     */
    protected $type = 'time';

    /**
     * @inheritdoc
     *
     * @var string
     */
    public $format = 'HH:mm';
}