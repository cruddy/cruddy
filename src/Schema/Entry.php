<?php

namespace Kalnoy\Cruddy\Schema;

use Illuminate\Support\Contracts\ArrayableInterface;
use Kalnoy\Cruddy\Entity;
use Kalnoy\Cruddy\Helpers;

class Entry implements \Kalnoy\Cruddy\Contracts\Entry {

    /**
     * The entity.
     *
     * @var Entity
     */
    protected $entity;

    /**
     * The attribute id.
     *
     * @var string
     */
    protected $id;

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * The JavaScript class.
     *
     * @var string
     */
    protected $class;

    /**
     * The attribute type.
     *
     * It's used to distinguish fields by type so it is possible to differentiate
     * styling.
     *
     * @var string
     */
    protected $type;

    /**
     * Init attribute.
     *
     * @param Entity $entity
     * @param string $id
     */
    public function __construct(Entity $entity, $id)
    {
        $this->entity = $entity;
        $this->id = $id;
    }

    /**
     * Set the value of the attribute.
     *
     * @param string $name
     * @param mixed $value
     *
     * @return $this
     */
    public function set($name, $value)
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * @param string $name
     * @param mixed $default
     *
     * @return mixed
     */
    public function get($name, $default = null)
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : $default;
    }

    /**
     * Get an attribute type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get a help string for the attribute.
     *
     * @return string
     */
    public function getHelp()
    {
        $help = $this->get('help');

        return $help ? Helpers::tryTranslate($help) : $this->translate('help');
    }

    /**
     * Translate an attribute id under specified group.
     *
     * @param string $group
     * @param string $default
     *
     * @return string
     */
    protected function translate($group = null, $default = null)
    {
        $key = $this->id;

        if ($group !== null) $key = "{$group}.{$key}";

        return $this->entity->translate($key, $default);
    }

    /**
     * Generate a label from the id.
     *
     * @return string
     */
    protected function generateLabel()
    {
        return Helpers::labelFromId($this->id);
    }

    /**
     * Get an attribute id.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFullyQualifiedId()
    {
        return $this->entity->getId().'.'.$this->id;
    }

    /**
     * Get an owning entity.
     *
     * @return Entity
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'class' => $this->class,
            'id' => $this->id,
            'type' => $this->type,
            'hide' => $this->get('hide', false),
            'help' => $this->getHelp(),
        ];
    }

    /**
     * @param string $name
     * @param array $arguments
     *
     * @return $this
     */
    function __call($name, $arguments)
    {
        return $this->set($name, empty($arguments) ? true : reset($arguments));
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    function __get($name)
    {
        return $this->get($name);
    }

    /**
     * @param string $name
     * @param mixed $value
     *
     * @return $this
     */
    function __set($name, $value)
    {
        return $this->set($name, $value);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    function __isset($name)
    {
        return isset($this->attributes[$name]);
    }

    /**
     * @param string $name
     */
    function __unset($name)
    {
        unset($this->attributes[$name]);
    }

}