<?php

namespace Kalnoy\Cruddy\Schema\Fields;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Kalnoy\Cruddy\Schema\BaseCollection;
use Kalnoy\Cruddy\Schema\FieldInterface;
use Kalnoy\Cruddy\Repo\SearchProcessorInterface;

class Collection extends BaseCollection implements SearchProcessorInterface {

    /**
     * Process input before validation.
     *
     * @param array $input
     *
     * @return array
     */
    public function process(array $input)
    {
        $result = [];

        foreach ($this->items as $key => $field)
        {
            if (array_key_exists($key, $input) && $field->keep($value = $input[$key]))
            {
                $result[$key] = $field->process($value);
            }
        }

        return $result;
    }

    /**
     * Clean input from disabled fields.
     *
     * @param string $action
     * @param array  $input
     *
     * @return array
     */
    public function cleanInput($action, array $input)
    {
        $result = [];

        foreach ($input as $key => $value)
        {
            if ($this->items[$key]->sendToRepository($action))
            {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Get validation labels.
     *
     * @return array
     */
    public function validationLabels()
    {
        return array_map(function ($item)
        {
            return mb_strtolower($item->getLabel());

        }, $this->items);
    }

    /**
     * Filter by keywords.
     *
     * @param QueryBuilder $builder
     * @param string       $keywords
     *
     * @return void
     */
    protected function filterByKeywords(QueryBuilder $builder, $keywords)
    {
        $builder->whereNested(function ($q) use ($keywords)
        {
            foreach ($this->items as $item)
            {
                if ($item->getFilterType() === FieldInterface::FILTER_STRING)
                {
                    $item->filter($q, $keywords);
                }
            } 
        });
    }

    /**
     * Apply complex filters.
     *
     * @param QueryBuilder $builder
     * @param array        $data
     *
     * @return void
     */
    protected function filterByData(QueryBuilder $builder, array $data)
    {
        foreach ($data as $key => $value)
        {
            if ( ! empty($value) && $this->has($key))
            {
                $item = $this->get($key);

                if ($item->getFilterType() !== FieldInterface::FILTER_NONE)
                {
                    $item->filter($builder, $value);
                }
            }
        }
    }

    /**
     * @inheritdoc
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param array                                 $options
     *
     * @return void
     */
    public function search(EloquentBuilder $builder, array $options)
    {
        $query = $builder->getQuery();

        if ($value = \array_get($options, 'keywords'))
        {
            $this->filterByKeywords($query, $value);
        }

        if ($value = \array_get($options, 'filters'))
        {
            $this->filterByData($query, $value);
        }
    }
}