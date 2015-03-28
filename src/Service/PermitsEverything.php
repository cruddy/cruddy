<?php

namespace Kalnoy\Cruddy\Service;

use Kalnoy\Cruddy\Contracts\Permissions;
use Kalnoy\Cruddy\Entity;

/**
 * Stub permissions.
 *
 * This type of permissions will just allow all operations.
 *
 * @since 1.0.0
 */
class PermitsEverything implements Permissions {

    /**
     * {@inheritdoc}
     */
    public function isPermitted($action, Entity $entity)
    {
        return true;
    }

}