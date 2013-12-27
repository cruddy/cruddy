<?php namespace Kalnoy\Cruddy\Fields;

use Kalnoy\Cruddy\AttributeCollection as BaseCollection;
use Illuminate\Database\Eloquent\Model as Eloquent;

class Collection extends BaseCollection {

    /**
     * Process an input data.
     *
     * @param  Eloquent $instance
     * @param  array  $input
     *
     * @return array
     */
    public function process(Eloquent $instance, array $input)
    {
        array_walk($this->items, function ($field) use (&$input, $instance) {

            if (!($field instanceof EditableInterface) || !$field->isEditable($instance))
            {
                return;
            }

            $id = $field->getId();

            if (array_key_exists($id, $input))
            {
                $value = $field->process($input[$id]);

                if ($value === null)
                {
                    unset($input[$id]);
                }
                else
                {
                    $input[$id] = $value;
                }
            }
        });

        return $input;
    }
}