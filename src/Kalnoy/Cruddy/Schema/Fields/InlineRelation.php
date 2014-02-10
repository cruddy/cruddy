<?php

namespace Kalnoy\Cruddy\Schema\Fields;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Kalnoy\Cruddy\Schema\InlineRelationInterface;

/**
 * Inline relation allows to edit related models inlinely.
 */
abstract class InlineRelation extends BaseRelation implements InlineRelationInterface {

    /**
     * @inheritdoc
     *
     * @var string
     */
    protected $type = 'inline-relation';

    /**
     * @inhertidoc
     *
     * Inline relation skips value since it is passed to the other repository.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function skip($value)
    {
        return true;
    }

    /**
     * @inheritdoc
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return array
     */
    public function extract(Eloquent $model)
    {
        return $this->reference->extract($model->{$this->id});
    }

}