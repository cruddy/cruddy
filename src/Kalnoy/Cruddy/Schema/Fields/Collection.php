<?php

namespace Kalnoy\Cruddy\Schema\Fields;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Kalnoy\Cruddy\Contracts\Filter;
use Kalnoy\Cruddy\Contracts\KeywordsFilter;
use Kalnoy\Cruddy\Schema\AttributesCollection;
use Kalnoy\Cruddy\Contracts\Field;
use Kalnoy\Cruddy\Contracts\SearchProcessor;

/**
 * Fields collection.
 *
 * @since 1.0.0
 */
class Collection extends AttributesCollection implements SearchProcessor {

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

        /**
         * @var Field $field
         */
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
            if ( ! $this->get($key)->isDisabled($action))
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
        return array_map(function (Field $item)
        {
            return mb_strtolower($item->getLabel());

        }, $this->items);
    }

    /**
     * Filter by keywords.
     *
     * @param \Illuminate\Database\Query\Builder $builder
     * @param array $keywords
     *
     * @return void
     */
    protected function applyKeywordsFilter(QueryBuilder $builder, array $keywords)
    {
        $builder->whereNested(function ($q) use ($keywords)
        {
            /**
             * @var KeywordsFilter $item
             */
            foreach ($this->items as $item)
            {
                if ($item instanceof KeywordsFilter)
                {
                    $item->applyKeywordsFilter($q, $keywords);
                }
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function constraintBuilder(EloquentBuilder $builder, array $options)
    {
        if ($value = array_get($options, 'keywords'))
        {
            $this->applyKeywordsFilter($builder->getQuery(), $this->processKeywords($value));
        }
    }

    /**
     * @param $keywords
     *
     * @return array
     */
    protected function processKeywords($keywords)
    {
        return preg_split('/\s/', $keywords, -1, PREG_SPLIT_NO_EMPTY);
    }
}