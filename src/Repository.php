<?php

namespace Kalnoy\Cruddy;

use Kalnoy\Cruddy\Contracts\Field;
use RuntimeException;
use Illuminate\Container\Container;

/**
 * The entity repository.
 */
class Repository
{
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
     * @var BaseForm[]
     */
    protected $resolved = [ ];

    /**
     * Available entities.
     *
     * @var string
     */
    protected $available;

    /**
     * Init repository.
     *
     * @param \Illuminate\Container\Container $container
     * @param array $classes
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
     * @return Entity
     */
    public function resolve($id)
    {
        if (array_key_exists($id, $this->resolved)) {
            return $this->resolved[$id];
        }

        if ( ! isset($this->classes[$id])) {
            throw new EntityNotFoundException(
                "Failed to resolve: the [{$id}] entity is not defined. Please, check your configuration."
            );
        }

        return $this->resolveEntity($id);
    }

    /**
     * Resolve an entity.
     *
     * @param string $id
     *
     * @return Entity
     */
    protected function resolveEntity($id)
    {
        $class = $this->classes[$id];

        if ( ! $this->classExists($class)) {
            throw new RuntimeException("Failed to resolve: the target class [{$class}] is not bound.");
        }

        /**
         * @var Entity $schema
         */
        $entity = $this->container->make($class);

        $this->resolved[$id] = $entity;

        $entity->setId($id);
        $entity->setEntitiesRepository($this);

        return $entity;
    }

    /**
     * Resolve all entities.
     *
     * @return Entity[]
     */
    public function resolveAll()
    {
        foreach ($this->classes as $key => $value) {
            $this->resolveEntity($key);
        }

        return array_values($this->resolved);
    }

    /**
     * Get whether the entity is resolvable.
     *
     * @param string $id
     *
     * @return bool
     */
    public function resolvable($id)
    {
        if ( ! isset($this->classes[$id])) return false;

        return $this->classExists($this->classes[$id]);
    }

    /**
     * Get whether class exists.
     *
     * @param string $class
     *
     * @return bool
     */
    protected function classExists($class)
    {
        return $this->container->bound($class) || class_exists($class);
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

    /**
     * Get available entity id's imploded with `|`.
     *
     * @return string
     */
    public function available()
    {
        if ($this->available === null) {
            return $this->available = implode('|', array_keys($this->classes));
        }

        return $this->available;
    }

    /**
     * Get a field with specified id.
     *
     * @param $id
     *
     * @return Field
     */
    public function field($id)
    {
        list($entityId, $fieldId) = explode('.', $id, 2);

        $entity = $this->resolve($entityId);
        $field = $entity->getFields()->get($fieldId);

        if ( ! $field) {
            throw new RuntimeException("The field [{$fieldId}] of [{$entityId}] entity is not found.");
        }

        return $field;
    }

}