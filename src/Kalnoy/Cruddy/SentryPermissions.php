<?php namespace Kalnoy\Cruddy;

use Cartalyst\Sentry\Sentry;

class SentryPermissions implements PermissionsInterface {

    protected $sentry;

    public function __construct(Sentry $sentry)
    {
        $this->sentry = $sentry;
    }

    public function hasAccess($to = "backend")
    {
        $user = $this->sentry->getUser();

        return $user && $user->hasAccess($to);
    }

    protected function check(Entity $entity, $action)
    {
        $key = "{$entity->getId()}.{$action}";

        return $this->sentry->check() && $this->sentry->getUser()->hasAccess($key);
    }

    public function canView(Entity $entity)
    {
        return $this->check($entity, "view");
    }

    public function canCreate(Entity $entity)
    {
        return $this->check($entity, "create");
    }

    public function canUpdate(Entity $entity)
    {
        return $this->check($entity, "update");
    }

    public function canDelete(Entity $entity)
    {
        return $this->check($entity, "delete");
    }
}