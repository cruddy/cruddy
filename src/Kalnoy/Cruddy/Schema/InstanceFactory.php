<?php

namespace Kalnoy\Cruddy\Schema;

use Kalnoy\Cruddy\Entity;

/**
 * Instance factory connects attribute collection, entity and actual factory.
 */
class InstanceFactory {

    /**
     * The factory.
     *
     * @var \Kalnoy\Cruddy\Schema\BaseFactory
     */
    protected $factory;

    /**
     * The entity.
     *
     * @var \Kalnoy\Cruddy\Entity
     */
    protected $entity;

    /**
     * The collection to where attributes are placed.
     *
     * @var \Kalnoy\Cruddy\Schema\BaseCollection
     */
    protected $collection;

    /**
     * Init instance factory.
     *
     * @param \Kalnoy\Cruddy\Schema\BaseFactory    $factory
     * @param \Kalnoy\Cruddy\Entity                $entity
     * @param \Kalnoy\Cruddy\Schema\BaseCollection $collection
     */
    public function __construct(BaseFactory $factory, Entity $entity, BaseCollection $collection)
    {
        $this->factory = $factory;
        $this->entity = $entity;
        $this->collection = $collection;
    }

    /**
     * Try to resolve macro.
     *
     * @param string $method
     * @param array $parameters
     *
     * @return \Kalnoy\Cruddy\Scheme\AttributeInterface
     */
    public function __call($method, $parameters)
    {
        return $this->factory->resolve($method, $this->entity, $this->collection, $parameters);
    }
}