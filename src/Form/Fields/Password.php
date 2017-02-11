<?php

namespace Kalnoy\Cruddy\Form\Fields;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Query\Builder;
use Kalnoy\Cruddy\Form\Fields\BaseInput;
use Kalnoy\Cruddy\Form\BaseTextField;

/**
 * Password field type.
 *
 * Password field will not expose a value and will always be empty.
 *
 * @package Kalnoy\Cruddy\Form\Fields
 */
class Password extends BaseInput
{
    /**
     * @var bool
     */
    public $hash = false;

    /**
     * Hash the password before setting on model.
     * 
     * @return $this
     */
    public function hash()
    {
        $this->hash = true;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getInputType()
    {
        return 'password';
    }

    /**
     * @inheritdoc
     */
    public function getModelValue($model)
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function setModelValue($model, $value)
    {
        if (is_null($value)) {
            return $this;
        }

        return parent::setModelValue($model, $value);
    }

    /**
     * @param array $value
     *
     * @return string
     */
    public function processValueBeforeSetting($value)
    {
        $value = parent::processValueBeforeSetting($value);

        return $this->hash ? bcrypt($value) : $value;
    }
}