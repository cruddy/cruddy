<?php

namespace Kalnoy\Cruddy\Entity\DataSource;

use Illuminate\Database\Eloquent\Model;
use Kalnoy\Cruddy\Entity\DataSource\Columns\BaseColumn;
use Kalnoy\Cruddy\Service\BaseCollection;

/**
 * Class ColumnsCollection
 *
 * @method Columns\Attribute attr(string $id)
 * @method Columns\Attribute attribute(string $id)
 *
 * @method Columns\Computed compute(string $id, callback $getter)
 * @method Columns\Computed computed(string $id, callback $getter)
 *
 * @method Columns\EntityColumn entity(string $id, string $refEntityId = null)
 * @method Columns\Enum enum(string $id, $items)
 * @method Columns\Boolean bool(string $id)
 * @method Columns\Boolean boolean(string $id)
 *
 * @package Kalnoy\Cruddy\Entity\DataSource
 */
class ColumnsCollection extends BaseCollection
{
    /**
     * @var DataSource
     */
    protected $owner;

    /**
     * @param Model $model
     *
     * @return array
     */
    public function modelData(Model $model)
    {
        return array_map(function (BaseColumn $column) use ($model) {
            return $column->getModelValue($model);
        }, $this->items);
    }

    /**
     * @return array
     */
    public function relationships()
    {
        return array_reduce($this->items, function ($carry, BaseColumn $column) {
            return array_merge($carry, $column->relationships());
        }, []);
    }
}