<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Kalnoy\Cruddy\Contracts\Filter;
use Kalnoy\Cruddy\Schema\Fields\BasicRelation;

/**
 * Handles belongs to relation.
 *
 * @since 1.0.0
 */
class BelongsTo extends BasicRelation implements Filter
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

        $model->setAttribute($this->getForeignKey(), $value);

        return $this;
    }

    /**
     * Get whether the relations works with a collection of models.
     *
     * @return bool
     */
    public function isMultiple()
    {
        return false;
    }

    /**
     * @param QueryBuilder $builder
     * @param $data
     *
     * @return void
     */
    public function applyFilterConstraint(QueryBuilder $builder, $data)
    {
        if (empty($data)) return;

        $builder->whereIn($this->getForeignKey(), $this->parseData($data));
    }

    /**
     * @return string
     */
    public function getForeignKey()
    {
        return $this->newRelationQuery()->getForeignKey();
    }
}