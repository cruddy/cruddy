<?php namespace Kalnoy\Cruddy;

use Illuminate\Support\Contracts\ArrayableInterface;
use Illuminate\Support\Contracts\JsonableInterface;
use Illuminate\Database\Eloquent\Model as Eloquent;

interface ComponentInterface extends ArrayableInterface, JsonableInterface {

    /**
     * Get data that depends on actual eloquent model.
     *
     * @param  Eloquent $model
     *
     * @return array
     */
    function runtime(Eloquent $model);

    /**
     * Get a component id.
     *
     * @return string
     */
    function getId();
}