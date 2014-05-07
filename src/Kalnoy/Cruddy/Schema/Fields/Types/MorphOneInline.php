<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Kalnoy\Cruddy\Schema\Fields\InlineRelation;

/**
 * This field will allow to inlinely edit models connectend by morph one relation.
 */
class MorphOneInline extends HasOneInline {

    /**
     * @inheritdoc
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return array
     */
    public function getExtra($model)
    {
        return
        [
            $this->relation->getPlainMorphType() => $this->relation->getMorphClass(),

        ] + parent::getExtra($model);
    }

}