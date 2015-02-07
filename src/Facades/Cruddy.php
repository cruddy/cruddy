<?php

namespace Kalnoy\Cruddy\Facades;

use Illuminate\Support\Facades\Facade;
use Kalnoy\Cruddy\Entity;

/**
 * @see \Kalnoy\Cruddy\Environment
 */
class Cruddy extends Facade {

    /**
     * Register new field type.
     *
     * @param string          $macro
     * @param string|Callable $callback
     */
    public static function registerField($macro, $callback)
    {
        app('cruddy.fields')->register($macro, $callback);
    }

    /**
     * Register new column type.
     *
     * @param string          $macro
     * @param string|Callable $callback
     */
    public static function registerColumn($macro, $callback)
    {
        app('cruddy.fields')->register($macro, $callback);
    }

    /**
     * Register a css file.
     *
     * @param string $uri
     *
     * @return void
     */
    public static function css($uri)
    {
        app('cruddy.assets')->css($uri);
    }

    /**
     * Register a js file.
     *
     * @param string $uri
     *
     * @return void
     */
    public static function js($uri)
    {
        app('cruddy.assets')->js($uri);
    }

    /**
     * Extend permissions manager.
     *
     * @param string $driver
     * @param \Closure $callback
     *
     * @return void
     */
    public static function extendPermissions($driver, $callback)
    {
        app('cruddy.permissions')->extend($driver, $callback);
    }

    /**
     * Register UI lang items.
     *
     * @param array $items
     *
     * @return void
     */
    public static function lang(array $items)
    {
        app('cruddy.lang')->lang($items);
    }

    /**
     * Register saving event handler.
     *
     * @param string $id
     * @param mixed $callback
     *
     * @return void
     */
    public static function saving($id, $callback)
    {
        Entity::saving($id, $callback);
    }

    /**
     * Register saved event handler.
     *
     * @param string $id
     * @param mixed $callback
     *
     * @return void
     */
    public static function saved($id, $callback)
    {
        Entity::saved($id, $callback);
    }

    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor() { return 'cruddy'; }

}