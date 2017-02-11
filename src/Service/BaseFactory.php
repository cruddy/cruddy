<?php

namespace Kalnoy\Cruddy\Service;

use Illuminate\Support\Str;

class BaseFactory
{
    /**
     * @var string
     */
    protected $parentFactory;

    /**
     * @var array
     */
    protected $types = [];

    /**
     * @param $type
     * @param array $args
     *
     * @return object
     */
    public function resolve($type, array $args)
    {
        if ( ! isset($this->types[$type])) {
            if ( ! $this->parentFactory) {
                throw new \InvalidArgumentException("The type [{$type}] is not registered.");
            }
            
            return app($this->parentFactory)->resolve($type, $args);
        }

        $className = $this->types[$type];

        if (is_callable($className)) {
            return call_user_func_array($className, $args);
        }

        $class = new \ReflectionClass($className);

        return $class->newInstanceArgs($args);
    }

    /**
     * @param string $type
     * @param string $className
     *
     * @return $this
     */
    public function register($type, $className)
    {
        if (isset($this->types[$type])) {
            throw new \InvalidArgumentException("The type [{$type}] already registered.");
        }
        
        if ( ! is_callable($className) && ! class_exists($className)) {
            throw new \InvalidArgumentException("Class [{$className}] not defined.");
        }
        
        $this->types[$type] = $className;
        
        return $this;
    }
}