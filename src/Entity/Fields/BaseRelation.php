<?php

namespace Kalnoy\Cruddy\Entity\Fields;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Kalnoy\Cruddy\Entity\Entity;
use Kalnoy\Cruddy\Entity\Form;
use Kalnoy\Cruddy\Form\Fields\BaseField;
use Kalnoy\Cruddy\Entity\Repository;
use Kalnoy\Cruddy\Service\ReferencesEntity;
use Kalnoy\Cruddy\Service\WorksWithRelations;

/**
 * Base relation field class.
 *
 * @package \Kalnoy\Cruddy\Entity\Fields
 */
abstract class BaseRelation extends BaseField
{
    use ReferencesEntity, WorksWithRelations;

    /**
     * @var Form
     */
    protected $owner;

    /**
     * Init field.
     *
     * @param Form $owner
     * @param string $id
     * @param $refEntityId
     */
    public function __construct(Form $owner, $id, $refEntityId = null)
    {
        parent::__construct($owner, $id);

        $this->refEntityId = $refEntityId;
    }

    /**
     * Get whether the relations works with a collection of models.
     *
     * @return bool
     */
    abstract public function isMultiple();

    /**
     * @inheritdoc
     *
     * This will force null value if user is not allowed to read referenced entity.
     */
    public function getModelValue($model)
    {
        if ( ! $this->getRefEntity()->isPermitted(Entity::READ)) {
            return null;
        }

        return parent::getModelValue($model);
    }

    /**
     * @param Model $model
     * @param mixed $value
     *
     * @return $this
     */
    public function syncRelation($model, $value)
    {
        return $this;
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
     * @inheritdoc
     */
    public function getEntity()
    {
        return $this->owner->getEntity();
    }

    /**
     * @param Model $model
     * @param string $attr
     *
     * @return mixed
     */
    public function getRelationValue(Model $model, $attr)
    {
        return $model->getRelationValue($attr);
    }

    /**
     * @inheritDoc
     */
    protected function getDefaultGetter()
    {
        return [ $this, 'getRelationValue' ];
    }

    /**
     * @inheritdoc
     */
    public function getConfig()
    {
        return [
            'multiple' => $this->isMultiple(),
            'reference' => $this->getRefEntityId(),

        ] + parent::getConfig();
    }

}