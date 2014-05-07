<?php

namespace Kalnoy\Cruddy\Schema\Fields;

use Illuminate\Database\Eloquent\Relations\Relation;
use Kalnoy\Cruddy\Schema\BaseFactory;
use Kalnoy\Cruddy\Entity;

/**
 * Field Factory class.
 */
class Factory extends BaseFactory {

    protected $macros =
    [
        'increments' => 'Kalnoy\Cruddy\Schema\Fields\Types\Primary',
        'string' => 'Kalnoy\Cruddy\Schema\Fields\Types\String',
        'text' => 'Kalnoy\Cruddy\Schema\Fields\Types\Text',
        'email' => 'Kalnoy\Cruddy\Schema\Fields\Types\Email',
        'password' => 'Kalnoy\Cruddy\Schema\Fields\Types\Password',
        'datetime' => 'Kalnoy\Cruddy\Schema\Fields\Types\DateTime',
        'time' => 'Kalnoy\Cruddy\Schema\Fields\Types\Time',
        'date' => 'Kalnoy\Cruddy\Schema\Fields\Types\Date',
        'boolean' => 'Kalnoy\Cruddy\Schema\Fields\Types\Boolean',
        'bool' => 'Kalnoy\Cruddy\Schema\Fields\Types\Boolean',
        'file' => 'Kalnoy\Cruddy\Schema\Fields\Types\File',
        'image' => 'Kalnoy\Cruddy\Schema\Fields\Types\Image',
        'markdown' => 'Kalnoy\Cruddy\Schema\Fields\Types\Markdown',
        'code' => 'Kalnoy\Cruddy\Schema\Fields\Types\Code',
        'integer' => 'Kalnoy\Cruddy\Schema\Fields\Types\Integer',
        'float' => 'Kalnoy\Cruddy\Schema\Fields\Types\Float',
    ];

    /**
     * Generate timestamp columns.
     *
     * @param \Kalnoy\Cruddy\Entity                $entity
     * @param \Kalnoy\Cruddy\Schema\BaseCollection $collection
     *
     * @return void
     */
    public function timestamps($entity, $collection)
    {
        $this->resolve('datetime', $entity, $collection, ['created_at'])->unique();
        $this->resolve('datetime', $entity, $collection, ['updated_at'])->unique();
    }

    /**
     * Add relation field type.
     *
     * @param \Kalnoy\Cruddy\Entity                $entity
     * @param \Kalnoy\Cruddy\Schema\BaseCollection $collection
     * @param string                               $id
     * @param string                               $ref
     * @param bool                                 $inline
     *
     * @return \Kalnoy\Cruddy\Schema\Fields\Types\Relation
     */
    public function relates($entity, $collection, $id, $ref = null, $inline = false)
    {
        if ($ref === null) $ref = \str_plural($id);

        $ref = Entity::getEnvironment()->entity($ref);
        $model = $entity->getRepository()->newModel();

        if ( ! method_exists($model, $id))
        {
            throw new \RuntimeException("The target model {get_class($model)} doesn't have relation {$id} defined.");
        }

        $relation = $model->$id();

        if ( ! $relation instanceof Relation)
        {
            throw new \RuntimeException("The method {$id} of model {get_class($model)} did not return valid relation.");
        }

        $relationClassName = \class_basename($relation);
        $className = 'Kalnoy\Cruddy\Schema\Fields\Types\\' . $relationClassName;

        if ($inline) $className .= 'Inline';

        if ( ! class_exists($className))
        {
            throw new \RuntimeException("Cruddy does not know how to handle [{$relationClassName}] relation.");
        }

        $instance = new $className($entity, $id, $ref, $relation);

        $collection->add($instance);

        if ($inline) $entity->relates($instance);

        return $instance;
    }

    /**
     * Create inline relation field.
     *
     * @param \Kalnoy\Cruddy\Entity                $entity
     * @param \Kalnoy\Cruddy\Schema\BaseCollection $collection
     * @param string                               $id
     * @param string                               $ref
     *
     * @return \Kalnoy\Cruddy\Schema\Fields\Types\Relation
     */
    public function embed($entity, $collection, $id, $ref = null)
    {
        return $this->relates($entity, $collection, $id, $ref, true);
    }

    /**
     * Create slug field.
     *
     * @param \Kalnoy\Cruddy\Entity                $entity
     * @param \Kalnoy\Cruddy\Schema\BaseCollection $collection
     * @param string                               $id
     * @param array|string                         $ref
     *
     * @return \Kalnoy\Cruddy\Fields\Types\Slug
     */
    public function slug($entity, $collection, $id, $ref = null)
    {
        $instance = new Types\Slug($entity, $id);

        $instance->ref = $ref;

        $collection->add($instance);

        return $instance;
    }

    /**
     * Create enum field.
     *
     * @param \Kalnoy\Cruddy\Entity                $entity
     * @param \Kalnoy\Cruddy\Schema\BaseCollection $collection
     * @param string                               $id
     * @param array|\Closure                       $items
     *
     * @return \Kalnoy\Cruddy\Fields\Types\Enum
     */
    public function enum($entity, $collection, $id, $items)
    {
        $instance = new Types\Enum($entity, $id);

        $instance->items = $items;

        $collection->add($instance);

        return $instance;
    }

    /**
     * Create computed field.
     *
     * @param \Kalnoy\Cruddy\Entity                $entity
     * @param \Kalnoy\Cruddy\Schema\BaseCollection $collection
     * @param string                               $id
     * @param string|\Closure                     $accessor
     *
     * @return \Kalnoy\Cruddy\Fields\Types\Computed
     */
    public function computed($entity, $collection, $id, $accessor = null)
    {
        $instance = new Types\Computed($entity, $id);

        if ($accessor === null)
        {
            $accessor = 'get'.\camel_case($id);
        }

        $instance->accessor = $accessor;

        $collection->add($instance);

        return $instance;
    }
}