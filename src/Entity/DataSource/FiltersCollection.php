<?php

namespace Kalnoy\Cruddy\Entity\DataSource;

use Kalnoy\Cruddy\Service\BaseCollection;

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