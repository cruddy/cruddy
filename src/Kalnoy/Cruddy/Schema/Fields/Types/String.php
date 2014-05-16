<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Kalnoy\Cruddy\Schema\Fields\BaseTextField;

/**
 * Basic string field type.
 * 
 * @since 1.0.0
 */
class String extends BaseTextField {

    /**
     * @inheritdoc
     *
     * @var string
     */
    protected $type = 'string';

    /**
     * The input mask.
     *
     * {@link http://digitalbush.com/projects/masked-input-plugin}
     *
     * @var string
     */
    public $mask;

    /**
     * The input placeholder.
     *
     * @var string
     */
    public $placeholder;

    /**
     * Set the mask.
     *
     * @param string $value
     *
     * @return $this
     */
    public function mask($value)
    {
        $this->mask = $value;

        return $this;
    }

    /**
     * Set placeholder value.
     *
     * @param string $value
     *
     * @return $this
     */
    public function placeholder($value)
    {
        $this->placeholder = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return
        [
            'mask' => $this->mask,
            'placeholder' => $this->placeholder ? \Kalnoy\Cruddy\try_trans($this->placeholder) : null,
            
        ] + parent::toArray();
    }
}