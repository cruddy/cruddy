<?php

namespace Kalnoy\Cruddy\Form\Fields;

use Carbon\Carbon;

/**
 * Date and time field.
 * 
 * @method $this before($date)
 *
 * @package Kalnoy\Cruddy\Form\Fields
 */
class DateTime extends BaseField
{
    /**
     * The name of the JavaScript class that is used to render this field.
     *
     * @return string
     */
    public function getUIModelClass()
    {
        return 'Cruddy.Fields.DateTime';
    }

    /**
     * @inheritdoc
     *
     * @return \Carbon\Carbon
     */
    public function processValueBeforeValidating($value)
    {
        return empty($value) ? null : Carbon::createFromTimestamp($value);
    }

    /**
     * @inheritdoc
     *
     * @return int
     */
    public function getModelValue($model)
    {
        $value = parent::getModelValue($model);

        if ($value === null) {
            return null;
        }

        if ( ! $value instanceof Carbon) {
            $value = new Carbon($value);
        }

        return $value->getTimestamp();
    }

    /**
     * @inheritdoc
     */
    public function getRules()
    {
        return array_merge(parent::getRules(), [ 'date' ]);
    }

}