<?php

namespace Kalnoy\Cruddy\Schema\Columns;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
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
     * Apply modifications to the query
     *
     * @param EloquentBuilder $builder
     *
     * @return void
     */
    public function modifyQuery(EloquentBuilder $builder)
    {
        /**
         * @var \Kalnoy\Cruddy\Contracts\Column $item
         */
        foreach ($this->items as $item)
        {
            $item->modifyQuery($builder);
        }
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
        $this->modifyQuery($builder);

        $query = $builder->getQuery();

        if ($value = array_get($options, 'order'))
        {
            $this->order($query, $value);
        }
    }

}