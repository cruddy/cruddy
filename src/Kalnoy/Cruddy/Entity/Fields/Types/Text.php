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
     * The input mask.
     *
     * {@link http://digitalbush.com/projects/masked-input-plugin}
     *
     * @var  [type]
     */
    public $mask;

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

    public function toArray()
    {
        return
        [
            'mask' => $this->mask,
            
        ] + parent::toArray();
    }
}