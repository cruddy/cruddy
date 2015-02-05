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
     * {@inheritdoc}
     */
    protected $class = 'Cruddy.Fields.DateTime';

    /**
     * {@inheritdoc}
     */
    protected $type = 'datetime';

    /**
     * @var bool
     */
    protected $canOrder = true;

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
    public function extract(Eloquent $model)
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