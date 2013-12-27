<?php namespace Kalnoy\Cruddy\Fields\Types;

use Kalnoy\Cruddy\Fields\Input;

class Text extends Input {

    /**
     * The input type.
     *
     * @var string
     */
    protected $inputType = 'text';

    /**
     * Input pattern attribute.
     *
     * @var string
     */
    public $pattern;

    /**
     * Process value.
     *
     * @param  string $value
     *
     * @return string
     */
    public function process($value)
    {
        return trim($value);
    }

    /**
     * Convert field to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return parent::toArray() + array('pattern' => $this->pattern);
    }
}