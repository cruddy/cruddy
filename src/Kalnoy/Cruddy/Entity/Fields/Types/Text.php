<?php namespace Kalnoy\Cruddy\Entity\Fields\Types;

use Kalnoy\Cruddy\Entity\Fields\Input;

class Text extends Input {

    /**
     * The input type.
     *
     * @var string
     */
    protected $inputType = 'text';

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
}