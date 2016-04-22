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
    protected function getModelClass()
    {
        return 'Cruddy.Fields.Boolean';
    }

    /**
     * {@inheritdoc}
     */
    public function getModelValue($model)
    {
        return (bool)parent::getModelValue($model);
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function processInputValue($value)
    {
        return $value === 'true' || $value == '1' || $value === 'on' ? 1 : 0;
    }

    /**
     * {@inheritdoc}
     */
    public function applyFilterConstraint(Builder $builder, $data)
    {
        if ($this->getSettingMode()) {
            $builder->where($this->id, '=', $this->processInputValue($data));
        }
    }

    /**
     * @inheritDoc
     */
    public function getRules($modelKey)
    {
        return array_merge(parent::getRules($modelKey), [ 'boolean' ]);
    }
}