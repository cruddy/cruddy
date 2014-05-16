<?php

namespace Kalnoy\Cruddy\Repo;

use Illuminate\Database\Eloquent\Builder;

/**
 * Search processor interface for applying conditions the the query.
 */
interface SearchProcessorInterface {

    /**
     * Apply search conditions to the builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param array                                 $options
     *
     * @return void
     */
    public function search(Builder $builder, array $options);
}