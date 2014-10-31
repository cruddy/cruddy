<?php

namespace Kalnoy\Cruddy\Contracts;

use Illuminate\Database\Query\Builder;

interface KeywordsFilter {

    /**
     * @param Builder $builder
     * @param array $keywords
     *
     * @return void
     */
    public function applyKeywordsFilter(Builder $builder, array $keywords);

}