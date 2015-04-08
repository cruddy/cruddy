<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Kalnoy\Cruddy\Contracts\Filter;
use Kalnoy\Cruddy\Schema\Fields\BasicRelation;

/**
 * Handles belongs to many relation.
 *
 * @since 1.0.0
 */
class BelongsToMany extends BasicRelation implements Filter {

    /**
     * @var \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    protected $relation;

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

        $builder->whereExists(function ($q) use ($data)
        {
            $this->initNestedQuery($q, $this->parseData($data));
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
    protected function initNestedQuery(QueryBuilder $query, array $ids)
    {
        $connection = $query->getConnection();
        $keyName = $connection->raw($this->relation->getParent()->getQualifiedKeyName());

        $query
            ->from($this->relation->getTable())
            ->select($connection->raw('1'))
            ->where($this->relation->getForeignKey(), '=', $keyName)
            ->whereIn($this->relation->getOtherKey(), $ids);
    }
}