<?php

namespace Kalnoy\Cruddy\Service\Permissions;

use Kalnoy\Cruddy\Entity;

/**
 * This type of permissions will just allow all operations. 
 */
class Stub implements PermissionsInterface {

    /**
     * @inheritdoc
     *
     * @param string $action
     * @param \Kalnoy\Cruddy\Entity $entity
     *
     * @return bool
     */
    public function isPermitted($action, Entity $entity)
    {
        return true;
    }

}