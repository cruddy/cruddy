<?php namespace Kalnoy\Cruddy\Entity\Fields;

use Kalnoy\Cruddy\Entity\Attribute\Factory as AttributeFactory;
use Kalnoy\Cruddy\Entity\Entity;

class Factory extends AttributeFactory {

    protected $types = array(
        'primary' => 'Kalnoy\Cruddy\Entity\Fields\Types\Primary',
        'string' => 'Kalnoy\Cruddy\Entity\Fields\Types\Text',
        'text' => 'Kalnoy\Cruddy\Entity\Fields\Types\TextArea',
        'email' => 'Kalnoy\Cruddy\Entity\Fields\Types\Email',
        'password' => 'Kalnoy\Cruddy\Entity\Fields\Types\Password',
        'datetime' => 'Kalnoy\Cruddy\Entity\Fields\Types\DateTime',
        'time' => 'Kalnoy\Cruddy\Entity\Fields\Types\Time',
        'date' => 'Kalnoy\Cruddy\Entity\Fields\Types\Date',
        'bool' => 'Kalnoy\Cruddy\Entity\Fields\Types\Boolean',
        'relation' => 'Kalnoy\Cruddy\Entity\Fields\Types\Relation',
    );

    protected $defaultType = 'string';

    protected $generate = array('primary', 'timestamps');

    protected function generatePrimary(Entity $entity, Collection $collection)
    {
        $instance = $entity->form()->instance();
        $key = $instance->getKeyName();

        if (!$collection->has($key))
        {
            $field = $this->create($entity, 'primary', $key);
            $collection->put($key, $field);
        }
    }

    protected function generateTimestamps(Entity $entity, Collection $collection)
    {
        $instance = $entity->form()->instance();

        if ($instance->timestamps)
        {
            $columns = array(
                $instance->getCreatedAtColumn(),
                $instance->getUpdatedAtColumn(),
            );

            foreach ($columns as $id)
            {
                if ($collection->has($id)) continue;

                $item = $this->create($entity, 'datetime', $id);

                $collection->put($id, $item);
            }
        }
    }

    /**
     * @inheritdoc
     *
     * @param  array  $items
     *
     * @return Collection
     */
    public function newCollection(array $items = array())
    {
        return new Collection($items);
    }
}