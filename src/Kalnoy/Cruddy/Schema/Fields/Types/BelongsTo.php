<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Kalnoy\Cruddy\Schema\Fields\BasicRelation;

/**
 * Handles belongs to relation.
 */
class BelongsTo extends BasicRelation {

    /**
     * @inheritdoc
     *
     * @var bool
     */
    protected $multiple = false;

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
        $query->where($this->relation->getForeignKey(), '=', $data['id']);

        return $this;
    }
}