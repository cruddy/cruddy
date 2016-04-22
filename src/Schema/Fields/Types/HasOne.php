<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\MorphOneOrMany;
use Illuminate\Support\Arr;
use Kalnoy\Cruddy\Entity;
use Kalnoy\Cruddy\Schema\Fields\InlineRelation;

/**
 * This field will allow to inlinely edit related model.
 *
 * @since 1.0.0
 */
class HasOne extends InlineRelation
{
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
     * @inheritDoc
     *
     * @param Model $model
     */
    public function setModelValue($model, $value)
    {
        if ( ! $value) {
            if ($this->getRefEntity()->isPermitted(Entity::DELETE)) {
                $this->deleteRelated($model);
            }

            return $this;
        }

        $innerInput = reset($value);

        $innerId = $this->getInnerModelId($innerInput);

        $innerModel = $innerId
            ? $this->newRelationQuery($model)->find($innerId)
            : null;

        if ( ! $innerModel) {
            $innerModel = $this->newRelatedInstance($model);
        }

        if ($this->modelCanBeSaved($innerModel)) {
            $this->getRefEntity()->save($innerModel, $innerInput);
        }

        return $this;
    }

    /**
     * @param Model $model
     *
     * @return static
     */
    public function newRelatedInstance($model)
    {
        /** @var HasOneOrMany $relation */
        $relation = $this->newRelationQuery($model);

        $inner = $relation->getRelated()->newInstance();

        $inner->setAttribute($relation->getPlainForeignKey(),
                             $relation->getParentKey());

        if ($relation instanceof MorphOneOrMany) {
            $inner->setAttribute($relation->getPlainMorphType(),
                                 $relation->getMorphClass());
        }

        return $inner;
    }

    /**
     * @param Model $model
     * @param array $idList
     */
    protected function deleteRelated($model, array $idList = [ ])
    {
        $relation = $this->newRelationQuery($model);

        if ($idList) {
            $relation->whereIn($relation->getRelated()->getKeyName(), $idList);
        }

        $relation->delete();
    }

}