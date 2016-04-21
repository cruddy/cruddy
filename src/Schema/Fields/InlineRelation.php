<?php

namespace Kalnoy\Cruddy\Schema\Fields;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Kalnoy\Cruddy\Contracts\InlineRelation as InlineRelationContract;
use Kalnoy\Cruddy\Entity;
use Kalnoy\Cruddy\Helpers;
use Kalnoy\Cruddy\Service\Validation\ValidationException;

/**
 * Inline relation allows to edit related models inlinely.
 *
 * @since 1.0.0
 */
abstract class InlineRelation extends BaseRelation
{
    /**
     * The name of the property where id is stored.
     */
    const ID_PROPERTY = '__id';

    /**
     * The name of the JavaScript class that is used to render this field.
     *
     * @return string
     */
    protected function getModelClass()
    {
        return 'Cruddy.Fields.Embedded';
    }

    /**
     * @inheritDoc
     */
    public function validate($value)
    {
        if (empty($value)) return [];

        if ( ! $this->isMultiple()) {
            $action = $this->getActionFromInput($value);

            return $this->getRefEntity()->validate($action, $value);
        }

        $result = [];

        foreach ($value as $cid => $itemInput) {
            $action = $this->getActionFromInput($itemInput);

            if ($errors = $this->getRefEntity()->validate($action, $itemInput)) {
                $result[$cid] = $errors;
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function parseInputValue($data)
    {
        if (empty($data)) $data = null;

        $data = (array)$data;

        array_walk($data, function (&$item) {
            $this->getRefEntity()->getFields()->parseInput($item);
        });

        return $this->isMultiple() ? $data : Helpers::firstOrNull($data);
    }

    /**
     * @inheritDoc
     */
    protected function processInputValue($value)
    {
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingMode()
    {
        return self::MODE_AFTER_SAVE;
    }

    /**
     * {@inheritdoc}
     */
    public function getModelValue($model)
    {
        $items = parent::getModelValue($model);

        if ($items) {
            $this->loadRelations($items);
        }

        return $this->getRefEntity()->getModelData($items);
    }

    /**
     * @param string $scope
     *
     * @return array
     */
    public function relations($scope)
    {
        $relations = parent::relations($scope);
        $entity = $this->getRefEntity();

        $scope = $scope ? $scope.'.'.$entity->getId() : $entity->getId();

        return array_merge($relations, $entity->relations($scope));
    }

    /**
     * {@inheritdoc}
     */
    public function getModelValueForColumn($model)
    {
        return $this->getRefEntity()->simplifyModel(parent::getModelValue($model));
    }

    /**
     * Find inner relations and load them on target model.
     *
     * @param Model|\Illuminate\Database\Eloquent\Collection $loadee
     *
     * @return void
     */
    protected function loadRelations($loadee)
    {
        $relations = $this->getRefEntity()->relations();

        if ($relations) $loadee->load($relations);
    }

    /**
     * {@inheritdoc}
     */
    protected function generateLabel()
    {
        if ($label = $this->translate('fields')) {
            return $label;
        }

        $entity = $this->getRefEntity();

        return $this->isMultiple()
            ? $entity->getPluralTitle()
            : $entity->getSingularTitle();
    }

    /**
     * @param Model $model
     *
     * @return bool
     */
    protected function modelCanBeSaved($model)
    {
        $action = $this->getRefEntity()->getActionFromModel($model);

        return $this->getRefEntity()->isPermitted($action);
    }

    /**
     * @param array $value
     *
     * @return string
     */
    protected function getActionFromInput($value)
    {
        $id = $this->getInnerModelId($value);

        return is_null($id) ? Entity::CREATE : Entity::UPDATE;
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    protected function getInnerModelId($value)
    {
        return Helpers::processString(Arr::get($value, self::ID_PROPERTY));
    }

}