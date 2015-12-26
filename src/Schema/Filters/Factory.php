<?php

namespace Kalnoy\Cruddy\Schema\Filters;

use Kalnoy\Cruddy\Contracts\Filter;
use Kalnoy\Cruddy\Schema\BaseFactory;

class Factory extends BaseFactory
{
    /**
     * @param \Kalnoy\Cruddy\Entity $entity
     * @param \Kalnoy\Cruddy\Schema\BaseCollection $collection
     * @param $id
     * @param null $fieldId
     *
     * @return Types\Proxy
     */
    public function usingField($entity, $collection, $id, $fieldId = null)
    {
        $field = $this->resolveField($entity, $fieldId ?: $id);

        $instance = new Types\Proxy($entity, $id, $field);

        $collection->push($instance);

        return $instance;
    }

}