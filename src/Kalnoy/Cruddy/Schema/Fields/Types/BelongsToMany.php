<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Kalnoy\Cruddy\Schema\Fields\BasicRelation;

/**
 * Handles belongs to many relation.
 */
class BelongsToMany extends BasicRelation {

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

        $builder->whereExists(function ($q) use ($data)
        {
            $connection = $q->getConnection();
            $keyName = $connection->raw($this->relation->getParent()->getQualifiedKeyName());

            $q
                ->from($this->relation->getTable())
                ->select($connection->raw('1'))
                ->where($this->relation->getForeignKey(), $keyName)
                ->where($this->relation->getOtherKey(), $data);
        });

        return $this;
    }
}