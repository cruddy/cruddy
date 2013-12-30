<?php namespace Kalnoy\Cruddy\Entity\Fields;

use Kalnoy\Cruddy\Entity\Attribute\Attribute;
use Illuminate\Database\Eloquent\Model as Eloquent;

abstract class AbstractField extends Attribute implements EditableInterface {

    /**
     * Whether the field can be updated.
     *
     * @var bool
     */
    public $updatable = true;

    /**
     * Get the value of model's respective attribute.
     *
     * @return mixed
     */
    public function value(Eloquent $model)
    {
        return $model->getAttribute($this->id);
    }

    /**
     * Process the input value before sending it to the repository.
     *
     * @param mixed $value
     */
    public function process($value)
    {
        return $value;
    }

    /**
     * Get runtime configuration that depends on a model.
     *
     * @param  Eloquent $model
     *
     * @return array
     */
    public function runtime(Eloquent $model)
    {
        return parent::runtime($model) + array('editable' => $this->isEditable($model));
    }

    /**
     * Get whether the field is actually editable.
     *
     * @param  Eloquent $model
     *
     * @return bool
     */
    public function isEditable(Eloquent $model)
    {
        return $model->isFillable($this->id);
    }

    public function getLabel()
    {
        $translator = $this->entity->getTranslator();

        $key = "validation.attributes.{$this->id}";

        if (($label = $translator->trans($key)) !== $key) return $label;

        return humanize($this->id);
    }

    /**
     * Get the field configuration as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return parent::toArray() + array(
            'updatable' => $this->updatable,
            'label' => $this->getLabel(),
        );
    }
}