<?php

namespace Kalnoy\Cruddy\Schema\Filters;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Kalnoy\Cruddy\Contracts\SearchProcessor;
use Kalnoy\Cruddy\Schema\BaseCollection;

class Collection extends BaseCollection implements SearchProcessor {

    /**
     * @param EloquentBuilder $builder
     * @param array $input
     */
    public function constraintBuilder(EloquentBuilder $builder, array $input)
    {
        $query = $builder->getQuery();

        /** @var BaseFilter $filter */
        foreach ($this->items as $filter)
        {
            $key = $filter->getDataKey();

            if (isset($input[$key]))
            {
                $filter->applyFilterConstraint($query, $input[$key]);
            }
        }
    }
}