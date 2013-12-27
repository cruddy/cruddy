<?php namespace Kalnoy\Cruddy;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Builder;

abstract class Attribute implements AttributeInterface {

    /**
     * The entity.
     *
     * @var Entity
     */
    protected $entity;

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
     * @param string $id
     * @param mixed $model
     */
    public function __construct(Entity $entity, $type, $id)
    {
        $this->entity = $entity;
        $this->type = $type;
        $this->id = $id;
    }

    /**
     * Modify a query builder.
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
     * Get the entity that ownes a field.
     *
     * @return Entity
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Get the column id.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    public function getHelp()
    {
        return $this->translate("help");
    }

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
     * Get the field configuration.
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
     * Convert the field into a json string.
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