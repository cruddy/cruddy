<?php

namespace Kalnoy\Cruddy\Schema;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Kalnoy\Cruddy\Entity;

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
     * Whether to hide this attribute.
     *
     * @var bool
     */
    public $hide = false;

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
     * Set hide property.
     *
     * @param bool $value
     *
     * @return $this
     */
    public function hide($value = true)
    {
        $this->hide = $value;

        return $this;
    }

    /**
     * @inheritdoc
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return $this
     */
    public function modifyQuery(EloquentBuilder $builder)
    {
        return $this;
    }

    /**
     * @inheritdoc
     *
     * @param \Illuminate\Database\Query\Builder $builder
     * @param asc|desc                           $direction
     *
     * @return $this
     */
    public function order(QueryBuilder $builder, $direction)
    {
        $builder->orderBy($this->id, $direction);
        
        return $this;
    }

    /**
     * Get attribute id.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get owning entity.
     *
     * @return \Kalnoy\Cruddy\Entity
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get help for an attribute.
     *
     * @return string
     */
    public function getHelp()
    {
        return $this->translate('help');
    }

    /**
     * Translate given key.
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
     * @inheritdoc
     *
     * @return bool
     */
    public function canOrder()
    {
        return $this->canOrder;
    }

    /**
     * @inheritdoc
     *
     * @return array
     */
    public function toArray()
    {
        return
        [
            'class' => $this->class,
            'id' => $this->id,
            'type' => $this->type,
            'hide' => $this->hide,
            'help' => $this->getHelp(),
            'can_order' => $this->canOrder(),
        ];
    }

}