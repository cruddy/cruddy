<?php

namespace Kalnoy\Cruddy\Entity\DataSource\Filters;

class Boolean extends BaseFilter
{
    /**
     * @param $query
     * @param $value
     */
    public function apply($query, $value)
    {
        $query->where($this->id, $value);
    }

    /**
     * @return string
     */
    public function getUIModelClass()
    {
        return 'Cruddy.Filters.Boolean';
    }
}