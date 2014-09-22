<?php

namespace Kalnoy\Cruddy\Service\Permissions;

use Illuminate\Support\Manager;

/**
 * Permissions provider.
 *
 * @method bool isPermitted(string $action, \Kalnoy\Cruddy\Entity $entity)
 */
class PermissionsManager extends Manager {

    /**
     * Create stub driver.
     *
     * @return Stub
     */
    public function createStubDriver()
    {
        return new Stub;
    }

    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->app['config']['cruddy::permissions'];
    }

}