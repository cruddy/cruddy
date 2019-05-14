<?php

namespace Kalnoy\Cruddy\Entity\Fields;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\MessageBag;
use Kalnoy\Cruddy\Entity\Entity;
use Kalnoy\Cruddy\Helpers;

/**
 * Inline relation allows to edit related models inside base form.
 *
 * @package \Kalnoy\Cruddy\Entity\Fields
 */
abstract class BaseInlineRelation extends BaseRelation
{
    /**
     * The name of the JavaScript class that is used to render this field.
     *
     * @return string
     */
    public function getUIModelClass()
    {
        return 'Cruddy.Fields.Embedded';
    }

    /**
     * @inheritdoc
     */
    public function validate($data)
    {
        if (empty($data)) return [];

        if ( ! $this->isMultiple()) {
            return $this->validateInner($data);
        }

        $result = [];

        foreach ($data as $cid => $itemInput) {
            $errors = $this->validateInner($itemInput);

            foreach ($errors as $innerKey => $innerErrors) {
                $result["{$cid}.{$innerKey}"] = $innerErrors;
            }
        }

        return $result;
    }

    /**
     * @param $data
     *
     * @return array
     */
    protected function validateInner($data)
    {
        $key = $this->getInnerModelId($data);
        $form = $this->baseInnerForm($key);

        if (!$this->getRefEntity()->isPermitted($form->getType())) return [];

        return $form->validate($data, $key);
    }

    /**
     * @param $key
     *
     * @return \Kalnoy\Cruddy\Entity\Form
     */
    protected function baseInnerForm($key)
    {
        $type = $key ? Entity::UPDATE : Entity::CREATE;

        $form = $this->getRefEntity()->baseForm($type);

        return $form;
    }

    /**
     * @inheritdoc
     */
    public function processValueBeforeValidating($data)
    {
        // Data will always be either empty or an array even when relation type
        // isn't multiple
        if (empty($data)) $data = [];

        $data = array_map(function ($input) {
            $key = $this->getInnerModelId($input);

            return $this
                ->baseInnerForm($key)
                ->getFields()
                ->processInputForValidation($input);
        }, $data);

        return $this->isMultiple() ? $data : Helpers::firstOrNull($data);
    }

    /**
     * @inheritdoc
     *
     * @param Model $model
     */
    public function setModelValue($model, $value)
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function processValueBeforeSetting($value)
    {
        return $value;
    }

    /**
     * @inheritdoc
     */
    public function getModelValue($model)
    {
        $items = parent::getModelValue($model);

        if ($items) {
            $this->loadRelations($items);
        }

        $form = $this->getRefEntity()->formForUpdate();

        if ($items instanceof Collection) {
            return $items->map(function ($model) use ($form) {
                return $form->getData($model);
            });
        }

        return $form->getData($items);
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

        return array_merge($relations, $entity->formForUpdate()
                                              ->getFields()
                                              ->relations($scope));
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
        $relations = $this->getRefEntity()
                          ->formForUpdate()
                          ->getFields()
                          ->relations();

        if ($relations) {
            $loadee->load($relations);
        }
    }

    /**
     * @inheritdoc
     */
    protected function generateLabel()
    {
        if ($label = $this->owner->translate("fields.{$this->id}")) {
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

    /**
     * @param array $data
     *
     * @return \Kalnoy\Cruddy\Entity\ModelForm
     */
    protected function innerForm(array $data)
    {
        $key = $this->getInnerModelId($data);

        return $this->getRefEntity()->form($key);
    }

}