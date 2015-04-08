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
     * The name of the JavaScript class that is used to render this field.
     *
     * @return string
     */
    protected function modelClass()
    {
        return 'Cruddy.Fields.Computed';
    }

    /**
     * {@inheritdoc}
     */
    public function keep($value)
    {
        return false;
    }

}