<?php

namespace Kalnoy\Cruddy\Schema\Columns;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Kalnoy\Cruddy\Contracts\Column;
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
     * Get a list of relations that should be eagerly loaded.
     *
     * @return array
     */
    public function eagerLoads()
    {
        $relations = array_reduce($this->items, function ($relations, Column $item)
        {
            return array_merge($relations, $item->eagerLoads());

        }, $this->entity->eagerLoads);

        return array_unique($relations);
    }

    /**
     * Apply order to the query builder.
     *
     * @param QueryBuilder $builder
     * @param array        $data
     *
     * @return void
     */
    public function order(QueryBuilder $builder, array $data)
    {
        foreach ($data as $id => $direction)
        {
            if ($this->has($id))
            {
                $item = $this->get($id);

                if ($item->canOrder()) $item->order($builder, $direction);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function constraintBuilder(EloquentBuilder $builder, array $options)
    {
        if ($relations = $this->eagerLoads())
        {
            $builder->with($relations);
        }

        $query = $builder->getQuery();

        if ($value = array_get($options, 'order'))
        {
            $this->order($query, $value);
        }
    }

}