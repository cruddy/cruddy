<?php

namespace Kalnoy\Cruddy\Schema;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Kalnoy\Cruddy\Entity;

/**
 * Base attribute class.
 *
 * @property string $help
 * @property string $hide
 * @method $this help(string $value)
 * @method $this hide(bool $value = true)
 *
 * @since 1.0.0
 */
abstract class Attribute implements AttributeInterface {

    /**
     * The entity.
     *
     * @var \Kalnoy\Cruddy\Entity
     */
    protected $entity;

    /**
     * The JavaScript class.
     *
     * @var string
     */
    protected $class;

    /**
     * The attribute id.
     *
     * @var string
     */
    protected $id;

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
     * Whether this field can order data.
     *
     * @var bool
     */
    protected $canOrder = false;

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * Init attribute.
     *
     * @param \Kalnoy\Cruddy\Entity $entity
     * @param string                $id
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
     * {@inheritdoc}
     */
    public function modifyQuery(EloquentBuilder $builder)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function order(QueryBuilder $builder, $direction)
    {
        $builder->orderBy($this->id, $direction);

        return $this;
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
     * Get an owning entity.
     *
     * @return \Kalnoy\Cruddy\Entity
     */
    public function getEntity()
    {
        return $this->entity;
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

        return $help ? \Kalnoy\Cruddy\try_trans($help) : $this->translate('help');
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
        return \Kalnoy\Cruddy\ucfirst(\Kalnoy\Cruddy\prettify_string($this->id));
    }

    /**
     * {@inheritdoc}
     */
    public function canOrder()
    {
        return $this->canOrder;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return
        [
            'class' => $this->class,
            'id' => $this->id,
            'type' => $this->type,
            'hide' => $this->get('hide', false),
            'help' => $this->getHelp(),
            'can_order' => $this->canOrder(),
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