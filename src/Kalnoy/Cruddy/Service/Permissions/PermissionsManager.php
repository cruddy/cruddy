<?php

namespace Kalnoy\Cruddy\Service\Permissions;

use Illuminate\Support\Manager;

/**
 * Permissions provider.
 */
class PermissionsManager extends Manager {

    /**
     * Create sentry driver.
     *
     * @return \Kalnoy\Cruddy\Service\Permissions\SentryPermissions
     */
    public function createSentryDriver()
    {
        return new SentryPermissions($this->app['sentry']);
    }

    /**
     * Create stub driver.
     *
     * @return \Kalnoy\Cruddy\Service\Permissions\Stub
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