<?php

namespace Kalnoy\Cruddy\Schema;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Kalnoy\Cruddy\Contracts\Attribute as AttributeContract;
use Kalnoy\Cruddy\Entity;

/**
 * Base attribute class.
 *
 * @property string $help
 * @property string $hide
 * @method $this help(string $value)
 * @method $this hide(bool $value = true)
 *
 * @since 1.0.0
 */
abstract class Attribute extends Entry implements AttributeContract {

    /**
     * @return array
     */
    public function eagerLoads()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function order(QueryBuilder $builder, $direction)
    {
        $builder->orderBy($this->id, $direction);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function canOrder()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'can_order' => $this->canOrder(),

        ] + parent::toArray();
    }
}