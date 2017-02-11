<?php

namespace Kalnoy\Cruddy\Entity\Fields;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Support\Arr;
use Kalnoy\Cruddy\Entity\Entity;
use Kalnoy\Cruddy\Entity\Fields\HasOne;
use Kalnoy\Cruddy\Helpers;

/**
 * Field to edit many inline models.
 *
 * @package \Kalnoy\Cruddy\Entity\Fields
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
     * @inheritdoc
     */
    public function syncRelation($model, $value)
    {
        $existing = $this->newRelationQuery($model)->get()->getDictionary();

        // Make sure value is array (it can be empty string)
        $value = empty($value) ? [] : $value;

        foreach ($value as $innerInput) {
            $id = $this->getInnerModelId($innerInput);

            if ($id && isset($existing[$id])) {
                $id = $existing[$id];
            }

            $form = $this->getRefEntity()->form($id);

            if ($this->modelCanBeSaved($innerModel = $form->getModel())) {
                $this->associate($model, $innerModel);

                $form->save($innerInput);
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