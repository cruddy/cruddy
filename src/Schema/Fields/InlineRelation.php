<?php

namespace Kalnoy\Cruddy\Schema\Fields;

use Illuminate\Database\Eloquent\Model;
use Kalnoy\Cruddy\Contracts\InlineRelation as InlineRelationContract;
use Kalnoy\Cruddy\Service\Validation\ValidationException;

/**
 * Inline relation allows to edit related models inlinely.
 *
 * @since 1.0.0
 */
abstract class InlineRelation extends BaseRelation implements InlineRelationContract {

    /**
     * Extra attributes that will be set on model.
     *
     * @var array|\Closure
     */
    public $extra = [];

    /**
     * The name of the JavaScript class that is used to render this field.
     *
     * @return string
     */
    protected function modelClass()
    {
        return 'Cruddy.Fields.Embedded';
    }

    /**
     * Set an extra attributes that will be set on model.
     *
     * Note that this attributes will not overwrite request data.
     *
     * @param array $value
     *
     * @return $this
     */
    public function extra($value)
    {
        $this->extra = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * Returns just the number of items so we could validate it.
     */
    public function process($data)
    {
        return array_reduce($data, function ($carry, $item)
        {
            return $carry + (array_get($item, 'isDeleted') ? 0 : 1);

        }, 0);
    }

    /**
     * {@inheritdoc}
     */
    public function keep($value)
    {
        return ! empty($value);
    }

    /**
     * @param Model $model
     * @param Model $parent
     *
     * @return void
     */
    public function attach(Model $model, Model $parent)
    {
        $extra = $this->extra;

        if ($extra instanceof \Closure)
        {
            $extra($model);
        }
        else
        {
            $model->fill($extra);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function extract($model)
    {
        $items = parent::extract($model);

        $items and $this->loadRelations($items);

        return $this->reference->extract($items);
    }

    /**
     * @param string $owner
     *
     * @return array
     */
    public function relations($owner)
    {
        $relations = parent::relations($owner);

        $owner = $owner ? $owner.'.'.$this->reference->getId() : $this->reference->getId();

        return array_merge($relations, $this->reference->relations($owner));
    }

    /**
     * {@inheritdoc}
     */
    public function extractForColumn($model)
    {
        return $this->reference->simplify(parent::extract($model));
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
        $relations = $this->reference->relations();

        if ($relations) $loadee->load($relations);
    }

    /**
     * {@inheritdoc}
     */
    protected function generateLabel()
    {
        if ($label = $this->translate('fields')) return $label;

        return $this->isMultiple() ? $this->reference->getPluralTitle() : $this->reference->getSingularTitle();
    }

}