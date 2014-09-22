<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Kalnoy\Cruddy\Schema\Fields\BasicRelation;

/**
 * Handles belongs to many relation.
 *
 * @since 1.0.0
 */
class BelongsToMany extends BasicRelation {

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
    public function filter(QueryBuilder $builder, $data)
    {
        $data = $data['id'];

        $builder->whereExists(function ($q) use ($data)
        {
            $this->initNestedQuery($q, $data);
        });

        return $this;
    }

    /**
     * Init nested query for filter.
     *
     * @param QueryBuilder $query
     * @param mixed        $data
     *
     * @return void
     */
    protected function initNestedQuery(QueryBuilder $query, $data)
    {
        $connection = $query->getConnection();
        $keyName = $connection->raw($this->relation->getParent()->getQualifiedKeyName());

        $query
            ->from($this->relation->getTable())
            ->select($connection->raw('1'))
            ->where($this->relation->getForeignKey(), $keyName)
            ->where($this->relation->getOtherKey(), $data);
    }
}