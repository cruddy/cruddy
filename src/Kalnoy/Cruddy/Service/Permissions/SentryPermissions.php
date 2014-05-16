<?php

namespace Kalnoy\Cruddy\Service\Permissions;

use Cartalyst\Sentry\Sentry;
use Kalnoy\Cruddy\Entity;

/**
 * Permissions for handling sentry users.
 * 
 * The user is checked to have `entityId.action` permission, i.e. `users.update`.
 * 
 * @since 1.0.0
 */
class SentryPermissions implements PermissionsInterface {

    /**
     * The sentry instance.
     *
     * @var \Cartalyst\Sentry\Sentry
     */
    protected $sentry;

    /**
     * Init permissions.
     *
     * @param Cartalyst\Sentry\Sentry $sentry
     */
    public function __construct(Sentry $sentry)
    {
        $this->sentry = $sentry;
    }

    /**
     * {@inhertidoc}
     */
    public function isPermitted($action, Entity $entity)
    {
        $key = "{$entity->getId()}.{$action}";

        return ($user = $this->sentry->getUser()) && $user->hasAccess($key);
    }
}