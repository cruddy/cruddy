<?php

namespace Kalnoy\Cruddy\Schema\Fields;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Kalnoy\Cruddy\Schema\Attribute;
use Kalnoy\Cruddy\Schema\FieldInterface;

abstract class BaseField extends Attribute implements FieldInterface {
    
    /**
     * Get whether the field is required.
     *
     * @var bool
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
     * @inheritdoc
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return mixed
     */
    public function extract(Eloquent $model)
    {
        return $model->getAttribute($this->id);
    }

    /**
     * @inheritdoc
     *
     * @param mixed $value
     *
     * @return mixed
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
        if (null === $label = $this->translate('fields'))
        {
            $label = \Kalnoy\Cruddy\ucfirst(\Kalnoy\Cruddy\prettify_string($this->id));
        }

        return $label;
    }

    /**
     * @inheritdoc
     *
     * @param \Illuminate\Database\Query\Builder $builder
     * @param mixed                              $data
     *
     * @return $this
     */
    public function filter(QueryBuilder $builder, $data)
    {
        return $this;
    }

    /**
     * @inheritdoc
     *
     * @return string
     */
    public function getFilterType()
    {
        return $this->filterType;
    }

    /**
     * @inheritdoc
     *
     * @return array
     */
    public function toArray()
    {
        return
        [
            'required' => $this->required,
            'unique' => $this->unique,
            'disabled' => $this->disabled,
            'fillable' => $this->isFillable(),
            'label' => $this->getLabel(),
            'filter_type' => $this->getFilterType(),

        ] + parent::toArray();
    }

    /**
     * Get whether the field is fillable.
     *
     * @return bool
     */
    public function isFillable()
    {
        return $this->entity->getRepository()->isFillable($this->id);
    }

    /**
     * @inheritdoc
     *
     * @return bool
     */
    public function keep($value)
    {
        return true;
    }

    /**
     * @inheritdoc
     *
     * @param string $action
     *
     * @return bool
     */
    public function sendToRepository($action)
    {
        return $this->isFillable() and $this->disabled !== true and $this->disabled !== $action;
    }

}