<?php namespace Kalnoy\Cruddy\Entity\Fields\Types;

use Illuminate\Database\Eloquent\Builder;
use Kalnoy\Cruddy\Entity\Columns\ColumnInterface;
use Kalnoy\Cruddy\Entity\Fields\AbstractField;

class Boolean extends AbstractField implements ColumnInterface {

    public function process($value)
    {
        return $value === 'true' || $value === '1' || $value === 'on' ? 1 : 0;
    }

    public function applyConstraints(Builder $builder, $data)
    {
        $data = $this->process($data);

        $builder->where($this->id, $data ? 1 : 0);

        return $this;
    }

    public function applyOrder(Builder $builder, $direction)
    {
        $builder->orderBy($this->id, $direction);

        return $this;
    }

    public function isSortable()
    {
        return true;
    }

    public function isFilterable()
    {
        return true;
    }

    public function getJavaScriptClass()
    {
        return 'Boolean';
    }
}