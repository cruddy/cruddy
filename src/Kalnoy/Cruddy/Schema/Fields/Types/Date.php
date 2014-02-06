<?php namespace Kalnoy\Cruddy\Schema\Fields\Types;

class Date extends DateTime {

    /**
     * @inheritdoc
     *
     * @var string
     */
    protected $type = 'date';

    /**
     * @inheritdoc
     *
     * @var string
     */
    public $format = 'DD.MM.YYYY';
}