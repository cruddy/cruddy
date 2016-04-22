<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * Handles morph to many relation.
 *
 * @since 1.0.0
 */
class MorphToMany extends BelongsToMany
{
    /**
     * {@inheritdoc}
     */
    protected function initFilterQuery(QueryBuilder $query, array $ids)
    {
        parent::initFilterQuery($query, $ids);

        /** @var \Illuminate\Database\Eloquent\Relations\MorphToMany $relation */
        $relation = $this->newRelationQuery();

        $query->where($relation->getMorphType(), '=', $relation->getMorphClass());
    }

}