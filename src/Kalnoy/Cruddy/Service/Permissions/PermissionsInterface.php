<?php

namespace Kalnoy\Cruddy\Service\Permissions;

use Kalnoy\Cruddy\Entity;

interface PermissionsInterface {

    const VIEW = 'view';

    const CREATE = 'create';

    const UPDATE = 'update';

    const DELETE = 'delete';

    /**
     * Get whether a user is allowed to perform an action on user.
     *
     * @param string $action
     * @param Kalnoy\Cruddy\Entity $entity
     *
     * @return bool
     */
    public function isPermitted($action, Entity $entity);
}