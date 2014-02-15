<?php

namespace Kalnoy\Cruddy\Service\Permissions;

use Cartalyst\Sentry\Sentry;
use Kalnoy\Cruddy\Entity;

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
     * @inhertidoc
     *
     * @param string $action
     * @param \Kalnoy\Cruddy\Entity $entity
     *
     * @return bool
     */
    public function isPermitted($action, Entity $entity)
    {
        $key = "{$entity->getId()}.{$action}";

        return ($user = $this->sentry->getUser()) && $user->hasAccess($key);
    }
}