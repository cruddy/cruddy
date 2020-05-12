<?php

namespace Kalnoy\Cruddy\Entity\DataSource\Columns;

use Illuminate\Database\Eloquent\Model;
use Kalnoy\Cruddy\Entity\DataSource\DataSource;
use Kalnoy\Cruddy\Entity\Entity;
use Kalnoy\Cruddy\Service\ReferencesEntity;
use Kalnoy\Cruddy\Service\WorksWithRelations;

/**
 * Class EntityColumn
 *
 * @package Kalnoy\Cruddy\Entity\DataSource\Columns
 */
class EntityColumn extends BaseColumn
{
    use ReferencesEntity, WorksWithRelations;

    /**
     * EntityColumn constructor.
     *
     * @param DataSource $owner
     * @param $id
     * @param null $refEntityId
     */
    public function __construct(DataSource $owner, $id, $refEntityId = null)
    {
        parent::__construct($owner, $id);

        $this->refEntityId = $refEntityId;
    }

    /**
     * @return callback
     */
    protected function getDefaultGetter()
    {
        return [ $this, 'modelRelationToText' ];
    }

    /**
     * @param Model $model
     * @param $attribute
     *
     * @return string
     */
    public function modelRelationToText(Model $model, $attribute)
    {
        if ( ! $value = data_get($model, $attribute)) {
            return null;
        }

        if ($value instanceof Model) {
            return $this->refModelToText($value);
        }

        return $value->map([ $this, 'refModelToText' ])->implode(', ');
    }

    /**
     * @inheritDoc
     */
    public function canOrder()
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function relationships()
    {
        return [ $this->getModelAttribute() ];
    }

    /**
     * @param Model $model
     *
     * @return string
     */
    public function refModelToText(Model $model)
    {
        $html = $this->getRefEntity()->toHTML($model);

        $params['cruddy_entity'] = $this->getRefEntityId();
        $params['id'] = $model->getKey();

        return '<a href="'.route('cruddy.index', $params).'">'.$html.'</a>';
    }

    /**
     * @return Entity
     */
    public function getEntity()
    {
        return $this->owner->getEntity();
    }
}