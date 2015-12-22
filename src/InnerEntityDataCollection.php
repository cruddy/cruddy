<?php

namespace Kalnoy\Cruddy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Kalnoy\Cruddy\Contracts\InlineRelation;
use Kalnoy\Cruddy\Contracts\Permissions;

class InnerEntityDataCollection
{
    /**
     * @var InlineRelation
     */
    protected $relation;

    /**
     * @param InlineRelation $relation
     * @param InnerEntityData[] $items
     */
    public function __construct(InlineRelation $relation, array $items = [ ])
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
        $items = [ ];

        foreach ($data as $cid => $item) {
            $items[$cid] = new InnerEntityData($relation, $cid, $item);
        }

        return new static($relation, $items);
    }

    /**
     * @param Model $parent
     */
    public function save(Model $parent)
    {
        foreach ($this->items as $item) {
            if ( ! $item->isDeleted()) {
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
        $items = array_filter($this->items, function (InnerEntityData $item) {
            return $item->isDeleted();
        });

        return array_map(function (InnerEntityData $item) {
            return $item->getId();
        }, $items);
    }

    /**
     * Delete models.
     */
    protected function deleteModels()
    {
        $ref = $this->relation->getReference();

        if ($ref->isPermitted(Entity::DELETE)) {
            $ref->getRepository()->delete($this->getIdsToDelete());
        }
    }

    /**
     * @return array
     */
    public function getValidationErrors()
    {
        $result = [ ];

        foreach ($this->items as $cid => $item) {
            if ($errors = $item->getValidationErrors()) {
                $result[$cid] = $errors;
            }
        }

        return $result;
    }
}