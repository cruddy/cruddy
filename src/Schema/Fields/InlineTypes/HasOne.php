<?php

namespace Kalnoy\Cruddy\Schema\Fields\InlineTypes;

use Illuminate\Database\Eloquent\Model;
use Kalnoy\Cruddy\Schema\Fields\InlineRelation;

/**
 * This field will allow to inlinely edit related model.
 *
 * @since 1.0.0
 */
class HasOne extends InlineRelation {

    /**
     * @param Model $model
     * @param Model $parent
     */
    public function attach(Model $model, Model $parent)
    {
        parent::attach($model, $parent);

        $model->setAttribute($this->relation->getPlainForeignKey(), $parent->getKey());
    }

}