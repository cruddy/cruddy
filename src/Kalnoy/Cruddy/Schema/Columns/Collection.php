<?php

namespace Kalnoy\Cruddy\Schema\Columns;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Kalnoy\Cruddy\Schema\BaseCollection;
use Kalnoy\Cruddy\Schema\AttributeInterface;
use Kalnoy\Cruddy\Repo\SearchProcessorInterface;

/**
 * Columns collection class.
 */
class Collection extends BaseCollection implements SearchProcessorInterface {

    /**
     * Apply modifications to the query 
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
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
     * Filter by keywords.
     *
     * @param QueryBuilder $builder
     * @param string       $keywords
     *
     * @return void
     */
    public function filterByKeywords(QueryBuilder $builder, $keywords)
    {
        $builder->whereNested(function ($q) use ($keywords)
        {
            foreach ($this->items as $item)
            {
                if ($item->getFilterType() === AttributeInterface::FILTER_STRING)
                {
                    $item->filter($q, $keywords);
                }
            } 
        });
    }

    /**
     * Apply complex filters.
     *
     * @param QueryBuilder $builder
     * @param array        $data
     *
     * @return void
     */
    public function filterByData(QueryBuilder $builder, array $data)
    {
        foreach ($data as $key => $value)
        {
            if (!empty($value) && $this->has($key))
            {
                $item = $this->get($key);

                if ($item->getFilterType() === AttributeInterface::FILTER_COMPLEX)
                {
                    $item->filter($builder, $value);
                }
            }
        }
    }

    /**
     * @inheritdoc
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param array                                 $options
     *
     * @return void
     */
    public function search(EloquentBuilder $builder, array $options)
    {
        $this->modifyQuery($builder);

        $query = $builder->getQuery();

        if ($value = \array_get($options, 'order'))
        {
            $this->order($query, $value);
        }

        if ($value = \array_get($options, 'keywords'))
        {
            $this->filterByKeywords($query, $value);
        }

        if ($value = \array_get($options, 'filters'))
        {
            $this->filterByData($query, $value);
        }
    }

}