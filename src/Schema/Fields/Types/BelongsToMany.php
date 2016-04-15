<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Kalnoy\Cruddy\Contracts\Filter;
use Kalnoy\Cruddy\Schema\Fields\BasicRelation;

/**
 * Handles belongs to many relation.
 *
 * @since 1.0.0
 */
class BelongsToMany extends BasicRelation implements Filter
{
    /**
     * @param Model $model
     * @param array $value
     *
     * @return $this
     */
    public function setModelValue($model, $value)
    {
        $value = $this->processInputValue($value);

        $this->newRelationQuery($model)->sync($value);

        return $this;
    }

    /**
     * Get whether the relations works with a collection of models.
     *
     * @return bool
     */
    public function isMultiple()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function applyFilterConstraint(QueryBuilder $builder, $data)
    {
        if (empty($data)) return;

        $builder->whereExists(function ($q) use ($data) {
            $this->initFilterQuery($q, $this->parseData($data));
        });
    }

    /**
     * Init nested query for filter.
     *
     * @param QueryBuilder $query
     * @param array $ids
     *
     * @return void
     */
    protected function initFilterQuery(QueryBuilder $query, array $ids)
    {
        /** @var \Illuminate\Database\Eloquent\Relations\BelongsToMany $relation */
        $relation = $this->newRelationQuery();

        $connection = $query->getConnection();
        $keyName = $connection->raw($relation->getParent()
                                             ->getQualifiedKeyName());

        $query
            ->from($relation->getTable())
            ->select($connection->raw('1'))
            ->where($relation->getForeignKey(), '=', $keyName)
            ->whereIn($relation->getOtherKey(), $ids);
    }
}