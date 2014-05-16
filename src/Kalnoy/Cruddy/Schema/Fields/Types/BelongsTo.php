<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Kalnoy\Cruddy\Schema\Fields\BasicRelation;

/**
 * Handles belongs to relation.
 * 
 * @since 1.0.0
 */
class BelongsTo extends BasicRelation {

    /**
     * {@inheritdoc}
     */
    protected $multiple = false;

    /**
     * {@inheritdoc}
     */
    protected $filterType = self::FILTER_COMPLEX;

    /**
     * {@inheritdoc}
     */
    public function filter(QueryBuilder $builder, $data)
    {
        $builder->where($this->relation->getForeignKey(), '=', $data['id']);

        return $this;
    }
}