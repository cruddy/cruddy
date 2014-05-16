<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Kalnoy\Cruddy\Schema\Fields\InlineRelation;

/**
 * This field will allow to inlinely edit models connectend by morph one relation.
 * 
 * @since 1.0.0
 */
class MorphOneInline extends HasOneInline {

    /**
     * {@inheritdoc}
     */
    public function getExtra($model)
    {
        return
        [
            $this->relation->getPlainMorphType() => $this->relation->getMorphClass(),

        ] + parent::getExtra($model);
    }

}