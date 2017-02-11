<?php

namespace Kalnoy\Cruddy\Form;

use Illuminate\Support\Arr;
use Kalnoy\Cruddy\Form\Fields\BaseField;
use Kalnoy\Cruddy\Entity\Fields\BaseInlineRelation;
use Kalnoy\Cruddy\Service\BaseCollection;
use Kalnoy\Cruddy\Service\BaseFactory;

/**
 * Fields collection.
 *
 * @method Fields\StringInput string(string $id)
 * @method Fields\Text text(string $id)
 * @method Fields\Email email(string $id)
 * @method Fields\Password password(string $id)
 *
 * @method Fields\DateTime datetime(string $id)
 * @method Fields\Date date(string $id)
 * @method Fields\Time time(string $id)
 *
 * @method Fields\Boolean bool(string $id)
 * @method Fields\Boolean boolean(string $id)
 *
 * @method Fields\File file(string $id)
 * @method Fields\Image image(string $id)
 *
 * @method Fields\Integer int(string $id)
 * @method Fields\Integer integer(string $id)
 * @method Fields\Float float(string $id)
 *
 * @method Fields\Computed compute(string $id, $accessor = null)
 * @method Fields\Computed computed(string $id, $accessor = null)
 *
 * @method Fields\Enum enum(string $id, $items)
 * @method Fields\Slug slug(string $id, string $field = null)
 *
 * @package Kalnoy\Cruddy\Form
 */
class FieldsCollection extends BaseCollection
{
    /**
     * FieldsCollection constructor.
     *
     * @param BaseForm $form
     * @param BaseFactory $factory
     */
    public function __construct(BaseForm $form, BaseFactory $factory)
    {
        parent::__construct($form, $factory);
    }

    /**
     * @inheritdoc
     * 
     * @return BaseField
     */
    public function get($id)
    {
        return parent::get($id);
    }

    /**
     * @param $model
     *
     * @return array
     */
    public function modelData($model)
    {
        return array_map(function (BaseField $field) use ($model) {
            return $field->getModelValue($model);
        }, $this->items);
    }
    
    /**
     * Process input before validation.
     *
     * @param array $input
     *
     * @return array
     */
    public function processInputForValidation(array $input)
    {
        array_walk($input, function (&$value, $key) {
            if ($this->has($key)) {
                $value = $this->get($key)->processValueBeforeValidating($value);
            }
        });

        return $input;
    }

    /**
     * @param mixed $model
     * @param array $input
     *
     * @return $this
     */
    public function fillModel($model, array $input)
    {
        foreach ($this->items as $key => $field) {
            if ( ! $field->isDisabled() && array_key_exists($key, $input)) {
                $field->setModelValue($model, $input[$key]);
            }
        }

        return $this;
    }

    /**
     * Get validation labels.
     *
     * @return array
     */
    public function validationLabels()
    {
        return array_map(function (BaseField $field) {
            return mb_strtolower($field->getLabel());
        }, $this->items);
    }

    /**
     * @return array
     */
    public function getRules()
    {
        return array_filter(array_map(function (BaseField $field) {
            return $field->isDisabled() ? null : $field->getRules();
        }, $this->items));
    }

}