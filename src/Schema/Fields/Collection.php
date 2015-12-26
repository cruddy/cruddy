<?php

namespace Kalnoy\Cruddy\Schema\Fields;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Arr;
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
class Collection extends AttributesCollection implements SearchProcessor
{
    /**
     * @var null|array
     */
    protected $searchable;

    /**
     * Process input before validation.
     *
     * @param array $input
     *
     * @return array
     */
    public function process(array $input)
    {
        $result = [ ];

        /**
         * @var Field $field
         */
        foreach ($input as $key => $value) {
            if (($field = $this->get($key)) && $field->keep($value)) {
                $result[$key] = $field->process($value);
            }
        }

        return $result;
    }

    /**
     * Clean input from disabled fields.
     *
     * @param string $action
     * @param array $input
     *
     * @return array
     */
    public function cleanInput($action, array $input)
    {
        $result = [ ];

        foreach ($input as $key => $value) {
            if ( ! $this->get($key)->isDisabled($action)) {
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
        return array_map(function (Field $item) {
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
    protected function applyKeywordsFilter(QueryBuilder $builder,
                                           array $keywords
    ) {
        $builder->whereNested(function ($q) use ($keywords) {
            foreach ($this->searchable() as $item) {
                $item->applyKeywordsFilter($q, $keywords);
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function constraintBuilder(EloquentBuilder $builder, array $options)
    {
        if ($keywords = array_get($options, 'keywords')) {
            $keywords = $this->processKeywords($keywords);

            $this->applyKeywordsFilter($builder->getQuery(), $keywords);
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

    /**
     * Get a list of relations.
     *
     * @param string $owner
     *
     * @return array
     */
    public function relations($owner = null)
    {
        $data = [ ];

        foreach ($this->items as $field) {
            if ($field instanceof BaseRelation) {
                $data = array_merge($data, $field->relations($owner));
            }
        }

        return $data;
    }

    /**
     * @param array|null $value
     */
    public function setSearchableFields($value)
    {
        $this->searchable = $value;
    }

    /**
     * Get searchable fields collection.
     *
     * @return KeywordsFilter[]|array
     */
    protected function searchable()
    {
        $items = $this->searchable
            ? Arr::only($this->items, $this->searchable)
            : $this->items;

        return array_filter($items, function ($item) {
            return $item instanceof KeywordsFilter;
        });
    }
}