<?php

namespace Kalnoy\Cruddy\Schema\Fields;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Model as Eloquent;
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
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return mixed
     */
    public function extract(Eloquent $model)
    {
        if ( ! Entity::getEnvironment()->getPermissions()->canView($this->reference))
        {
            return null;
        }

        return parent::extract($model);
    }

    /**
     * Start new relational query for specified model.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function newRelationalQuery(Eloquent $model = null)
    {
        $model = $model ?: $this->reference->getRepository()->newModel();

        return $model->{$this->getRelationId()}();
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
     * @param array  $relations
     * @param string $key
     *
     * @return void
     */
    protected function appendPreloadableRelations(array &$relations, $key = null)
    {
        $relations[] = $this->getKeyedRelationId($key);
    }

    /**
     * Get relation id prefixed with key if one is provided.
     *
     * @param string $key
     *
     * @return string
     */
    protected function getKeyedRelationId($key)
    {
        $relationId = $this->getRelationId();

        return $key ? $key . '.' . $relationId : $relationId;
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
        return true;
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