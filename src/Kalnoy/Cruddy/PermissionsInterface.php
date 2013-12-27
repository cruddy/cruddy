<?php namespace Kalnoy\Cruddy;

interface PermissionsInterface {

    /**
     * Get whether user has access to the backend.
     *
     * @return bool
     */
    function hasAccess($to = "backend");

    /**
     * Get whether an entity can be viewed.
     *
     * @param  Entity $entity
     *
     * @return bool
     */
    function canView(Entity $entity);

    /**
     * Get whether a new instance of an entity can be created.
     *
     * @param  Entity $entity
     *
     * @return bool
     */
    function canCreate(Entity $entity);

    /**
     * Get whether an entity instance can be updated.
     *
     * @param  Entity $entity
     *
     * @return bool
     */
    function canUpdate(Entity $entity);

    /**
     * Get whether an entity instance can be deleted.
     *
     * @param  Entity $entity
     *
     * @return bool
     */
    function canDelete(Entity $entity);
}