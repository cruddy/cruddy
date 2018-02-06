<?php

namespace Kalnoy\Cruddy\Entity\Fields;

use Illuminate\Database\Eloquent\Model;
use Kalnoy\Cruddy\Form\BaseForm;
use Kalnoy\Cruddy\Contracts\Filter;
use Kalnoy\Cruddy\Entity\Fields\BaseRelation;
use RuntimeException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Kalnoy\Cruddy\Contracts\SearchProcessor;
use Kalnoy\Cruddy\Contracts\Field;

/**
 * The base class for relation that will be selectable in drop down list.
 *
 * @package \Kalnoy\Cruddy\Entity\Fields
 */
abstract class BaseEntitySelector extends BaseRelation
{
    /**
     * The filter that will be applied to the query builder.
     *
     * @var mixed
     */
    public $filter;

    protected $rules = [ 'nullable' => [] ];

    /**
     * The name of the JavaScript class that is used to render this field.
     *
     * @return string
     */
    public function getUIModelClass()
    {
        return 'Cruddy.Fields.Relation';
    }

    /**
     * Set the query filter.
     *
     * @param mixed $filter
     *
     * @return $this
     */
    public function filterOptions($filter)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * @param mixed $value
     *
     * @return array|mixed
     */
    public function processValueBeforeValidating($value)
    {
        $value = $this->parseData($value);

        return $this->isMultiple() ? $value : (empty($value) ? null : reset($value));
    }

    /**
     * @inheritdoc
     */
    public function getModelValue($model)
    {
        // Strange, but relations are still resolved even when model doesn't
        // really exists, so wee need to handle this case.
        if ( ! $model->exists) {
            return $this->isMultiple() ? [] : null;
        }

        return parent::getModelValue($model);
    }

    /**
     * @param Eloquent $model
     * @param string $attr
     *
     * @return array|null
     */
    public function getRelationValue(Model $model, $attr)
    {
        return $this
            ->getRefEntity()
            ->getSimpleDataSource()
            ->data(parent::getRelationValue($model, $attr));
    }

    /**
     * @param string $data
     *
     * @return array
     */
    protected function parseData($data)
    {
        if (is_array($data)) return $data;

        if (empty($data)) return [];

        return explode(',', $data);
    }

    /**
     * Define exists rule with constraints.
     *
     * The exists rule is applied by default; use this method only to define
     * additional constraints.
     *
     * ```php
     * $form->belongsTo('foo')->exists('bar', 'NOT_NULL', 'type', 'baz');
     * ```
     *
     * @param array $constraints
     *
     * @return $this
     */
    public function exists($constraints)
    {
        $constraints = is_array($constraints) ? $constraints : func_get_args();

        $this->rules['exists'] = $this->existsRuleConfig($constraints);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getConfig()
    {
        return [
            'constraint' => null,

        ] + parent::getConfig();
    }

    /**
     * @inheritdoc
     */
    public function getRules()
    {
        $rules = parent::getRules();

        if ( ! isset($rules['exists'])) {
            $rules['exists'] = $this->existsRuleConfig();
        }

        return $rules;
    }

    /**
     * @param array $constraints
     *
     * @return array
     */
    protected function existsRuleConfig(array $constraints = [])
    {
        $model = $this->getRefEntity()->newModel();

        array_unshift($constraints, $model->getTable(), $model->getKeyName());

        return $constraints;
    }
}