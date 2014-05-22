<?php

namespace Kalnoy\Cruddy\Schema\Fields;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Kalnoy\Cruddy\Schema\Attribute;
use Kalnoy\Cruddy\Schema\FieldInterface;

/**
 * A base class for all fields.
 * 
 * @since 1.0.0
 */
abstract class BaseField extends Attribute implements FieldInterface {
    
    /**
     * Get whether the field is required.
     * 
     * Field can be required based on the state of the model. {@see $disabled}.
     *
     * @var bool|string
     */
    public $required = false;

    /**
     * Whether the field is unique for instance and therefore cannot be copied.
     *
     * @var bool
     */
    public $unique = false;

    /**
     * Whether the editing is disabled.
     * 
     * The field can be disabled for specified state of the model; if set to
     * {@see \Kalnoy\Cruddy\Schema\BaseSchema::WHEN_EXISTS} the field will be
     * disabled when model is exists; if set to
     * {@see \Kalnoy\Cruddy\Schema\BaseSchema::WHEN_NEW} the field will not be
     * shown at all when model is new.
     * 
     * @var bool|string
     */
    public $disabled = false;

    /**
     * The label.
     *
     * @var string
     */
    protected $label;

    /**
     * The filter type.
     *
     * @var string
     */
    protected $filterType = self::FILTER_NONE;

    /**
     * {@inheritdoc}
     */
    public function extract(Eloquent $model)
    {
        return $model->getAttribute($this->id);
    }

    /**
     * {@inheritdoc}
     */
    public function extractForColumn(Eloquent $model)
    {
        return $this->extract($model);
    }

    /**
     * {@inheritdoc}
     */
    public function process($value)
    {
        return $value;
    }

    /**
     * Set required value.
     *
     * @param bool $value
     *
     * @return $this
     */
    public function required($value = true)
    {
        $this->required = $value;

        return $this;
    }

    /**
     * Set unique value.
     *
     * @param bool $value
     *
     * @return $this
     */
    public function unique($value = true)
    {
        $this->unique = $value;

        return $this;
    }

    /**
     * Set disabled value.
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
     * Set label.
     *
     * @param string $value
     *
     * @return $this
     */
    public function label($value)
    {
        $this->label = $value;

        return $this;
    }

    /**
     * Get field label.
     *
     * @return string
     */
    public function getLabel()
    {
        if ($this->label === null)
        {
            $this->label = $this->generateLabel();
        }

        return $this->label;
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
     * {@inheritdoc}
     */
    public function filter(QueryBuilder $builder, $data)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterType()
    {
        return $this->filterType;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return
        [
            'required' => $this->required,
            'unique' => $this->unique,
            'disabled' => $this->disabled,
            'label' => $this->getLabel(),
            'filter_type' => $this->getFilterType(),

        ] + parent::toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function keep($value)
    {
        return true;
    }

    /**
     * Get whether the field is disabled for specified action.
     * 
     * @param string $action
     * 
     * @return bool
     */
    public function isDisabled($action)
    {
        return $this->disabled === true or $this->disabled === $action;
    }

    /**
     * {@inheritdoc}
     */
    public function sendToRepository($action)
    {
        return ! $this->isDisabled($action);
    }

}