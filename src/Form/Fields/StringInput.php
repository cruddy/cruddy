<?php

namespace Kalnoy\Cruddy\Form\Fields;

/**
 * Basic string field type.
 * 
 * @method $this activeUrl()
 * @method $this url()
 * @method $this alpha()
 * @method $this alphaDash()
 * @method $this alphaNum()
 * @method $this ip()
 *
 * @package Kalnoy\Cruddy\Form\Fields
 */
class StringInput extends BaseInput
{
    /**
     * @var string
     */
    public $mask;

    /**
     * Set the input mask.
     * 
     * @see http://digitalbush.com/projects/masked-input-plugin
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
     * @inheritdoc
     */
    public function getConfig()
    {
        return [
            'mask' => $this->mask,

        ] + parent::getConfig();
    }

    /**
     * @inheritdoc
     */
    public function getRules()
    {
        return array_merge(parent::getRules(), [ 'string' ]);
    }
}