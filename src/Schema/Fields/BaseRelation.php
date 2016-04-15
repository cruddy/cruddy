<?php

namespace Kalnoy\Cruddy\Schema\Fields;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Kalnoy\Cruddy\Entity;
use Kalnoy\Cruddy\Repository;

/**
 * Base relation field class.
 *
 * This field type is provided with references entity and corresponding relation
 * object.
 *
 * @since 1.0.0
 */
abstract class BaseRelation extends BaseField
{
    /**
     * The entity that this relation refers to.
     *
     * @var Entity
     */
    private $refEntity;

    /**
     * @var string
     */
    protected $refEntityId;

    /**
     * Init field.
     *
     * @param Entity $form
     * @param string $id
     * @param $refEntityId
     */
    public function __construct(Entity $form, $id, $refEntityId)
    {
        parent::__construct($form, $id);

        $this->refEntityId = $refEntityId;
    }

    /**
     * Get whether the relations works with a collection of models.
     *
     * @return bool
     */
    abstract public function isMultiple();

    /**
     * {@inheritdoc}
     */
    public function getModelValue($model)
    {
        if ( ! $this->getRefEntity()->isPermitted(Entity::READ)) {
            return null;
        }

        return parent::getModelValue($model);
    }

    /**
     * Start new relational query for specified model.
     *
     * @param Model $model
     *
     * @return Relation
     */
    public function newRelationQuery(Model $model = null)
    {
        $model = $model ?: $this->form->newModel();

        return $model->{$this->getRelationId()}();
    }

    /**
     * {@inheritdoc}
     */
    public function eagerLoads()
    {
        $relation = $this->getRelationId();

        return array_merge((array)$relation,
                           $this->getRefEntity()->eagerLoads($relation));
    }

    /**
     * Get a list of relations.
     *
     * @param $scope
     *
     * @return array
     */
    public function relations($scope)
    {
        return [ $this->getRelationId($scope) ];
    }

    /**
     * Get referenced entity instance.
     *
     * @return Entity
     */
    public function getRefEntity()
    {
        if ($this->refEntity !== null) {
            return $this->refEntity;
        }

        $this->refEntity = $this->getEntitiesRepository()
                                ->resolve($this->getRefEntityId());

        return $this->refEntity;
    }

    /**
     * @return string
     */
    public function getRefEntityId()
    {
        return $this->refEntityId ?: $this->id;
    }

    /**
     * @return Repository
     */
    public function getEntitiesRepository()
    {
        return app(Repository::class);
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
        $id = $this->getModelAttributeName();

        return $scope ? $scope.'.'.$id : $id;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'multiple' => $this->isMultiple(),
            'reference' => $this->getRefEntityId(),

        ] + parent::toArray();
    }

}