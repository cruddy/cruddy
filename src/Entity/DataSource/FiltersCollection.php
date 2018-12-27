<?php

namespace Kalnoy\Cruddy\Entity\DataSource;

use Kalnoy\Cruddy\Entity\DataSource\Filters\BaseFilter;
use Kalnoy\Cruddy\Service\BaseCollection;

/**
 * Class FiltersCollection
 *
 * @method Filters\Enum enum(string $id, $items)
 * @method Filters\Boolean bool(string $id)
 * @method Filters\Boolean boolean(string $id)
 * @method Filters\Entity entity(string $id, string $refEntityId = null)
 * @method Filters\Input text(string $id)
 *
 * @package Kalnoy\Cruddy\Entity\DataSource
 */
class FiltersCollection extends BaseCollection
{
    /**
     * @var DataSource
     */
    protected $owner;

    /**
     * @var FiltersFactory
     */
    protected $factory;

    /**
     * @var array|BaseFilter[]
     */
    protected $items = [];

    /**
     * FiltersCollection constructor.
     *
     * @param DataSource $owner
     * @param FiltersFactory $factory
     */
    public function __construct(DataSource $owner, FiltersFactory $factory)
    {
        parent::__construct($owner, $factory);
    }

    /**
     * @param $query
     * @param array $input
     *
     * @return mixed
     */
    public function apply($query, array $input)
    {
        foreach ($this->items as $filter) {
            $key = $filter->getDataKey();

            if (isset($input[$key])) {
                $filter->apply($query, $input[$key]);
            }
        }

        return $query;
    }

    /**
     * @return FiltersFactory
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * @return DataSource
     */
    public function getOwner()
    {
        return $this->owner;
    }
}