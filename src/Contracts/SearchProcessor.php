<?php

namespace Kalnoy\Cruddy\Contracts;

use Illuminate\Database\Eloquent\Builder;

/**
 * Search processor interface for applying conditions the the query.
 */
interface SearchProcessor {

    /**
     * Apply search conditions to the builder.
     *
     * @param Builder $builder
     * @param array $input
     */
    public function constraintBuilder(Builder $builder, array $input);
}