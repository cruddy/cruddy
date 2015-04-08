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
class DateTime extends BaseField {

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
        return 'Cruddy.Fields.DateTime';
    }

    /**
     * {@inheritdoc}
     *
     * @return \Carbon\Carbon
     */
    public function process($value)
    {
        return empty($value) ? null : Carbon::createFromTimestamp($value);
    }

    /**
     * {@inheritdoc}
     *
     * @return int
     */
    public function extract($model)
    {
        $value = parent::extract($model);

        if ($value === null) return null;

        if ( ! $value instanceof Carbon) $value = new Carbon($value);

        return $value->getTimestamp();
    }

    /**
     * {@inheritdoc}
     */
    public function order(Builder $builder, $direction)
    {
        $builder->orderBy($this->id, $direction);

        return $this;
    }

}