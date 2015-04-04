<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Kalnoy\Cruddy\Entity;
use Kalnoy\Cruddy\Schema\ComputedTrait;
use Kalnoy\Cruddy\Schema\Fields\BaseField;

/**
 * Computed field.
 *
 * @since 1.0.0
 */
class Computed extends BaseField {

    use ComputedTrait;

    /**
     * {@inheritdoc}
     */
    protected $class = 'Cruddy.Fields.Computed';

    /**
     * @param Entity $entity
     * @param string $id
     * @param string $accessor
     */
    public function __construct(Entity $entity, $id, $accessor = null)
    {
        parent::__construct($entity, $id);

        $this->accessor = $accessor;
    }

    /**
     * {@inheritdoc}
     */
    public function isDisabled($action)
    {
        return $action != Entity::WHEN_NEW or $this->default !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function keep($value)
    {
        return false;
    }

}