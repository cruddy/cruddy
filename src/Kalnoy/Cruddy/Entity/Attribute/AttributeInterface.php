<?php namespace Kalnoy\Cruddy\Entity\Attribute;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Kalnoy\Cruddy\Entity\Entity;
use Kalnoy\Cruddy\ComponentInterface;

interface AttributeInterface extends ComponentInterface {

    /**
     * Get the value of a model's respective attribute.
     *
     * @param Eloquent $model
     *
     * @return mixed
     */
    function value(Eloquent $model);

    /**
     * Modify a query builder before querying a model collection.
     *
     * @param  Builder $query
     *
     * @return void
     */
    function modifyQuery(Builder $query);

    /**
     * Get the entity that owns an attribute.
     *
     * @return Entity
     */
    function getEntity();

    /**
     * Get a JavaScript class name that will serve the attribute.
     *
     * @return string
     */
    function getJavaScriptClass();
}