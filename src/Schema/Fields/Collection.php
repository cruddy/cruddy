<?php

namespace Kalnoy\Cruddy\Schema\Fields;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Arr;
use Kalnoy\Cruddy\Contracts\Filter;
use Kalnoy\Cruddy\Contracts\KeywordsFilter;
use Kalnoy\Cruddy\Contracts\ValidatingField;
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
     * @return $this
     */
    public function parseInput(array &$input)
    {
        array_walk($input, function (&$value, $key) {
            if ($this->has($key)) {
                $value = $this->get($key)->parseInputValue($value);
            }
        });

        return $this;
    }

    /**
     * @param $mode
     * @param $model
     * @param array $input
     *
     * @return $this
     */
    public function fillModel($mode, $model, array $input)
    {
        /** @var Field $field */
        foreach ($this->items as $key => $field) {
            if ($field->getSettingMode() == $mode &&
                array_key_exists($key, $input) &&
                ! $field->isDisabled($model)
            ) {
                $field->setModelValue($model, $input[$key]);
            }
        }

        return $this;
    }

    /**
     * Get validation labels.
     *
     * @return array
     */
    public function getValidationLabels()
    {
        $labels = [];

        foreach ($this->items as $key => $field) {
            $labels[$key] = mb_strtolower($field->getLabel());

            // Add validation labels for inline forms
            if ($field instanceof InlineRelation) {
                $innerLabels = $field->getRefEntity()
                               ->getFields()
                               ->getValidationLabels();

                foreach ($innerLabels as $innerKey => $label) {
                    if ($field->isMultiple()) {
                        $innerKey = "*.{$innerKey}";
                    }

                    $labels["{$key}.{$innerKey}"] = $label;
                }
            }
        }

        return $labels;
    }

    /**
     * Filter by keywords.
     *
     * @param \Illuminate\Database\Query\Builder $builder
     * @param array $keywords
     *
     * @return void
     */
    protected function applyKeywordsFilter($builder, array $keywords)
    {
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
        if ($keywords = Arr::get($options, 'keywords')) {
            $this->applyKeywordsFilter($builder->getQuery(), (array)$keywords);
        }
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
        $fields = $this->items;

        if ($this->searchable) {
            $fields = Arr::only($fields, $this->searchable);
        }

        return array_filter($fields, function ($item) {
            return $item instanceof KeywordsFilter;
        });
    }

    /**
     * Validates an input and returns errors if any.
     *
     * @param array $input
     * @param $modelKey
     *
     * @return array
     */
    public function validateInner(array $input)
    {
        $result = [];

        foreach ($this->items as $key => $field) {
            if ( ! $field instanceof InlineRelation) continue;

            $errors = $field->validate(Arr::get($input, $key));

            foreach ($errors as $innerKey => $innerErrors) {
                $result["{$key}.{$innerKey}"] = $innerErrors;
            }
        }

        return $result;
    }

    public function getRules($modelKey)
    {
        return array_filter(array_map(function (Field $field) use ($modelKey) {
            return $field->getRules($modelKey);
        }, $this->items));
    }
}