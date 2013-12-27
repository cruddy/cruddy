<?php namespace Kalnoy\Cruddy\Fields;

use Illuminate\Database\Eloquent\Builder;
use Kalnoy\Cruddy\Columns\ColumnInterface;

abstract class Input extends AbstractField implements ColumnInterface {

    /**
     * The input type.
     *
     * @var string
     */
    protected $inputType;

    /**
     * The placeholder.
     *
     * @var string
     */
    public $placeholder;

    /**
     * Whether the value is required.
     *
     * @var bool
     */
    public $required = false;

    /**
     * Apply constraints to the query builder.
     *
     * @param  Builder $query
     * @param  mixed  $data
     *
     * @return Input
     */
    public function applyConstraints(Builder $query, $data)
    {
        $query->where($this->id, 'like', '%'.$data.'%');

        return $this;
    }

    /**
     * Apply an order to a query builder.
     *
     * @param  Builder $builder
     * @param  string  $direction
     *
     * @return Input
     */
    public function applyOrder(Builder $builder, $direction)
    {
        $builder->orderBy($this->id, $direction);

        return $this;
    }

    /**
     * Get whether the field is sortable.
     *
     * @return bool
     */
    public function isSortable()
    {
        return true;
    }

    public function isFilterable()
    {
        return true;
    }

    /**
     * Convert a field into a configuration array.
     *
     * @return array
     */
    public function toArray()
    {
        return parent::toArray() + array(
            'input_type' => $this->inputType,
            'placeholder' => $this->placeholder,
            'required' => $this->required,
        );
    }

    /**
     * Get the java script class name.
     *
     * @return string
     */
    public function getJavaScriptClass()
    {
        return 'Input';
    }
}