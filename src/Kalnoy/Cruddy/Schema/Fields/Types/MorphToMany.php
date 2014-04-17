<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * Handles morph to many relation.
 */
class MorphToMany extends BelongsToMany {

    /**
     * @inheritdoc
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param mixed                              $data
     *
     * @return void
     */
    protected function initNestedQuery(QueryBuilder $query, $data)
    {
        parent::initNestedQuery($query, $data);

        $query->where($this->relation->getMorphType(), '=', $this->relation->getMorphClass());
    }
}