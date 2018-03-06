<?php

namespace Kalnoy\Cruddy\Common;

use Kalnoy\Cruddy\Helpers;

trait EnumAttr
{
    /**
     * @var array|callable
     */
    protected $items;

    /**
     * @return array
     */
    public function getItems()
    {
        if (is_callable($this->items)) {
            $this->items = call_user_func($this->items);
        }

        return $this->items;
    }

    /**
     * Translate items if possible.
     *
     * @param array $items
     *
     * @return array
     */
    protected function translateItems($items)
    {
        foreach ($items as $key => $value) {
            $items[$key] = Helpers::tryTranslate($value);
        }

        return $items;
    }

    /**
     * @param $value
     *
     * @return array
     */
    protected function parse($value)
    {
        if (empty($value)) return [];

        if (is_string($value)) {
            return explode(',', $value);
        }

        return $value;
    }
}