<?php

namespace Kalnoy\Cruddy\Schema;

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
     * @param string                                $macro
     * @param \Kalnoy\Cruddy\Entity                 $entity
     * @param \Kalnoy\Cruddy\Schema\BaseCollection  $collection
     * @param array                                 $params
     *
     * @return \Kalnoy\Cruddy\Scheme\AttributeInterface
     */
    public function resolve($macro, $entity, $collection, array $params)
    {
        if (method_exists($this, $macro))
        {
            return $this->evaluate([ $this, $macro ], $entity, $collection, $params);
        }

        if (!isset($this->macros[$macro]))
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
     * @param mixed                                 $callback
     * @param \Kalnoy\Cruddy\Entity                 $entity
     * @param \Kalnoy\Cruddy\Schema\BaseCollection  $collection
     * @param array                                 $params
     *
     * @return \Kalnoy\Cruddy\Schema\AttributeInterface
     */
    protected function evaluate($callback, $entity, $collection, array $params)
    {
        array_unshift($params, $entity, $collection);

        return call_user_func_array($callback, $params);
    }
}