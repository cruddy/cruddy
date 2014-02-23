<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Kalnoy\Cruddy\Schema\Fields\BasicRelation;

/**
 * Handles has many relation.
 */
class HasMany extends BasicRelation {

    /**
     * @inheritdoc
     *
     * @var bool
     */
    protected $multiple = true;

    /**
     * @inheritdoc
     *
     * @var string
     */
    protected $filterType = self::FILTER_COMPLEX;

    /**
     * @inheritdoc
     *
     * @param \Illuminate\Database\Query\Builder $builder
     * @param array                              $data
     *
     * @return $this
     */
    public function filter(QueryBuilder $builder, $data)
    {
        $data = $data['id'];

        $query->whereExists(function ($q) use ($data)
        {
            $this->filterInnerQuery($q, $data);
        });

        return $this;
    }

    /**
     * Setup inner query for filtering.
     *
     * @param $q
     * @param $data
     *
     * @return void
     */
    protected function filterInnerQuery($q, $data)
    {
        $connection = $q->getConnection();
        $keyName = $connection->raw($this->relation->getParent()->getQualifiedKeyName());

        $q
            ->from($this->relation->getBaseQuery()->from)
            ->select($connection->raw('1'))
            ->where($this->relation->getForeignKey(), $keyName);
    }
}