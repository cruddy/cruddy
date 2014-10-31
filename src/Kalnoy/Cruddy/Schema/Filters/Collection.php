<?php

namespace Kalnoy\Cruddy\Schema\Filters;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Kalnoy\Cruddy\Contracts\Filter;
use Kalnoy\Cruddy\Contracts\SearchProcessor;
use Kalnoy\Cruddy\Schema\BaseCollection;

class Collection extends BaseCollection implements SearchProcessor {

    /**
     * Apply complex filters.
     *
     * @param \Illuminate\Database\Query\Builder $builder
     * @param array                              $data
     *
     * @return void
     */
    protected function applyFilterConstraints(QueryBuilder $builder, array $data)
    {
        foreach ($data as $key => $value)
        {
            if ( ! empty($value) && $this->has($key))
            {
                /**
                 * @var Filter $item
                 */
                $item = $this->get($key);

                if ($item instanceof Filter)
                {
                    $item->applyFilterConstraint($builder, $value);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function constraintBuilder(EloquentBuilder $builder, array $options)
    {
        if ($value = array_get($options, 'filters'))
        {
            $this->applyFilterConstraints($builder->getQuery(), $value);
        }
    }
}