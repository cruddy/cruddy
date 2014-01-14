<?php namespace Kalnoy\Cruddy;

use Illuminate\Support\Contracts\JsonableInterface;
use Illuminate\Http\Request;
use Illuminate\Config\Repository as Config;
use Kalnoy\Cruddy\Entity\Factory as EntityFactory;
use Kalnoy\Cruddy\Service\Permissions\PermissionsInterface;

class Environment implements JsonableInterface {

    /**
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * @var Entity\Factory
     */
    protected $entities;

    /**
     * @var PermissionsInterface
     */
    protected $permissions;

    /**
     * @var Menu
     */
    protected $menu;

    /**
     * @param Config               $config
     * @param EntityFactory        $entities
     * @param PermissionsInterface $permissions
     * @param Menu                 $menu
     * @param Request              $request
     */
    public function __construct(Config $config, EntityFactory $entities, PermissionsInterface $permissions, Menu $menu, Request $request)
    {
        $this->config = $config;
        $this->entities = $entities;
        $this->permissions = $permissions;
        $this->menu = $menu;
        $this->request = $request;
    }

    /**
     * Resolve an entity.
     *
     * @param $id
     *
     * @return Entity\Entity
     */
    public function entity($id)
    {
        return $this->entities->resolve($id);
    }

    /**
     * Render a menu.
     *
     * @return string
     */
    public function menu()
    {
        return $this->menu->render($this->config("menu"));
    }

    /**
     * Get configuration option from cruddy configuration file.
     *
     * @param      $key
     * @param null $default
     *
     * @return mixed
     */
    public function config($key, $default = null)
    {
        return $this->config->get("cruddy::{$key}", $default);
    }

    /**
     * @param int $options
     *
     * @return string
     */
    public function toJSON($options = 0)
    {
        return json_encode(array(

            "locale" => $this->config->get("app.locale"),
            "uri" => $this->config("uri"),
            "root" => $this->request->root(),

        ), $options);
    }
}