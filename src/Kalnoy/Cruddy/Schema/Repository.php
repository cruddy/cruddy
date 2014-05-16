<?php

namespace Kalnoy\Cruddy\Schema;

use Illuminate\Container\Container;

use Kalnoy\Cruddy\EntityNotFoundException;

/**
 * The schemas repository.
 */
class Repository {

    /**
     * The container.
     *
     * @var \Illuminate\Container\Container
     */
    protected $container;

    /**
     * The list of registered schemas.
     *
     * @var array
     */
    protected $classes;

    /**
     * The list of resolved entities.
     *
     * @var \Kalnoy\Cruddy\Schema\SchemaInterface[]
     */
    protected $resolved = [];

    /**
     * Init repository.
     *
     * @param \Illuminate\Container\Container $container
     * @param array                           $classes
     */
    public function __construct(Container $container, array $classes)
    {
        $this->container = $container;
        $this->classes = $classes;
    }

    /**
     * Resolve schema.
     *
     * @param string $id
     *
     * @return \Kalnoy\Cruddy\Schema\SchemaInterface
     */
    public function resolve($id)
    {
        if (array_key_exists($id, $this->resolved)) return $this->resolved[$id];

        if ( ! $this->resolvable($id))
        {
            throw new EntityNotFoundException("The entity [{$id}] is not registered.");
        }

        return $this->resolved[$id] = $this->container->make($this->classes[$id]);
    }

    /**
     * Get whether the schema is resolvable.
     *
     * @param string $id
     *
     * @return bool
     */
    public function resolvable($id)
    {
        if ( ! isset($this->classes[$id])) return false;

        $class = $this->classes[$id];

        return $this->container->bound($class) or class_exists($class);
    }

    /**
     * Get registered classes.
     *
     * @return array
     */
    public function getClasses()
    {
        return $this->classes;
    }

}