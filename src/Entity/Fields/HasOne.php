<?php

namespace Kalnoy\Cruddy\Entity\Fields;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\MorphOneOrMany;
use Illuminate\Support\Arr;
use Kalnoy\Cruddy\Entity\Entity;
use Kalnoy\Cruddy\Entity\Fields\BaseInlineRelation;

/**
 * @package \Kalnoy\Cruddy\Entity\Fields
 */
class HasOne extends BaseInlineRelation
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
     * @inheritdoc
     */
    public function syncRelation($model, $value)
    {
        if ( ! $value) {
            if ($this->getRefEntity()->isPermitted(Entity::DELETE)) {
                $this->deleteRelated($model);
            }

            return $this;
        }

        $innerInput = reset($value);

        $form = $this->innerForm($innerInput);

        if ($this->modelCanBeSaved($innerModel = $form->getModel())) {
            $this->associate($model, $innerModel);

            $form->save($innerInput);
        }

        return $this;
    }

    /**
     * @param Model $model
     * @param Model $inner
     *
     * @return Model
     */
    public function associate($model, $inner)
    {
        /** @var HasOneOrMany $relation */
        $relation = $this->newRelationQuery($model);

        $inner->setAttribute($relation->getForeignKeyName(),
                             $relation->getParentKey());

        if ($relation instanceof MorphOneOrMany) {
            $inner->setAttribute($relation->getMorphType(),
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