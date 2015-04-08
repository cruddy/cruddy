<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Kalnoy\Cruddy\Contracts\Filter;
use Kalnoy\Cruddy\Schema\Fields\BaseField;

/**
 * Boolean field.
 *
 * @since 1.0.0
 */
class Boolean extends BaseField implements Filter {

    /**
     * @return bool
     */
    public function canOrder()
    {
        return true;
    }

    /**
     * The name of the JavaScript class that is used to render this field.
     *
     * @return string
     */
    protected function modelClass()
    {
        return 'Cruddy.Fields.Boolean';
    }

    /**
     * {@inheritdoc}
     */
    public function extract($model)
    {
        return (bool)parent::extract($model);
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function process($value)
    {
        return $value === 'true' || $value == '1' || $value === 'on' ? 1 : 0;
    }

    /**
     * {@inheritdoc}
     */
    public function keep($value)
    {
        return trim($value) !== '';
    }

    /**
     * {@inheritdoc}
     */
    public function order(Builder $builder, $direction)
    {
        $builder->orderBy($this->id, $direction);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function applyFilterConstraint(Builder $builder, $data)
    {
        if ($this->keep($data))
        {
            $builder->where($this->id, '=', $this->process($data));
        }
    }
}