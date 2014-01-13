<?php namespace Kalnoy\Cruddy\Entity\Fields\Types;

use Illuminate\Database\Query\Builder;
use Kalnoy\Cruddy\Entity\Attribute\Attribute;
use Kalnoy\Cruddy\Entity\Columns\ColumnInterface;
use Kalnoy\Cruddy\Entity\Fields\AbstractField;

class Enum extends AbstractField implements ColumnInterface {

    /**
     * @var array|Callable
     */
    public $items;

    /**
     * @var string
     */
    public $prompt;

    /**
     * Get a JavaScript class name that will serve the attribute.
     *
     * @return string
     */
    function getJavaScriptClass()
    {
        return 'Enum';
    }

    /**
     * Get whether the column can be sorted.
     *
     * @return bool
     */
    public function isSortable()
    {
        return true;
    }

    /**
     * Get whether column can filter data.
     *
     * @return bool
     */
    public function isFilterable()
    {
        return true;
    }

    /**
     * Get whether column can search using "search everything" feature.
     *
     * @return bool
     */
    public function isSearchable()
    {
        return false;
    }

    /**
     * Apply an order to the query builder.
     *
     * @param  Builder $builder
     * @param          $direction
     *
     * @return $this
     */
    public function applyOrder(Builder $builder, $direction)
    {
        $builder->orderBy($this->id, $direction);

        return $this;
    }

    /**
     * Apply constraints to the query builder.
     *
     * @param  Builder $query
     * @param  mixed   $data
     * @param string   $boolean
     *
     * @return $this
     */
    public function applyConstraints(Builder $query, $data, $boolean = 'and')
    {
        $query->where($this->id, '=', $data, $boolean);

        return $this;
    }

    public function toArray()
    {
        return parent::toArray() +
        [
            'prompt' => \Kalnoy\Cruddy\try_trans($this->prompt),
            'items' => $this->evaluate($this->items, $this->entity->form()->instance()),
        ];
    }
}