<?php

namespace Kalnoy\Cruddy\Schema\Columns;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Kalnoy\Cruddy\Contracts\Column;
use Kalnoy\Cruddy\Entity;
use Kalnoy\Cruddy\Schema\AttributesCollection;
use Kalnoy\Cruddy\Contracts\SearchProcessor;

/**
 * Columns collection class.
 *
 * This collections implements SearchProcessor for applying order.
 *
 * @since 1.0.0
 */
class Collection extends AttributesCollection implements SearchProcessor {

    /**
     * @var string|array
     */
    protected $defaultOrder;

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
     * Get a list of relations that should be eagerly loaded.
     *
     * @return array
     */
    public function eagerLoads($simplified)
    {
        if ($simplified) return $this->entity->eagerLoads();

        $relations = array_reduce($this->items, function ($relations, Column $item)
        {
            return array_merge($relations, $item->eagerLoads());

        }, $this->entity->eagerLoads());

        return array_unique($relations);
    }

    /**
     * Apply order to the query builder.
     *
     * @param QueryBuilder $builder
     * @param array $data
     */
    public function order(QueryBuilder $builder, $data)
    {
        foreach ((array)$data as $id => $direction)
        {
            if (is_numeric($id))
            {
                $id = $direction;
                $direction = null;
            }

            if ($this->has($id))
            {
                /** @var \Kalnoy\Cruddy\Contracts\Column $item */
                $item = $this->get($id);

                if ($item->canOrder()) $item->order($builder, $direction ?: $item->getDefaultOrderDirection());
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function constraintBuilder(EloquentBuilder $builder, array $options)
    {
        if ($relations = $this->eagerLoads(array_get($options, 'simple')))
        {
            $builder->with($relations);
        }

        $query = $builder->getQuery();

        if ($value = array_get($options, 'order', $this->defaultOrder))
        {
            $this->order($query, $value);
        }
    }

}