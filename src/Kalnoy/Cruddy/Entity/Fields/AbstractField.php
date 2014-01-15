<?php namespace Kalnoy\Cruddy\Entity\Fields;

use Kalnoy\Cruddy\Entity\Attribute\Attribute;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Kalnoy\Cruddy;

abstract class AbstractField extends Attribute implements EditableInterface {

    /**
     * Whether the field can be updated.
     *
     * @var bool
     */
    public $updateable = true;

    /**
     * Whether the value is required.
     *
     * @var bool
     */
    public $required = false;

    /**
     * Whether the value of this field can be transferred to a copy.
     *
     * @var bool
     */
    public $copyable = true;

    /**
     * @inheritdoc
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return mixed
     */
    public function value(Eloquent $model)
    {
        return $model->getAttribute($this->id);
    }

    /**
     * @inheritdoc
     *
     * @param mixed $value
     *
     * @return mixed
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

    /**
     * Get a field label.
     *
     * It will first look for a label in translated validation attributes.
     * If nothing is found, prettified id is used.
     *
     * @return string
     */
    public function getLabel()
    {
        $translator = $this->entity->getTranslator();

        $key = "validation.attributes.{$this->id}";

        if (($label = $translator->trans($key)) !== $key) return $label;

        return Cruddy\prettify_string($this->id);
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
            'updateable' => $this->updateable,
            'label' => $this->getLabel(),
            'required' => $this->required,
            'copyable' => $this->copyable,

        ] + parent::toArray();
    }
}