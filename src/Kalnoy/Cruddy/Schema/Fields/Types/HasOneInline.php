<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Kalnoy\Cruddy\Schema\Fields\InlineRelation;

/**
 * This field will allow to inlinely edit related model.
 */
class HasOneInline extends InlineRelation {

    /**
     * @inheritdoc
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return array
     */
    public function getExtra($model)
    {
        return [ $this->relation->getPlainForeignKey() => $model->getKey() ];
    }

}