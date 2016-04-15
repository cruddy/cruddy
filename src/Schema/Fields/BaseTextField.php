<?php

namespace Kalnoy\Cruddy\Schema\Fields;

use Illuminate\Database\Query\Builder;
use Kalnoy\Cruddy\Contracts\Filter as FilterContract;
use Kalnoy\Cruddy\Contracts\KeywordsFilter as KeywordsFilterContract;
use Kalnoy\Cruddy\Helpers;

/**
 * Base text field class.
 *
 * This kind of fields don't have complex filters.
 *
 * @method $this placeholder(string $value)
 * @property string $placeholder
 *
 * @since 1.0.0
 */
abstract class BaseTextField extends BaseInput implements KeywordsFilterContract,
                                                          FilterContract
{
    /**
     * The name of the JavaScript class that is used to render this field.
     *
     * @return string
     */
    protected function getModelClass()
    {
        return 'Cruddy.Fields.Input';
    }

    /**
     * Get the type of the <input> tag.
     *
     * @return string
     */
    protected function getInputType()
    {
        return 'text';
    }

    /**
     * {@inheritdoc}
     */
    public function parseInputValue($value)
    {
        return Helpers::processString($value);
    }

    /**
     * @param Builder $builder
     * @param array $keywords
     *
     * @return void
     */
    public function applyKeywordsFilter(Builder $builder, array $keywords)
    {
        foreach ($keywords as $keyword) {
            $builder->orWhere($this->getModelAttributeName(), 'like', '%'.$keyword.'%');
        }
    }

    /**
     * @inheritDoc
     */
    public function applyFilterConstraint(Builder $builder, $input)
    {
        if ($input = Helpers::processString($input)) {
            $builder->where($this->getModelAttributeName(), 'like', '%'.$input.'%');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'input_type' => $this->getInputType(),
            'placeholder' => Helpers::tryTranslate($this->get('placeholder')),

        ] + parent::toArray();
    }

}