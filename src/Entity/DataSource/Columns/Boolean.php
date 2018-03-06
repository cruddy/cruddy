<?php

namespace Kalnoy\Cruddy\Entity\DataSource\Columns;

use Illuminate\Database\Eloquent\Model;

class Boolean extends Attribute
{
    /**
     * @param Model $model
     * @param $attr
     *
     * @return mixed
     */
    public function modelValue(Model $model, $attr)
    {
        if (null === $value = data_get($model, $attr)) {
            return null;
        }

        return $value
            ? '<span class="glyphicon glyphicon-ok text-success"></span>'
            : '<span class="glyphicon glyphicon-remove text-danger"></span>';
    }
}