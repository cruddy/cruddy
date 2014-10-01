<?php

namespace Kalnoy\Cruddy\Schema\Fields;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

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
abstract class BaseTextField extends BaseInput {

    /**
     * {@inheritdoc}
     */
    protected $class = 'Input';

    /**
     * {@inheritdoc}
     */
    protected $filterType = self::FILTER_STRING;

    /**
     * The HTML <input> type attribute value.
     *
     * @var string
     */
    protected $inputType = 'text';

    /**
     * {@inheritdoc}
     */
    public function process($value)
    {
        $value = trim($value);

        return $value === '' ? null : $value;
    }

    /**
     * {@inheritdoc}
     */
    public function order(QueryBuilder $builder, $direction)
    {
        $builder->orderBy($this->id, $direction);

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * Simple keywords search.
     */
    public function filter(QueryBuilder $builder, $data)
    {
        $builder->orWhere($this->id, 'like', '%'.$data.'%');

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return
        [
            'input_type' => $this->inputType,
            'placeholder' => \Kalnoy\Cruddy\try_trans($this->get('placeholder')),

        ] + parent::toArray();
    }

}