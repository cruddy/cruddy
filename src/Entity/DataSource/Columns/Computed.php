<?php

namespace Kalnoy\Cruddy\Entity\DataSource\Columns;

use Kalnoy\Cruddy\Entity\DataSource\DataSource;

/**
 * Class Computed
 *
 * @package Kalnoy\Cruddy\Entity\DataSource\Columns
 */
class Computed extends BaseColumn
{
    /**
     * Computed constructor.
     *
     * @param DataSource $owner
     * @param \Kalnoy\Cruddy\Entity\DataSource\ColumnsFactory $id
     * @param callback $getter
     */
    public function __construct(DataSource $owner, $id, callable $getter)
    {
        parent::__construct($owner, $id);

        $this->getter($getter);
    }

    /**
     * @return callback
     */
    protected function getDefaultGetter()
    {
        return null;
    }

    /**
     * Get whether this column can order data.
     *
     * @return bool
     */
    public function canOrder()
    {
        return false;
    }

    /**
     * Get a list of relationships that needs to be loaded in order to access
     * the value on the model.
     *
     * @return array
     */
    public function relationships()
    {
        return [];
    }
}