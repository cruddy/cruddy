<?php

namespace Kalnoy\Cruddy\Contracts;

use Illuminate\Database\Query\Builder;

interface Filter extends Entry {

    /**
     * @param Builder $builder
     * @param $input
     *
     * @return void
     */
    public function applyFilterConstraint(Builder $builder, $input);

}