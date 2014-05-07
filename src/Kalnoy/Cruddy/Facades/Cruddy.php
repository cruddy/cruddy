<?php

namespace Kalnoy\Cruddy\Facades;

use Illuminate\Support\Facades\Facade;
use Kalnoy\Cruddy\Entity;

/**
 * @see \Kalnoy\Cruddy\Environment
 */
class Cruddy extends Facade {

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

    protected static function getFacadeAccessor() { return 'cruddy'; }

}