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
     * @var bool
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
     * @inheritdoc
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function skip($value)
    {
        return false;
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
            if (null === $this->label = $this->translate('fields'))
            {
                $this->label = \Kalnoy\Cruddy\ucfirst(\Kalnoy\Cruddy\prettify_string($this->id));
            }
        }

        return $this->label;
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
        return ! $this->disabled and $this->entity->getRepository()->isFillable($this->id);
    }

}