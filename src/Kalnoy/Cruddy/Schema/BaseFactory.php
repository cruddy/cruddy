<?php

namespace Kalnoy\Cruddy\Schema;

use Kalnoy\Cruddy\Contracts\Attribute;

/**
 * Base factory for all kinds of attributes.
 *
 * @since 1.0.0
 */
class BaseFactory {

    protected $macros = [];

    /**
     * Register new type.
     *
     * @param string          $type
     * @param string|Callable $callback
     *
     * @return $this
     */
    public function register($type, $callback)
    {
        $this->macros[$type] = $callback;

        return $this;
    }

    /**
     * Resolve attribute instance.
     *
     * @param string                $macro
     * @param \Kalnoy\Cruddy\Entity $entity
     * @param BaseCollection        $collection
     * @param array                 $params
     *
     * @return Attribute
     */
    public function resolve($macro, $entity, $collection, array $params)
    {
        if (method_exists($this, $macro))
        {
            return $this->evaluate([ $this, $macro ], $entity, $collection, $params);
        }

        if ( ! isset($this->macros[$macro]))
        {
            throw new \RuntimeException("Attribute of type {$macro} is not registered.");
        }

        $callback = $this->macros[$macro];

        if (is_string($callback))
        {
            $instance = new $callback($entity, reset($params));

            $collection->add($instance);

            return $instance;
        }

        return $this->evaluate($callback, $entity, $collection, $params);
    }

    /**
     * Evaluate callback.
     *
     * @param mixed                 $callback
     * @param \Kalnoy\Cruddy\Entity $entity
     * @param BaseCollection        $collection
     * @param array                 $params
     *
     * @return Attribute
     */
    protected function evaluate($callback, $entity, $collection, array $params)
    {
        array_unshift($params, $entity, $collection);

        return call_user_func_array($callback, $params);
    }

    /**
     * @param \Kalnoy\Cruddy\Entity $entity
     * @param $id
     *
     * @return \Kalnoy\Cruddy\Contracts\Field
     */
    protected function resolveField($entity, $id)
    {
        $field = $entity->getFields()->get($id);

        if ($field === null)
        {
            throw new \RuntimeException("The field with an id of {$entity->getId()}.{$id} is not found.");
        }

        return $field;
    }
}