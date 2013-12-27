<?php namespace Kalnoy\Cruddy;

use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Builder;

class AttributeCollection extends BaseCollection {

    /**
     * Get data of an eloquent model.
     *
     * @param  Eloquent $model
     *
     * @return array
     */
    public function data(Eloquent $model)
    {
        $data = array();

        foreach ($this->items as $item)
        {
            $value = $item->value($model);

            if ($value instanceof ArrayableInterface)
            {
                $value = $value->toArray();
            }
            elseif (is_object($value))
            {
                $value = (string)$value;
            }

            $data[$item->getId()] = $value;
        }

        return $data;
    }

    /**
     * Apply data() to every item in a collection.
     *
     * @param  array|BaseCollection $collection
     *
     * @return array
     */
    public function collectionData($collection)
    {
        if ($collection instanceof BaseCollection)
        {
            $collection = $collection->all();
        }

        return array_map(array($this, 'data'), $collection);
    }

    /**
     * Get runtime data for every attribute.
     *
     * @param  Eloquent $model
     *
     * @return array
     */
    public function runtime(Eloquent $model)
    {
        return array_values(array_map(function ($item) use ($model) {

            return $item->runtime($model);

        }, $this->items));
    }

    /**
     * Modify a query builder with every attribute.
     *
     * @param  Builder $builder
     *
     * @return AttributeCollection
     */
    public function modifyQuery(Builder $builder)
    {
        array_walk($this->items, function ($item) use ($builder) {

            $item->modifyQuery($builder);
        });

        return $this;
    }
}