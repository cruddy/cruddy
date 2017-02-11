<?php

namespace Kalnoy\Cruddy\Form\Fields;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Kalnoy\Cruddy\Contracts\Filter;
use Kalnoy\Cruddy\Form\Fields\BaseField;

/**
 * Boolean field.
 *
 * @package Kalnoy\Cruddy\Form\Fields
 */
class Boolean extends BaseField
{
    /**
     * The name of the JavaScript class that is used to render this field.
     *
     * @return string
     */
    public function getUIModelClass()
    {
        return 'Cruddy.Fields.Boolean';
    }

    /**
     * @inheritdoc
     */
    public function getModelValue($model)
    {
        return (bool)parent::getModelValue($model);
    }

    /**
     * @inheritdoc
     *
     * @return bool
     */
    public function processValueBeforeSetting($value)
    {
        return $value === 'true' || $value == '1' || $value === 'on' ? 1 : 0;
    }

    /**
     * @inheritdoc
     */
    public function getRules()
    {
        return array_merge(parent::getRules(), [ 'boolean' ]);
    }
}