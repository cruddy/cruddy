<?php

namespace Kalnoy\Cruddy\Contracts;

use Kalnoy\Cruddy\BaseForm;

/**
 * Permissions interface.
 */
interface Permissions {

    /**
     * Get whether a user is allowed to perform an action on entity.
     *
     * @param string $action
     * @param BaseForm $entity
     *
     * @return bool
     */
    public function isPermitted($action, BaseForm $entity);
}