<?php namespace Kalnoy\Cruddy;

use Illuminate\Support\Contracts\JsonableInterface;
use Illuminate\Http\Request;
use Illuminate\Config\Repository as Config;
use Kalnoy\Cruddy\Entity\Factory as EntityFactory;

class Environment implements JsonableInterface {

    protected $config;

    protected $entities;

    protected $permissions;

    protected $menu;

    public function __construct(Config $config, EntityFactory $entities, PermissionsInterface $permissions, Menu $menu, Request $request)
    {
        $this->config = $config;
        $this->entities = $entities;
        $this->permissions = $permissions;
        $this->menu = $menu;
        $this->request = $request;
    }

    public function entity($id)
    {
        return $this->entities->resolve($id);
    }

    public function menu()
    {
        return $this->menu->render($this->config("menu"));
    }

    public function config($key, $default = null)
    {
        return $this->config->get("cruddy::{$key}", $default);
    }

    public function toJSON($options = 0)
    {
        return json_encode(array(

            "locale" => $this->config->get("app.locale"),
            "uri" => $this->config("uri"),
            "root" => $this->request->root(),

        ), $options);
    }
}