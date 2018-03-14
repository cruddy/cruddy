<?php

namespace Kalnoy\Cruddy\Entity\DataSource\Columns;

use Illuminate\Database\Eloquent\Model;
use Kalnoy\Cruddy\Entity\DataSource\DataSource;
use Kalnoy\Cruddy\Helpers;

class Enum extends Attribute
{
    /**
     * @var array|callable
     */
    protected $items;

    /**
     * Enum constructor.
     *
     * @param DataSource $owner
     * @param $id
     * @param $items
     */
    public function __construct(DataSource $owner, $id, $items)
    {
        parent::__construct($owner, $id);

        $this->items = $items;
    }

    /**
     * @inheritDoc
     */
    public function modelValue(Model $model, $attr)
    {
        $value = data_get($model, $attr);

        if (is_null($value) || $value === '') {
            return null;
        }

        $items = $this->getItems();

        return implode(', ', array_map(function ($key) use ($items) {
            if ( ! isset($items[$key])) {
                return $key;
            }

            return Helpers::tryTranslate($items[$key]);
        }, (array)$value));
    }

    /**
     * @return mixed
     */
    public function getItems()
    {
        if (is_callable($this->items)) {
            $this->items = call_user_func($this->items);
        }

        return $this->items;
    }


}