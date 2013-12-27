<?php namespace Kalnoy\Cruddy;

interface FactoryInterface {

    /**
     * Get an entity of specific type.
     *
     * @param  string $id
     *
     * @return Entity
     */
    function resolve($id);

    /**
     * Create a form for an entity.
     *
     * @param  string $id
     *
     * @return FormInterface
     */
    function createForm($id);

    /**
     * Create a collection of fields for an entity.
     *
     * @param  Entity $entity
     *
     * @return Fields\Collection
     */
    function createFields(Entity $entity);

    function createColumns(Entity $entity);

    function createRelated(Entity $entity);
}