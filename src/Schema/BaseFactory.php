<?php

namespace Kalnoy\Cruddy\Schema;

use Kalnoy\Cruddy\BaseForm;
use Kalnoy\Cruddy\Contracts\Attribute;
use Kalnoy\Cruddy\Entity;

/**
 * Base factory for all kinds of attributes.
 *
 * @since 1.0.0
 */
class BaseFactory
{
    /**
     * @var array
     */
    protected $macros = [ ];

    /**
     * Register new type.
     *
     * @param string $type
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
     * @param string $macro
     * @param BaseCollection $collection
     * @param array $params
     *
     * @return Attribute
     */
    public function resolve($macro, $collection, array $params)
    {
        if (method_exists($this, $macro)) {
            return $this->evaluate([ $this, $macro ], $collection, $params);
        }

        if ( ! isset($this->macros[$macro])) {
            throw new \RuntimeException("Macro [{$macro}] is not registered.");
        }

        $callback = $this->macros[$macro];

        if ( ! is_string($callback)) {
            return $this->evaluate($callback, $collection, $params);
        }

        $class = new \ReflectionClass($callback);

        array_unshift($params, $collection->getContainer());

        $instance = $class->newInstanceArgs($params);

        $collection->push($instance);

        return $instance;
    }

    /**
     * Evaluate callback.
     *
     * @param mixed $callback
     * @param BaseCollection $collection
     * @param array $params
     *
     * @return Entry
     */
    protected function evaluate($callback, $collection, array $params)
    {
        array_unshift($params, $collection->getContainer(), $collection);

        return call_user_func_array($callback, $params);
    }

    /**
     * @param BaseForm $container
     * @param $id
     *
     * @return \Kalnoy\Cruddy\Contracts\Field
     */
    protected function resolveField($container, $id)
    {
        $field = $container->getFields()->get($id);

        if ($field === null) {
            throw new \RuntimeException("The field [{$container->getId()}.{$id}] is not found.");
        }

        return $field;
    }

}