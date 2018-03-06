<?php

namespace Kalnoy\Cruddy\Entity\DataSource\Filters;

use Kalnoy\Cruddy\Common\EnumAttr;
use Kalnoy\Cruddy\Entity\DataSource\DataSource;

class Enum extends BaseFilter
{
    use EnumAttr;

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
     * @param $query
     * @param $value
     */
    public function apply($query, $value)
    {
        $query->whereIn($this->id, $this->parse($value));
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return array_merge(parent::getConfig(), [
            'items' => $this->translateItems($this->getItems()),
        ]);
    }

    /**
     * @return string
     */
    public function getUIModelClass()
    {
        return 'Cruddy.Filters.Enum';
    }
}