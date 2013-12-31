<?php namespace Kalnoy\Cruddy\Entity\Attribute;

use Kalnoy\Cruddy\Entity\Entity;
use RuntimeException;

class Factory {

    protected $types = array();

    protected $defaultType;

    protected $generate = array();

    /**
     * Create an attribute.
     *
     * @param  Entity $entity
     * @param  string $type
     * @param  string $id
     * @param  array  $config
     *
     * @throws RuntimeException
     * @return Attribute
     */
    public function create(Entity $entity, $type, $id, array $config = array())
    {
        if (!isset($this->types[$type]))
        {
            throw new RuntimeException("The attribute of type {$type} is not registered.");
        }

        $className = $this->types[$type];

        $instance = new $className($entity, $type, $id);

        foreach ($config as $key => $value)
        {
            $instance->$key = $value;
        }

        return $instance;
    }

    /**
     * Create an attribute from config. Config must contain type key.
     *
     * @param  Entity $entity
     * @param  string $id
     * @param  array  $config
     *
     * @throws RuntimeException
     * @return Attribute
     */
    public function createFromConfig(Entity $entity, $id, array $config)
    {
        if (!isset($config['type']))
        {
            if ($this->defaultType === null)
            {
                throw new RuntimeException("Attribute config must contain type key.");
            }

            $type = $this->defaultType;
        }
        else
        {
            $type = $config['type'];

            unset($config['type']);
        }


        return $this->create($entity, $type, $id, $config);
    }

    /**
     * Create a collection of attributes.
     *
     * @param  Entity $entity
     * @param  array  $items
     *
     * @return Collection
     */
    public function createFromCollection(Entity $entity, array $items)
    {
        $collection = $this->newCollection();

        foreach ($items as $key => $value)
        {
            if (is_numeric($key))
            {
                $key = $value;
                $value = array('type' => $this->defaultType);
            }
            elseif (is_string($value))
            {
                $value = array('type' => $value);
            }

            $value = $this->createFromConfig($entity, $key, $value);

            $collection->put($key, $value);
        }

        // Generate items if any.
        $this->generate($entity, $collection);

        return $collection;
    }

    protected function generate(Entity $entity, Collection $collection)
    {
        foreach ($this->generate as $item)
        {
            $method = 'generate'.ucfirst($item);

            $this->$method($entity, $collection);
        }
    }

    /**
     * Register a new attribute type.
     *
     * @param  string $type
     * @param  string $className
     *
     * @return void
     */
    public function register($type, $className)
    {
        if (isset($this->types[$type]))
        {
            throw new RuntimeException("The attribute type {$type} is already registered.");
        }

        $this->types[$type] = $className;
    }

    /**
     * Create new collection.
     *
     * @param  array  $items
     *
     * @return Collection
     */
    public function newCollection(array $items = array())
    {
        return new Collection($items);
    }
}