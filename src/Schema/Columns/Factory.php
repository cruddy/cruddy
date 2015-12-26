<?php

namespace Kalnoy\Cruddy\Schema\Columns;

use Kalnoy\Cruddy\Entity;
use Kalnoy\Cruddy\Schema\BaseFactory;

/**
 * The column factory.
 *
 * @since 1.0.0
 */
class Factory extends BaseFactory
{
    /**
     * @var array
     */
    protected $macros = [
        'states' => Types\States::class,
        'compute' => Types\Computed::class,
    ];

    /**
     * Create new proxy column.
     *
     * @param Entity $entity
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

        $collection->push($instance);

        return $instance;
    }

    /**
     * @param $entity
     * @param $collection
     * @param array $items
     */
    public function cols($entity, $collection, array $items)
    {
        foreach ($items as $id => $fieldId) {
            if (is_numeric($id)) {
                $id = $fieldId;
                $fieldId = null;
            }

            $this->col($entity, $collection, $id, $fieldId);
        }
    }

}