<?php

namespace Kalnoy\Cruddy;

use Illuminate\Database\Eloquent\Model;
use Kalnoy\Cruddy\Contracts\InlineRelation;

class InnerData extends Data {

    /**
     * @var null|string
     */
    protected $cid;

    /**
     * @var InlineRelation
     */
    protected $relation;

    /**
     * @var bool
     */
    protected $isDeleted;

    /**
     * @var Model
     */
    protected $parent;

    /**
     * @param InlineRelation $relation
     * @param array $cid
     * @param array $data
     */
    public function __construct(InlineRelation $relation, $cid, array $data)
    {
        $this->relation = $relation;
        $this->entity = $relation->getReference();
        $this->cid = $cid;

        $this->process($data);
    }

    /**
     * @param array $data
     */
    protected function process(array $data)
    {
        $this->id = array_get($data, 'id');
        $this->isDeleted = array_get($data, 'isDeleted', false);

        parent::process(array_get($data, 'attributes', []));
    }

    /**
     * @param Model $model
     *
     * @return array|null
     */
    public function getValidationErrors()
    {
        return $this->isDeleted ? null : parent::getValidationErrors();
    }

    protected function fillModel(Model $model)
    {
        parent::fillModel($model);

        $this->relation->joinModels($model, $this->parent);
    }

    /**
     * @return mixed
     */
    public function isDeleted()
    {
        return $this->isDeleted;
    }

    /**
     * @param Model $model
     */
    public function setParent(Model $model)
    {
        $this->parent = $model;
    }
}