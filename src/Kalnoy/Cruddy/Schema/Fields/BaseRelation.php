<?php

namespace Kalnoy\Cruddy\Schema\Fields;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Kalnoy\Cruddy\Entity;
use Kalnoy\Cruddy\Contracts\Permissions;

/**
 * Base relation field class.
 *
 * This field type is provided with references entity and corresponding relation
 * object.
 *
 * @since 1.0.0
 */
abstract class BaseRelation extends BaseField {

    /**
     * The entity that this relation refers to.
     *
     * @var Entity
     */
    protected $reference;

    /**
     * The relation object.
     *
     * @var Relation
     */
    protected $relation;

    /**
     * Init field.
     *
     * @param Entity   $entity
     * @param string   $id
     * @param Entity   $reference
     * @param Relation $relation
     */
    public function __construct(Entity $entity, $id, Entity $reference, Relation $relation)
    {
        parent::__construct($entity, $id);

        $this->reference = $reference;
        $this->relation = $relation;
    }

    /**
     * {@inheritdoc}
     */
    public function extract(Eloquent $model)
    {
        $permissions = Entity::getEnvironment()->getPermissions();

        if ( ! $permissions->isPermitted(Permissions::VIEW, $this->reference))
        {
            return null;
        }

        return parent::extract($model);
    }

    /**
     * Start new relational query for specified model.
     *
     * @param Eloquent $model
     *
     * @return Relation
     */
    public function newRelationalQuery(Eloquent $model = null)
    {
        $model = $model ?: $this->reference->getRepository()->newModel();

        return $model->{$this->getRelationId()}();
    }

    /**
     * {@inheritdoc}
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
     * Get referenced entity instance.
     *
     * @return Entity
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * The relation object.
     *
     * @return Relation
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
        return $this->id;
    }

    /**
     * {@inheritdoc}
     *
     * Relational fields are always fillable since they are not actual attribute
     * on the model.
     */
    public function isFillable()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return
        [
            'reference' => $this->reference->getId(),

        ] + parent::toArray();
    }

}