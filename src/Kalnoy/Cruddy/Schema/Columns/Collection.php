<?php

namespace Kalnoy\Cruddy\Schema\Columns;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Kalnoy\Cruddy\Schema\BaseCollection;
use Kalnoy\Cruddy\Schema\AttributeInterface;
use Kalnoy\Cruddy\Repo\SearchProcessorInterface;

/**
 * Columns collection class.
 *
 * This collections implements SearchProcessorInterface for applying order.
 *
 * @since 1.0.0
 */
class Collection extends BaseCollection implements SearchProcessorInterface {

    /**
     * Apply modifications to the query
     *
     * @param EloquentBuilder $builder
     *
     * @return void
     */
    public function modifyQuery(EloquentBuilder $builder)
    {
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