<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * Handles belongs to many relation.
 */
class MorphToMany extends HasMany {

    /**
     * @inheritdoc
     *
     * @var bool
     */
    protected $multiple = true;

    protected $filterType = self::FILTER_NONE;

    protected function filterInnerQuery($q, $data)
    {
        parent::filterInnerQuery($q);

        // Wait until it is added to the Laravel
        // $q->where($this->relation->getMorphType(), '=', $this->relation->getMorphClass());
    }
}