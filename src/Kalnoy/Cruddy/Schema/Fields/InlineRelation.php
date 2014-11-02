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
     * {@inheritdoc}
     */
    protected $class = 'Cruddy.Fields.Embedded';

    /**
     * {@inheritdoc}
     */
    protected $type = 'inline-relation';

    /**
     * Whether the model relates to many items.
     *
     * @var bool
     */
    protected $multiple = false;

    /**
     * Extra attributes that will be set on model.
     *
     * @var array|\Closure
     */
    public $extra = [];

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
    public function joinModels(Model $model, Model $parent)
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
    public function extract(Model $model)
    {
        $items = parent::extract($model);

        $items and $this->loadRelations($items);

        return $this->reference->extract($items);
    }

    /**
     * {@inheritdoc}
     */
    public function extractForColumn(Model $model)
    {
        return $this->reference->simplify(parent::extract($model));
    }

    /**
     * {@inheritdoc}
     */
    protected function appendPreloadableRelations(array &$items, $key = null)
    {
        $relationId = $this->getKeyedRelationId($key);

        $items[] = $relationId;

        $this->appendReferenceRelations($items, $relationId);
    }

    /**
     * Append preloadables from reference entity.
     *
     * @param array  $items
     * @param string $key
     *
     * @return void
     */
    protected function appendReferenceRelations(array &$items, $key = null)
    {
        foreach ($this->reference->getFields() as $field)
        {
            if ($field instanceof BaseRelation)
            {
                $field->appendPreloadableRelations($items, $key);
            }
        }
    }

    /**
     * Find inner relations and load them on target model.
     *
     * @param mixed $loadee
     *
     * @return void
     */
    protected function loadRelations($loadee)
    {
        $relations = [];

        $this->appendReferenceRelations($relations);

        if ($relations) $loadee->load($relations);
    }

    /**
     * {@inheritdoc}
     */
    protected function generateLabel()
    {
        if ($label = $this->translate('fields')) return $label;

        return $this->multiple ? $this->reference->getPluralTitle() : $this->reference->getSingularTitle();
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return
        [
            'multiple' => $this->multiple,

        ] + parent::toArray();
    }

}