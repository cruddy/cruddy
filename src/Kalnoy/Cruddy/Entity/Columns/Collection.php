<?php namespace Kalnoy\Cruddy\Entity\Columns;

use Kalnoy\Cruddy\Entity\Attribute\Collection as BaseCollection;
use Illuminate\Database\Query\Builder;

class Collection extends BaseCollection {

    /**
     * Apply an order to a query builder.
     *
     * @param  Builder $builder
     * @param  array   $order
     *
     * @return Collection
     */
    public function applyOrder(Builder $builder, array $order)
    {
        if (empty($order)) return $this;

        array_walk($order, function ($direction, $id) use ($builder) {

            if (isset($this->items[$id]) && $this->items[$id]->isSortable())
            {
                $this->items[$id]->applyOrder($builder, $direction);
            }

        });

        return $this;
    }

    public function applyConstraints(Builder $builder, array $data)
    {
        if (empty($data)) return $this;

        array_walk($data, function ($value, $id) use ($builder) {

            if ($value === "") return;

            if (isset($this->items[$id]) && $this->items[$id]->isFilterable())
            {
                $this->items[$id]->applyConstraints($builder, $value);
            }

        });

        return $this;
    }

    /**
     * Search items by a string query.
     *
     * @param Builder $builder
     * @param string  $query
     * @return $this
     */
    public function search(Builder $builder, $query)
    {
        if (empty($query)) return $this;

        foreach ($this->items as $item)
        {
            if ($item->isFilterable() && $item->isSearchable())
            {
                $item->applyConstraints($builder, $query, 'or');
            }
        }

        return $this;
    }
}