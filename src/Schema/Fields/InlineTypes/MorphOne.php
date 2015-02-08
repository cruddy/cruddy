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
     * @param Model $model
     * @param Model $parent
     */
    public function attach(Model $model, Model $parent)
    {
        parent::attach($model, $parent);

        $model->setAttribute($this->relation->getPlainMorphType(), $this->relation->getMorphClass());
    }

}