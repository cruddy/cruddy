<?php

namespace Kalnoy\Cruddy\Entity;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Kalnoy\Cruddy\Entity\Fields\BaseInlineRelation;
use Kalnoy\Cruddy\Entity\Fields\BaseRelation;
use Kalnoy\Cruddy\Form\Fields\BaseField;
use Kalnoy\Cruddy\Form\FieldsCollection as BaseCollection;

/**
 * Class FieldsCollection
 *
 * @method Fields\BelongsTo belongsTo(string $id, string $entity = null)
 * @method Fields\BelongsToMany belongsToMany(string $id, string $entity = null)
 * @method Fields\BelongsToMany morphToMany(string $id, string $entity = null)
 *
 * @method Fields\HasOne hasOne(string $id, string $entity = null)
 * @method Fields\HasOne morphOne(string $id, string $entity = null)
 * @method Fields\HasMany hasMany(string $id, string $entity = null)
 * @method Fields\HasMany morphMany(string $id, string $entity = null)
 *
 * @package Kalnoy\Cruddy\Entity
 */
class FieldsCollection extends BaseCollection
{
    /**
     * @var Form
     */
    protected $owner;

    /**
     * @return $this
     */
    public function timestamps()
    {
        $this->datetime('created_at')->disable();
        $this->datetime('updated_at')->disable();

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function validationLabels()
    {
        $labels = parent::validationLabels();

        foreach ($this->items as $key => $field) {
            if ( ! $field instanceof BaseInlineRelation) {
                continue;
            }

            $innerLabels = [];

            foreach ([ Entity::CREATE, Entity::UPDATE ] as $type) {
                $innerLabels = array_merge($innerLabels,
                                           $field->getRefEntity()
                                                 ->baseForm($type)
                                                 ->getFields()
                                                 ->validationLabels());
            }

            foreach ($innerLabels as $innerKey => $label) {
                $labels["$key.$innerKey"] = $label;
            }
        }

        return $labels;
    }

    /**
     * Validates an input and returns errors if any.
     *
     * @param array $input
     *
     * @return array
     */
    public function validateInner(array $input)
    {
        $result = [];

        foreach ($this->items as $key => $field) {
            if ( $field->isDisabled() ||
                ! $field instanceof BaseInlineRelation
            ) {
                continue;
            }

            $errors = $field->validate(Arr::get($input, $key));

            foreach ($errors as $innerKey => $innerErrors) {
                $result["{$key}.{$innerKey}"] = $innerErrors;
            }
        }

        return $result;
    }

    /**
     * @param Model $model
     * @param array $input
     *
     * @return $this
     */
    public function syncRelations(Model $model, array $input)
    {
        foreach ($this->items as $key => $field) {
            if ( ! $field->isDisabled() &&
                $field instanceof BaseRelation &&
                array_key_exists($key, $input)
            ) {
                $field->syncRelation($model, $input[$key]);
            }
        }

        return $this;
    }

    /**
     * Get a list of relations of the model.
     *
     * @param string $scope
     *
     * @return array
     */
    public function relations($scope = null)
    {
        return array_reduce($this->items, function (array $carry, BaseField $field) use ($scope) {
            if ($field instanceof BaseRelation) {
                return array_merge($carry, $field->relations($scope));
            }

            return $carry;
        }, []);
    }

    /**
     * @return bool
     */
    public function updates()
    {
        return $this->owner->updates();
    }

    /**
     * @return bool
     */
    public function creates()
    {
        return $this->owner->creates();
    }
}