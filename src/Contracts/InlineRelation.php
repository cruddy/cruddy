<?php

namespace Kalnoy\Cruddy\Contracts;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Model;
use Kalnoy\Cruddy\Contracts\Field;

/**
 * Inline relation interface.
 *
 * These objects are used by entity to process and save embedded entities.
 *
 * @since 1.0.0
 */
interface InlineRelation extends Field {

    /**
     * @param Model $model
     * @param Model $parent
     *
     * @return void
     */
    public function attach(Model $model, Model $parent);

    /**
     * @return \Kalnoy\Cruddy\Entity
     */
    public function getReference();

}