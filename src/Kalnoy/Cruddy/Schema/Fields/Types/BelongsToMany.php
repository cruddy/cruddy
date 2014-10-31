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
     * {@inheritdoc}
     */
    protected $multiple = true;

    /**
     * {@inheritdoc}
     */
    protected $filterType = self::FILTER_COMPLEX;

    /**
     * {@inheritdoc}
     */
    public function applyFilterConstraint(QueryBuilder $builder, $data)
    {
        if ($id = array_get($data, 'id'))
        {
            $builder->whereExists(function ($q) use ($id)
            {
                $this->initNestedQuery($q, $id);
            });
        }
    }

    /**
     * Init nested query for filter.
     *
     * @param QueryBuilder $query
     * @param mixed        $id
     *
     * @return void
     */
    protected function initNestedQuery(QueryBuilder $query, $id)
    {
        $connection = $query->getConnection();
        $keyName = $connection->raw($this->relation->getParent()->getQualifiedKeyName());

        $query
            ->from($this->relation->getTable())
            ->select($connection->raw('1'))
            ->where($this->relation->getForeignKey(), $keyName)
            ->where($this->relation->getOtherKey(), $id);
    }
}