<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Kalnoy\Cruddy\Schema\Fields\BaseField;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Query\Builder;
use Carbon\Carbon;

/**
 * Date and time field.
 *
 * @since 1.0.0
 */
class DateTime extends BaseField
{
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
        return 'Cruddy.Fields.DateTime';
    }

    /**
     * {@inheritdoc}
     *
     * @return \Carbon\Carbon
     */
    public function parseInputValue($value)
    {
        return empty($value) ? null : Carbon::createFromTimestamp($value);
    }

    /**
     * {@inheritdoc}
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
     * @inheritDoc
     */
    public function getRules($modelKey)
    {
        return array_merge(parent::getRules($modelKey), [ 'date' ]);
    }

}