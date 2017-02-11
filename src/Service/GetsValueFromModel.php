<?php

namespace Kalnoy\Cruddy\Service;

use Kalnoy\Cruddy\Form\Fields\BaseField;

trait GetsValueFromModel
{
    /**
     * @var callback
     */
    public $getter;

    /**
     * @var string
     */
    public $modelAttribute;

    /**
     * Set the callback that will be used to retrieve a value from the model.
     *
     * ```php
     * $form->string('foo')->getter(function ($model, $attr) {
     *     return $model->$attr;
     * });
     * 
     * $form->string('foo')->getter('Helpers::processString');
     * ```
     *
     * @param callback $callback
     *
     * @return $this
     */
    public function getter(callable $callback)
    {
        $this->getter = $callback;

        return $this;
    }

    /**
     * Set model attribute name that will be used instead of field id.
     *
     * @param string $value
     *
     * @return BaseField
     */
    public function modelAttribute($value)
    {
        $this->modelAttribute = $value;

        return $this;
    }

    /**
     * @return callback
     */
    public function getGetter()
    {
        return $this->getter ?: $this->getDefaultGetter();
    }

    /**
     * Retrieve the value of this field from model.
     * 
     * @param mixed $model
     * 
     * @see getter To define custom getter
     * 
     * @return mixed
     */
    public function getModelValue($model)
    {
        $attribute = $this->getModelAttribute();

        return call_user_func($this->getGetter(), $model, $attribute);
    }

    /**
     * Get model attribute name.
     *
     * @return string
     */
    public function getModelAttribute()
    {
        return $this->modelAttribute ?: $this->getId();
    }

    /**
     * @return callback
     */
    abstract protected function getDefaultGetter();
}