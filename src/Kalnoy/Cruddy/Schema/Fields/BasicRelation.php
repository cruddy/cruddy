<?php

namespace Kalnoy\Cruddy\Schema\Fields;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Kalnoy\Cruddy\Repo\SearchProcessorInterface;

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

        ] + parent::toArray();
    }

}