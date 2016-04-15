<?php

namespace Kalnoy\Cruddy\Schema\Fields;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Arr;
use Kalnoy\Cruddy\Entity;
use Kalnoy\Cruddy\Helpers;
use Kalnoy\Cruddy\Schema\Attribute;
use Kalnoy\Cruddy\Contracts\Field;

/**
 * A base class for all fields.
 *
 * @property string $label
 * @method $this label(string $value)
 * @property bool $required
 * @method $this required(bool $value = true)
 * @property bool $unique
 * @method $this unique(bool $value = true)
 * @property string $disable
 * @method $this disable(mixed $value = true)
 * @property string $modelAttribute
 * @method $this modelAttribute(string $value)
 * 
 * @property callback $setter
 * @property callback $getter
 * @method $this setter(callback $callback)
 * @method $this getter(callback $callback)
 *
 * @since 1.0.0
 */
abstract class BaseField extends Attribute implements Field
{
    /**
     * {@inheritdoc}
     */
    public function getModelValue($model)
    {
        $attribute = $this->getModelAttributeName();

        return call_user_func($this->getGetter(), $model, $attribute);
    }

    /**
     * {@inheritdoc}
     */
    public function setModelValue($model, $value)
    {
        $attribute = $this->getModelAttributeName();
        $value = $this->processInputValue($value);

        call_user_func($this->getSetter(), $model, $value, $attribute);

        return $this;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    protected function processInputValue($value)
    {
        return $this->parseInputValue($value);
    }

    /**
     * @inheritDoc
     */
    public function validate($value)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getModelValueForColumn($model)
    {
        return $this->getModelValue($model);
    }

    /**
     * {@inheritdoc}
     */
    public function parseInputValue($value)
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
        if ($label = $this->get('label')) {
            return Helpers::tryTranslate($label);
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
        return $this->translate('fields') ?: parent::generateLabel();
    }

    /**
     * @return bool|string
     */
    protected function isRequired()
    {
        $required = $this->get('required');

        if ($required !== null) return $required;

        return $this->form->getValidator()->getRequiredState($this->id);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'required' => $this->isRequired(),
            'unique' => $this->get('unique'),
            'disabled' => $this->get('disable'),
            'label' => $this->getLabel(),

        ] + parent::toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingMode()
    {
        return self::MODE_BEFORE_SAVE;
    }

    /**
     * @inheritDoc
     */
    public function isDisabled($model)
    {
        $disabled = $this->get('disable', false);

        if ($this->form instanceof Entity) {
            $action = $this->form->getActionFromModel($model);

            return $disabled === true || $disabled === $action;
        }

        return $disabled;
    }

    /**
     * Get model attribute name.
     *
     * @return string
     */
    protected function getModelAttributeName()
    {
        return $this->get('modelAttribute') ?: $this->id;
    }

    /**
     * @return callback
     */
    protected function getGetter()
    {
        if ($getter = $this->get('getter')) {
            return $getter;
        }
        
        return [ $this->form, 'getModelAttributeValue' ];
    }

    /**
     * @return callback
     */
    protected function getSetter()
    {
        if ($setter = $this->get('setter')) {
            return $setter;
        }
        
        return [ $this->form, 'setModelAttributeValue' ];
    }

}