<?php namespace Kalnoy\Cruddy\Entity\Attribute;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Kalnoy\Cruddy\Entity\Entity;

abstract class Attribute implements AttributeInterface {

    /**
     * The entity.
     *
     * @var Entity
     */
    protected $entity;

    /**
     * The type of the attribute that is received from the factory.
     *
     * @var string
     */
    protected $type;

    /**
     * The id of the attribute.
     *
     * @var string
     */
    protected $id;

    /**
     * Whether the attribute is visible.
     *
     * @var bool
     */
    public $visible = true;

    /**
     * Initialize the field.
     *
     * @param \Kalnoy\Cruddy\Entity\Entity $entity
     * @param string                       $type
     * @param string                       $id
     *
     * @internal param mixed $model
     */
    public function __construct(Entity $entity, $type, $id)
    {
        $this->entity = $entity;
        $this->type = $type;
        $this->id = $id;
    }

    /**
     * @inheritdoc
     *
     * Default is no-op.
     *
     * @param  Builder $builder
     *
     * @return void
     */
    public function modifyQuery(Builder $builder) {}

    /**
     * Evaluate a value.
     *
     * @param  mixed $value
     * @param  Eloquent $model
     *
     * @return mixed
     */
    protected function evaluate($value, Eloquent $model)
    {
        return is_callable($value) ? $value($model, $this) : $value;
    }

    /**
     * Get runtime data.
     *
     * @param  Eloquent $model
     *
     * @return array
     */
    public function runtime(Eloquent $model)
    {
        return array('id' => $this->id);
    }

    /**
     * @inheritdoc
     *
     * @return Entity
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @inheritdoc
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get help for an attribute.
     *
     * @return string
     */
    public function getHelp()
    {
        return $this->translate('help');
    }

    /**
     * Translate given key.
     *
     * It will first look for `<entity>.<group>.<id>`, then in `entities.<group>.<id>`.
     *
     * @param string|null $group
     *
     * @return string
     */
    protected function translate($group = null)
    {
        $key = $this->id;

        if ($group !== null) $key = "{$group}.{$key}";

        if ($line = $this->entity->translate($key)) return $line;

        $translator = $this->entity->getTranslator();
        $entry = "entities.{$key}";

        if (($line = $translator->trans($entry)) !== $entry) return $line;
    }

    /**
     * Convert field to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return array_merge(
            array(
                'class' => $this->getJavaScriptClass(),
                'help' => $this->getHelp(),
                'visible' => $this->visible,
                'type' => $this->type,
            ),

            $this->runtime($this->entity->form()->instance())
        );
    }

    /**
     * Convert the attribute into a json string.
     *
     * @param  int    $options
     *
     * @return string
     */
    public function toJSON($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }
}