<?php

namespace Kalnoy\Cruddy\Schema\Fields;

use Illuminate\Database\Eloquent\Model as Eloquent;
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
        if ($this->multiple) return count($data);

        return 1;
    }

    /**
     * {@inheritdoc}
     */
    public function keep($value)
    {
        return ! empty($value);
    }

    /**
     * {@inheritdoc}
     */
    public function processInput($input)
    {
        if ( ! is_array($input)) return [];

        if ($this->multiple) return $this->processMany($input);

        return [ $this->reference->process($input) ];
    }

    /**
     * Process many items. This is needed to capture validation errors.
     *
     * @param array $input
     *
     * @return array
     */
    public function processMany(array $input)
    {
        $errors = [];
        $result = [];

        foreach ($input as $cid => $item)
        {
            try
            {
                $result[] = $this->reference->process($item);
            }

            catch (ValidationException $e)
            {
                // Remember errors by cid since we might be creating new items
                // that don't have an id
                $errors[$cid] = $e->getErrors();
            }
        }

        if ( ! empty($errors)) throw new ValidationException($errors);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function save(Eloquent $model, array $data)
    {
        $ref    = $this->reference;
        $permit = $ref->getPermissions();

        // Get current items and check if some needs to be deleted
        $delete = $this->newRelationalQuery($model)->lists('id');
        $ids = [];

        foreach ($data as $item)
        {
            $action = $ref->actionFromData($item);

            if ( ! $permit[$action]) continue;

            $item['extra'] = $this->mergeExtra($action, $this->getExtra($model));

            $ref->save($item);

            if ($action === 'update') $ids[] = $item['id'];
        }

        if ( ! empty($ids)) $delete = array_diff($delete, $ids);

        if ( ! empty($delete) && $permit['delete']) $ref->delete($delete);
    }

    /**
     * Merge data with some extra attributes that user may have provided.
     *
     * @param string $action
     * @param array $data
     *
     * @return array
     */
    protected function mergeExtra($action, array $data)
    {
        $extra = $this->extra;

        if ($extra instanceof \Closure)
        {
            $extra = $extra($action);
        }
        else
        {
            $extra = $action === 'create' ? $extra : [];
        }

        return $data + (array)$extra;
    }

    /**
     * Get extra attributes.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return array
     */
    abstract public function getExtra($model);

    /**
     * {@inheritdoc}
     */
    public function extract(Eloquent $model)
    {
        $items = parent::extract($model);

        $items and $this->loadRelations($items);

        return $this->reference->extract($items);
    }

    /**
     * {@inheritdoc}
     */
    public function extractForColumn(Eloquent $model)
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