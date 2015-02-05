<?php

namespace Kalnoy\Cruddy\Schema\Columns;

use Kalnoy\Cruddy\Schema\BaseFactory;

/**
 * The column factory.
 *
 * @since 1.0.0
 */
class Factory extends BaseFactory {

    /**
     * @var array
     */
    protected $macros =
    [
        'states' => 'Kalnoy\Cruddy\Schema\Columns\Types\States',
    ];

    /**
     * Create computed column.
     *
     * @param \Kalnoy\Cruddy\Entity $entity
     * @param Collection            $collection
     * @param int                   $id
     * @param \Closure              $value
     *
     * @return Types\Computed
     */
    public function compute($entity, $collection, $id, \Closure $value)
    {
        $instance = new Types\Computed($entity, $id, $value);

        $collection->add($instance);

        return $instance;
    }

    /**
     * Create new proxy column.
     *
     * @param \Kalnoy\Cruddy\Entity $entity
     * @param Collection $collection
     * @param string $id
     * @param string $fieldId
     *
     * @return Types\Proxy
     */
    public function col($entity, $collection, $id, $fieldId = null)
    {
        $field = $this->resolveField($entity, $fieldId ?: $id);

        $instance = new Types\Proxy($entity, $id, $field);

        $collection->add($instance);

        return $instance;
    }
}