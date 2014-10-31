<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Kalnoy\Cruddy\Contracts\Filter;
use Kalnoy\Cruddy\Schema\Fields\BasicRelation;

/**
 * Handles belongs to relation.
 *
 * @since 1.0.0
 */
class BelongsTo extends BasicRelation implements Filter {

    /**
     * {@inheritdoc}
     */
    protected $multiple = false;

    /**
     * {@inheritdoc}
     */
    protected $filterType = self::FILTER_COMPLEX;

    /**
     * @param QueryBuilder $builder
     * @param $data
     *
     * @return void
     */
    public function applyFilterConstraint(QueryBuilder $builder, $data)
    {
        if ($id = array_get($data, 'id'))
        {
            $builder->where($this->relation->getForeignKey(), '=', $id);
        }
    }
}