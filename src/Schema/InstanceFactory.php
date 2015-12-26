<?php

namespace Kalnoy\Cruddy\Schema;

use Kalnoy\Cruddy\Contracts\Attribute;
use Kalnoy\Cruddy\Entity;

/**
 * Instance factory connects attribute collection, entity and actual factory.
 *
 * @since 1.0.0
 */
class InstanceFactory {

    /**
     * The factory.
     *
     * @var BaseFactory
     */
    protected $factory;

    /**
     * The collection to where attributes are placed.
     *
     * @var BaseCollection
     */
    protected $collection;

    /**
     * Init instance factory.
     *
     * @param BaseFactory $factory
     * @param BaseCollection $collection
     */
    public function __construct($factory, $collection)
    {
        $this->factory = $factory;
        $this->collection = $collection;
    }

    /**
     * Try to resolve macro.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return Attribute
     */
    public function __call($method, $parameters)
    {
        return $this->factory->resolve($method, $this->collection, $parameters);
    }

}