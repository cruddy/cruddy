<?php

namespace Kalnoy\Cruddy\Schema\Fields\InlineTypes;

use Illuminate\Database\Eloquent\Model;
use Kalnoy\Cruddy\Schema\Fields\InlineTypes\HasOne;

/**
 * This field will allow to inlinely edit models connectend by morph one relation.
 *
 * @since 1.0.0
 */
class MorphOne extends HasOne {

    /**
     * @var \Illuminate\Database\Eloquent\Relations\MorphOne
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

        $model->setAttribute($this->relation->getPlainMorphType(), $this->relation->getMorphClass());
    }

}