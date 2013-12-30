<?php namespace Kalnoy\Cruddy\Entity\Related;

use Kalnoy\Cruddy\Entity\Attribute\Collection as BaseCollection;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class Collection extends BaseCollection {

    public function data(Eloquent $model)
    {
        $data = array();

        foreach ($this->items as $item)
        {
            $value = $item->value($model);
            $fields = $item->getRelated()->fields();

            if ($value instanceof Eloquent)
            {
                $value = $fields->data($value);
            }
            elseif ($value instanceof EloquentCollection)
            {
                $value = $fields->collectionData($value);
            }

            $data[$item->getId()] = $value;
        }

        return $data;
    }
}