<?php

namespace Kalnoy\Cruddy;

use Eloquent;
use Illuminate\Support\Collection;
use Kalnoy\Cruddy\Contracts\InlineRelation;

class InnerDataCollection {

    /**
     * @var InlineRelation
     */
    protected $relation;

    /**
     * @param InlineRelation $relation
     * @param InnerData[] $items
     */
    public function __construct(InlineRelation $relation, array $items = [])
    {
        $this->relation = $relation;
        $this->items = $items;
    }

    /**
     * @param InlineRelation $relation
     * @param array $data
     *
     * @return static
     */
    public static function make(InlineRelation $relation, array $data)
    {
        $items = [];

        foreach ($data as $cid => $item)
        {
            $items[$cid] = new InnerData($relation, $cid, $item);
        }

        return new static($relation, $items);
    }

    /**
     * @param Eloquent $parent
     */
    public function save(Eloquent $parent)
    {
        foreach ($this->items as $item)
        {
            if ( ! $item->isDeleted())
            {
                $item->setParent($parent);
                $item->save();
            }
        }

        $this->deleteModels();
    }

    /**
     * @return static
     */
    protected function getIdsToDelete()
    {
        $items = array_filter($this->items, function (InnerData $item)
        {
            return $item->isDeleted();
        });

        return array_map(function (InnerData $item)
        {
            return $item->getId();

        }, $items);
    }

    /**
     * Delete models.
     */
    protected function deleteModels()
    {
        $ref = $this->relation->getReference();

        if ($ref->isPermitted('delete'))
        {
            $ref->repository()->delete($this->getIdsToDelete());
        }
    }

    /**
     * @return array
     */
    public function getValidationErrors()
    {
        $result = [];

        foreach ($this->items as $cid => $item)
        {
            if ($errors = $item->getValidationErrors())
            {
                $result[$cid] = $errors;
            }
        }

        return $result;
    }
}