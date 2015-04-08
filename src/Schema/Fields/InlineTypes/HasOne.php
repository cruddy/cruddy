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
     * @var \Illuminate\Database\Eloquent\Relations\HasOne
     */
    protected $relation;

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
     * @param Model $model
     * @param Model $parent
     */
    public function attach(Model $model, Model $parent)
    {
        parent::attach($model, $parent);

        $model->setAttribute($this->relation->getPlainForeignKey(), $parent->getKey());
    }

}