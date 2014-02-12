<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Kalnoy\Cruddy\Schema\Fields\InlineRelation;

/**
 * This field will allow to inlinely edit related model.
 */
class HasOneInline extends InlineRelation {

    /**
     * @inheritdoc
     *
     * @var string
     */
    protected $class = 'HasOne';

    /**
     * @inheritdoc
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return array
     */
    public function getConnectingAttributes(Eloquent $model)
    {
        return [ $this->relation->getPlainForeignKey() => $model->getKey() ];
    }

}