<?php

namespace Kalnoy\Cruddy\Service;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Kalnoy\Cruddy\Entity\Entity;

trait WorksWithRelations
{
    /**
     * @return Entity
     */
    abstract public function getEntity();
    
    /**
     * Start new relational query for specified model.
     *
     * @param Model $model
     *
     * @return Relation
     */
    public function newRelationQuery(Model $model = null)
    {
        $model = $model ?: $this->getEntity()->newModel();

        return $model->{$this->getRelationId()}();
    }

    /**
     * Get relation id.
     *
     * @param string $scope
     *
     * @return string
     */
    public function getRelationId($scope = null)
    {
        $id = $this->getModelAttribute();

        return $scope ? $scope.'.'.$id : $id;
    }
}