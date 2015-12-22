<?php

namespace Kalnoy\Cruddy;

use Illuminate\Config\Repository as Config;
use Kalnoy\Cruddy\Contracts\Permissions;
use RuntimeException;

/**
 * Cruddy environment.
 *
 * @since 1.0.0
 */
class Environment
{
    /**
     * The entities repository.
     *
     * @var Repository
     */
    protected $entities;

    /**
     * @var Permissions
     */
    protected $permissions;

    /**
     * @var Lang
     */
    protected $lang;

    /**
     * @param Config $config
     * @param Repository $entities
     * @param Permissions $permissions
     * @param Lang $lang
     */
    public function __construct(Repository $entities, Permissions $permissions,
                                Lang $lang
    ) {
        $this->entities = $entities;
        $this->permissions = $permissions;
        $this->lang = $lang;
    }

    /**
     * Resolve an entity.
     *
     * @param $id
     *
     * @return Entity
     */
    public function entity($id)
    {
        return $this->entities->resolve($id);
    }

    /**
     * Find a field with given id.
     *
     * The full field id consists of two parts: the entity id and the field id.
     * I.e. `users.password`.
     *
     * @param string $id
     *
     * @throws RuntimeException
     *
     * @return Schema\Fields\BaseField
     */
    public function field($id)
    {
        return $this->entities->field($id);
    }

    /**
     * Get whether the action for an entity is permitted.
     *
     * @param string $action
     * @param Entity $entity
     *
     * @return bool
     */
    public function isPermitted($action, Entity $entity)
    {
        return $this->permissions->isPermitted($action, $entity);
    }

    /**
     * Permissions object.
     *
     * @return Permissions
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * Get entity repository.
     *
     * @return Repository
     */
    public function getEntities()
    {
        return $this->entities;
    }

    /**
     * Get permissions for every entity.
     *
     * @return array
     */
    public function permissions()
    {
        $data = [ ];

        foreach ($this->entities->resolveAll() as $entity) {
            $data[$entity->getId()] = $entity->getPermissions();
        }

        return $data;
    }

    /**
     * @return array
     */
    public function data()
    {
        return [
            'locale' => config('app.locale', 'en'),
            'brandName' => $this->lang->tryTranslate(config('cruddy.brand', 'Cruddy')),
            'uri' => config('cruddy.uri', 'backend'),
            'entities' => $this->entities->available(),
            'lang' => $this->lang->ui(),
            'permissions' => $this->permissions(),
        ];
    }

}