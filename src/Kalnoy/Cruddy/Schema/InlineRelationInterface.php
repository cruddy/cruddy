<?php

namespace Kalnoy\Cruddy\Schema;

use Illuminate\Database\Eloquent\Model as Eloquent;

/**
 * InlineRelationInterface
 */
interface InlineRelationInterface {

    /**
     * Extract inline model attributes from the input.
     *
     * @param array $data
     *
     * @return array
     */
    public function extractAttributes(array $data);

    /**
     * Get attributes for related models by which they will be connected to 
     * the parent model.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return array
     */
    public function getConnectingAttributes(Eloquent $model);

    /**
     * Get the id of the relation.
     *
     * @return string
     */
    public function getId();

    /**
     * Get the other entity instance.
     *
     * @return \Kalnoy\Cruddy\Entity
     */
    public function getReference();

}