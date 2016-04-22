<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Support\Arr;
use Kalnoy\Cruddy\Entity;

/**
 * Field to edit many inline models.
 *
 * @since 1.0.0
 */
class HasMany extends HasOne
{
    /**
     * Get whether the relations works with a collection of models.
     *
     * @return bool
     */
    public function isMultiple()
    {
        return true;
    }

    /**
     * @inheritDoc
     *
     * @param Model $model
     * @param array $value
     */
    public function setModelValue($model, $value)
    {
        $existing = $this->newRelationQuery($model)->get()->getDictionary();

        foreach ($value as $innerInput) {
            $id = $this->getInnerModelId($innerInput);

            if ($id && isset($existing[$id])) {
                $innerModel = $existing[$id];
            } else {
                $innerModel = $this->newRelatedInstance($model);
            }

            if ($this->modelCanBeSaved($innerModel)) {
                $this->getRefEntity()->save($innerModel, $innerInput);
            }
        }

        if ( ! $this->getRefEntity()->isPermitted(Entity::DELETE)) {
            return $this;
        }

        $idList = Arr::pluck($value, Entity::ID_PROPERTY);

        $idList = array_diff(array_keys($existing), $idList);

        if ($idList) {
            $this->deleteRelated($model, $idList);
        }

        return $this;
    }

}