<?php namespace Kalnoy\Cruddy\Entity\Fields\Types;

use Kalnoy\Cruddy\Entity\Fields\Input;

class TextArea extends Input {

    /**
     * The input type.
     *
     * @var string
     */
    protected $inputType = 'textarea';

    /**
     * The number of rows for a textarea.
     *
     * @var int
     */
    public $rows = 3;

    /**
     * Convert a field to a configartion array.
     *
     * @return array
     */
    public function toArray()
    {
        return parent::toArray() + array('rows' => $this->rows);
    }
}