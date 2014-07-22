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
     * {@inheritdoc}
     */
    public function toArray()
    {
        return
        [
            'mask' => $this->mask,
            
        ] + parent::toArray();
    }
}