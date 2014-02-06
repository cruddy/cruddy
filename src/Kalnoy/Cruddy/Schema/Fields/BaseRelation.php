<?php

namespace Kalnoy\Cruddy\Schema\Fields;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Kalnoy\Cruddy\Entity;

/**
 * Base relation field class.
 */
abstract class BaseRelation extends BaseField {

    /**
     * The entity that this relation refers to.
     *
     * @var \Kalnoy\Cruddy\Entity\Entity
     */
    protected $reference;

    /**
     * The relation object.
     *
     * @var \Illuminate\Database\Eloquent\Relations\Relation
     */
    protected $relation;

    /**
     * Init field.
     *
     * @param \Kalnoy\Cruddy\Entity $entity
     * @param string                $id
     * @param \Kalnoy\Cruddy\Entity $reference
     */
    public function __construct(Entity $entity, $id, Entity $reference, Relation $relation)
    {
        parent::__construct($entity, $id);

        $this->reference = $reference;
        $this->relation = $relation;
    }

    /**
     * @inheritdoc
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return $this
     */
    public function modifyQuery(EloquentBuilder $builder)
    {
        $builder->with($this->getRelationId());

        return $this;
    }

    /**
     * Get references entity instance.
     *
     * @return \Kalnoy\Cruddy\Entity
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * The relation object.
     *
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function getRelation()
    {
        return $this->relation;
    }

    /**
     * Get relation id.
     *
     * @return string
     */
    public function getRelationId()
    {
        return \camel_case($this->id);
    }

    /**
     * @inheritdoc
     *
     * @return bool
     */
    public function isFillable()
    {
        return ! $this->disabled;
    }

    /**
     * @inheritdoc
     *
     * @return array
     */
    public function toArray()
    {
        return
        [
            'reference' => $this->reference->getId(),

        ] + parent::toArray();
    }

}