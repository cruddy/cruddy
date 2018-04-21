<?php

namespace Kalnoy\Cruddy\Entity\Fields;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Kalnoy\Cruddy\Contracts\Filter;
use Kalnoy\Cruddy\Entity\Fields\BaseEntitySelector;

/**
 * Handles belongs to relation.
 *
 * @package \Kalnoy\Cruddy\Entity\Fields
 */
class BelongsTo extends BaseEntitySelector
{
    /**
     * @param Model $model
     * @param array $value
     *
     * @return $this
     */
    public function setModelValue($model, $value)
    {
        $value = $this->processValueBeforeSetting($value);

        if ($this->setter) {
            call_user_func($this->setter, $model, $value);
        } elseif ($value) {
            $this->newRelationQuery($model)->associate($value);
        } else {
            $this->newRelationQuery($model)->dissociate();
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
        return false;
    }

    /**
     * @return string
     */
    public function getForeignKey()
    {
        return $this->newRelationQuery()->getForeignKey();
    }
}