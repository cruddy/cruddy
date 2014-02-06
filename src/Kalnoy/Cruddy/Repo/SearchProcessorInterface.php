<?php

namespace Kalnoy\Cruddy\Repo;

use Illuminate\Database\Eloquent\Builder;

/**
 * SearchProcessorInterface
 */
interface SearchProcessorInterface {

    /**
     * Apply the search to the builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param array                                 $options
     *
     * @return void
     */
    public function search(Builder $builder, array $options);
}