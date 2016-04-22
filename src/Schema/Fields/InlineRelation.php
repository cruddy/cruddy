<?php

namespace Kalnoy\Cruddy\Schema\Fields;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\MessageBag;
use Kalnoy\Cruddy\Entity;
use Kalnoy\Cruddy\Helpers;

/**
 * Inline relation allows to edit related models inlinely.
 *
 * @since 1.0.0
 */
abstract class InlineRelation extends BaseRelation
{
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
    public function validate($data)
    {
        if (empty($data)) return [];

        if ( ! $this->isMultiple()) {
            $key = $this->getInnerModelId($data);

            return $this->getRefEntity()->validate($data, $key);
        }

        $result = [];

        foreach ($data as $cid => $itemInput) {
            $key = $this->getInnerModelId($itemInput);

            $errors = $this->getRefEntity()->validate($itemInput, $key);

            foreach ($errors as $innerKey => $innerErrors) {
                $result["{$cid}.{$innerKey}"] = $innerErrors;
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function parseInputValue($data)
    {
        // Data will always be either empty or an array even when relation type
        // isn't multiple
        if (empty($data)) $data = [];

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
     * @param $value
     *
     * @return mixed
     */
    protected function getInnerModelId($value)
    {
        return Helpers::processString(Arr::get($value, Entity::ID_PROPERTY));
    }

}