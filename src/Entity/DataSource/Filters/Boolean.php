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
        if ($this->callback) {
            call_user_func($this->callback, $query, $value);
        } else {
            $query->where($this->id, $value);
        }
    }

    /**
     * @return string
     */
    public function getUIModelClass()
    {
        return 'Cruddy.Filters.Boolean';
    }
}