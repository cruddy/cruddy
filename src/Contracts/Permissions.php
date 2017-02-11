<?php

namespace Kalnoy\Cruddy\Contracts;

use Kalnoy\Cruddy\Entity\Entity;

/**
 * Permissions interface.
 */
interface Permissions {

    /**
     * Get whether a user is allowed to perform an action on entity.
     *
     * @param string $action
     * @param Entity $entity
     *
     * @return bool
     */
    public function isPermitted($action, Entity $entity);
}