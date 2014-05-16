<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Kalnoy\Cruddy\Schema\Fields\InlineRelation;

/**
 * This field will allow to inlinely edit related model.
 * 
 * @since 1.0.0
 */
class HasOneInline extends InlineRelation {

    /**
     * {@inheritdoc}
     */
    public function getExtra($model)
    {
        return [ $this->relation->getPlainForeignKey() => $model->getKey() ];
    }

}