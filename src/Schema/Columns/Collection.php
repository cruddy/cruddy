<?php

namespace Kalnoy\Cruddy\Schema\Columns;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Arr;
use Kalnoy\Cruddy\Contracts\Column;
use Kalnoy\Cruddy\Entity\Entity;
use Kalnoy\Cruddy\Schema\AttributesCollection;
use Kalnoy\Cruddy\Contracts\SearchProcessor;

/**
 * Columns collection class.
 *
 * This collections implements SearchProcessor for applying order.
 *
 * @since 1.0.0
 */
class Collection extends AttributesCollection implements SearchProcessor
{
    /**
     * @var string|array
     */
    protected $defaultOrder;

    /**
     * @var Entity
     */
    protected $container;

    /**
     * @param Entity $entity
     * @param string|array $defaultOrder
     */
    public function __construct(Entity $entity, $defaultOrder)
    {
        parent::__construct($entity);

        $this->defaultOrder = $defaultOrder;
    }

    /**
     * Apply order to the query builder.
     *
     * @param QueryBuilder $builder
     * @param array $input
     */
    public function order(QueryBuilder $builder, $input)
    {
        foreach ((array)$input as $id => $direction) {
            if (is_numeric($id)) {
                $id = $direction;
                $direction = null;
            }

            if ( ! $this->has($id)) continue;

            /** @var \Kalnoy\Cruddy\Contracts\Column $item */
            $item = $this->get($id);

            if ( ! $item->canOrder()) continue;

            if ( ! $direction) {
                $direction = $item->getDefaultOrderDirection();
            }

            $item->order($builder, $direction);
        }
    }

    /**
     * @inheritdoc
     */
    public function constraintBuilder(EloquentBuilder $builder, array $options)
    {
        if ($value = Arr::get($options, 'order', $this->defaultOrder)) {
            $this->order($builder->getQuery(), $value);
        }
    }

}