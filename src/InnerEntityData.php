<?php

namespace Kalnoy\Cruddy;

use Illuminate\Database\Eloquent\Model;
use Kalnoy\Cruddy\Contracts\InlineRelation;

class InnerEntityData extends EntityData
{
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
        $this->cid = $cid;

        parent::__construct($relation->getReference(), $data);
    }

    /**
     * @param array $data
     */
    protected function process(array $data)
    {
        $this->id = array_get($data, '__id');
        $this->isDeleted = array_get($data, '__d', false);

        parent::process($data);
    }

    /**
     * @return array|null
     */
    public function getValidationErrors()
    {
        return $this->isDeleted ? null : parent::getValidationErrors();
    }

    /**
     * @param Model $model
     */
    protected function fillModel(Model $model)
    {
        parent::fillModel($model);

        $this->relation->attach($model, $this->parent);
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