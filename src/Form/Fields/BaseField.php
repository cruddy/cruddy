<?php

namespace Kalnoy\Cruddy\Form\Fields;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Kalnoy\Cruddy\Entity\Entity;
use Kalnoy\Cruddy\Form\BaseForm;
use Kalnoy\Cruddy\Helpers;
use Kalnoy\Cruddy\Schema\Attribute;
use Kalnoy\Cruddy\Contracts\Field;
use Kalnoy\Cruddy\Service\BaseItem;
use Kalnoy\Cruddy\Service\GetsValueFromModel;

/**
 * A base class for all fields.
 *
 * @method $this required()
 * @method $this unique()
 * @method $this nullable()
 *
 * @package Kalnoy\Cruddy\Form\Fields
 */
abstract class BaseField extends BaseItem
{
    use GetsValueFromModel;

    /**
     * @var BaseForm
     */
    protected $owner;

    /**
     * @var array
     */
    protected $rules = [];

    /**
     * @var callback
     */
    public $setter;

    /**
     * @var string
     */
    public $label;

    /**
     * @var bool
     */
    public $disabled = false;

    /**
     * BaseField constructor.
     *
     * @param BaseForm $owner
     * @param string $id
     */
    public function __construct(BaseForm $owner, $id)
    {
        parent::__construct($owner, $id);
    }

    /**
     * Set the field label.
     *
     * @param string $value Actual field label or language line key
     *
     * @return $this
     */
    public function label($value)
    {
        $this->label = $value;

        return $this;
    }

    /**
     * Set callback that will be used to set data on model.
     *
     * ```php
     * $form->string('foo')->setter(function ($model, $value, $attr) {
     *     $model->$attr = $value;
     * });
     * ```
     *
     * @param callback $callback
     *
     * @return $this
     */
    public function setter($callback)
    {
        $this->setter = $callback;

        return $this;
    }

    /**
     * Disable the field.
     *
     * Disabled field won't be editable.
     *
     * @param bool $value
     *
     * @return $this
     */
    public function disable($value = true)
    {
        $this->disabled = $value;

        return $this;
    }

    /**
     * Set the value on model.
     *
     * Value is pre-processed using {@see processValueBeforeSetting} function.
     *
     * @param mixed $model
     * @param mixed $value
     *
     * @see setter To define custom setter
     *
     * @return $this
     */
    public function setModelValue($model, $value)
    {
        $attribute = $this->getModelAttribute();

        $value = $this->processValueBeforeSetting($value);

        call_user_func($this->getSetter(), $model, $value, $attribute);

        return $this;
    }

    /**
     * Process value before it'll be set on model.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    protected function processValueBeforeSetting($value)
    {
        return $this->processValueBeforeValidating($value);
    }

    /**
     * Process value before sending it to the validator.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function processValueBeforeValidating($value)
    {
        return $value;
    }

    /**
     * Get field label.
     *
     * @return string
     */
    public function getLabel()
    {
        if ($this->label) {
            return Helpers::tryTranslate($this->label);
        }

        return $this->generateLabel();
    }

    /**
     * Generate a label.
     *
     * @return string
     */
    protected function generateLabel()
    {
        return $this->owner->translate("fields.{$this->id}")
            ?: Helpers::labelFromId($this->id);
    }

    /**
     * @return callback
     */
    public function getSetter()
    {
        return $this->setter ?: [ $this->owner, 'setModelAttributeValue' ];
    }

    /**
     * @return bool
     */
    public function isDisabled()
    {
        return $this->disabled;
    }

    /**
     * @return BaseForm
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @inheritdoc
     */
    public function getConfig()
    {
        return [
            'rules' => $this->rules,
            'label' => $this->getLabel(),
            'disabled' => $this->isDisabled(),
        ] + parent::getConfig();
    }

    /**
     * @inheritdoc
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function hasRule($name)
    {
        return isset($this->rules[$name]);
    }

    /**
     * @inheritdoc
     *
     * @return $this
     */
    public function __call($name, $arguments)
    {
        $this->rules[Str::snake($name)] = $arguments;

        return $this;
    }

    /**
     * @return array
     */
    protected function getDefaultGetter()
    {
        return [ $this->owner, 'getModelAttributeValue' ];
    }

}