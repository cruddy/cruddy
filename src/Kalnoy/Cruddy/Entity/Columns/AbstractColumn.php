<?php namespace Kalnoy\Cruddy\Entity\Columns;

use Kalnoy\Cruddy\Entity\Attribute\Attribute;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Kalnoy\Cruddy;

abstract class AbstractColumn extends Attribute implements ColumnInterface {

    /**
     * The default order direction.
     *
     * @var string
     */
    public $order_dir = 'asc';

    /**
     * The formatter class.
     *
     * @var
     */
    public $formatter;

    /**
     * The array of formatter options.
     *
     * @var
     */
    public $formatterOptions;

    /**
     * @inheritdoc
     *
     * @param Builder $builder
     *
     * @return $this
     */
    public function modifyQuery(Builder $builder)
    {
        return $this;
    }

    /**
     * Get the column title.
     *
     * @return mixed
     */
    public function getTitle()
    {
        $title = $this->translate('columns');

        return $title ?: Cruddy\prettify_string($this->id);
    }

    /**
     * @inheritdoc
     *
     * @return array
     */
    public function toArray()
    {
        return
        [
            'title' => $this->getTitle(),
            'sortable' => $this->isSortable(),
            'filterable' => $this->isFilterable(),
            'searchable' => $this->isSearchable(),
            'order_dir' => $this->order_dir,
            'formatter' => $this->formatter,
            'formatterOptions' => $this->formatterOptions,

        ] + parent::toArray();
    }
}