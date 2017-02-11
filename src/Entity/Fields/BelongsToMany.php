<?php

namespace Kalnoy\Cruddy\Entity\Fields;

use Illuminate\Database\Eloquent\Model;

/**
 * Handles belongs to many relation.
 *
 * @package \Kalnoy\Cruddy\Entity\Fields
 */
class BelongsToMany extends BaseEntitySelector
{
    /**
     * @param Model $model
     * @param array $value
     *
     * @return $this
     */
    public function setModelValue($model, $value)
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function syncRelation($model, $value)
    {
        $value = $this->processValueBeforeSetting($value);

        if ($this->setter) {
            call_user_func($this->setter, $model, $value);
        } else {
            $this->newRelationQuery($model)->sync($value);
        }
        
        return $this;
    }

    /**
     * Get whether the relations works with a collection of models.
     *
     * @return bool
     */
    public function isMultiple()
    {
        return true;
    }
}