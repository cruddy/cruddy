<?php

namespace Kalnoy\Cruddy\Schema\Fields;

use RuntimeException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Kalnoy\Cruddy\Repo\SearchProcessorInterface;
use Kalnoy\Cruddy\Schema\AttributeInterface;

/**
 * The base class for relation that will be selectable in drop down list.
 */
abstract class BasicRelation extends BaseRelation implements SearchProcessorInterface {

    /**
     * @inheritdoc
     *
     * @var string
     */
    protected $class = 'Relation';

    /**
     * @inheritdoc
     *
     * @var string
     */
    protected $type = 'relation';

    /**
     * Whether the relation will return collection rather than a single model.
     *
     * @var bool
     */
    protected $multiple;

    /**
     * The constraint with other field.
     *
     * @var array
     */
    protected $constraint;

    /**
     * The filter that will be applied to the query builder.
     *
     * @var mixed
     */
    public $filter;

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
     * Constraint options with other field.
     *
     * @param string $field
     * @param string $otherField
     *
     * @return $this
     */
    public function constraintWith($field, $otherField = null)
    {
        if ($otherField === null) $otherField = $field;

        if ( ! $this->entity->getFields()->get($field))
        {
            throw new RuntimeException("The field [{$this->entity->getId()}.{$field}] is not defined.");
        }

        if ( ! $this->reference->getColumns()->get($otherField))
        {
            throw new RuntimeException("The column [{$this->reference->getId()}.{$otherField}] is not defined.");
        }

        $this->constraint = compact('field', 'otherField');

        return $this;
    }

    /**
     * @inheritdoc
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return mixed
     */
    public function extract(Eloquent $model)
    {
        // Strange, but relations are still resolved even when model doesn't
        // really exists, so wee need to handle this case.
        if ( ! $model->exists)
        {
            return $this->multiple ? [] : null;
        }

        $data = $model->{$this->id};

        return $data ? $this->reference->simplify($data) : null;
    }

    /**
     * @inheritdoc
     * 
     * @param mixed $data
     *
     * @return mixed
     */
    public function process($data)
    {
        if (empty($data)) return null;

        return $this->multiple ? array_pluck($data, 'id') : $data['id'];
    }

    /**
     * @inheritdoc
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array                                 $options
     *
     * @return void
     */
    public function search(Builder $query, array $options)
    {
        if (isset($this->filter))
        {
            call_user_func($this->filter, $query, $options);
        }
    }

    /**
     * @inheritdoc
     *
     * @return array
     */
    public function toArray()
    {
        return
        [
            'multiple' => $this->multiple,
            'constraint' => $this->constraint,

        ] + parent::toArray();
    }

}