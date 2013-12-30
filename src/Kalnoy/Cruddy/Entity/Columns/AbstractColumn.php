<?php namespace Kalnoy\Cruddy\Entity\Columns;

use Kalnoy\Cruddy\Entity\Attribute\Attribute;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Builder;

abstract class AbstractColumn extends Attribute implements ColumnInterface {

    public $order_dir = 'asc';

    public function modifyQuery(Builder $builder)
    {
        return $this;
    }

    public function getTitle()
    {
        $title = $this->translate("columns");

        return $title ?: humanize($this->id);
    }

    public function toArray()
    {
        return parent::toArray() + array(
            'title' => $this->getTitle(),
            'sortable' => $this->isSortable(),
            'filterable' => $this->isFilterable(),
            'order_dir' => $this->order_dir,
        );
    }
}