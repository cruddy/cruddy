<?php

namespace Kalnoy\Cruddy\Schema\Fields;

use Illuminate\Database\Eloquent\Model as Eloquent;

/**
 * The base class for relation that will be selectable in drop down list.
 */
abstract class BasicRelation extends BaseRelation {

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