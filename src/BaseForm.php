<?php

namespace Kalnoy\Cruddy;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Validation\Validator;
use Kalnoy\Cruddy\Schema\AttributesCollection;
use Illuminate\Contracts\Events\Dispatcher;

abstract class BaseForm implements Arrayable
{
    /**
     * @var Lang
     */
    protected $lang;

    /**
     * The id.
     *
     * @var string
     */
    protected $id;

    /**
     * @var Schema\Layout\Layout
     */
    private $layout;

    /**
     * The field list.
     *
     * @var Schema\Fields\Collection
     */
    private $fields;

    /**
     * Specify the fields.
     *
     * @param Schema\Fields\InstanceFactory $schema
     */
    abstract protected function fields($schema);

    /**
     * Define the layout.
     *
     * @param Schema\Layout\Layout $l
     */
    protected function layout($l) {}

    /**
     * @return mixed
     */
    abstract public function getTitle();

    /**
     * @return array
     */
    abstract public function getPermissions();

    /**
     * @return string
     */
    abstract protected function getModelClass();

    /**
     * @return string
     */
    abstract protected function getControllerClass();

    /**
     * @param array $options
     *
     * @return mixed
     */
    abstract public function index(array $options);

    /**
     * Extract model fields.
     *
     * @param mixed $model
     * @param AttributesCollection $collection
     *
     * @return array
     */
    public function getModelData($model, AttributesCollection $collection = null)
    {
        if ( ! $model) return null;

        if ($collection === null) $collection = $this->getFields();

        return $collection->getModelData($model);
    }

    /**
     * Get the attribute of the model.
     *
     * @param $model
     * @param $attribute
     *
     * @return mixed
     */
    abstract public function getModelAttributeValue($model, $attribute);

    /**
     * Set the value of attribute of the model.
     *
     * @param $model
     * @param $value
     * @param $attribute
     *
     * @return
     */
    abstract public function setModelAttributeValue($model, $value, $attribute);

    /**
     * Translate line.
     *
     * If key isn't namespaced, looks for a key under entity's id namespace,
     * then under `entities` namespace. Othwerwise, just translates line as is.
     *
     * @param string $key
     * @param string $default
     *
     * @return string
     */
    public function translate($key, $default = null)
    {
        $lang = $this->getLang();

        if (false !== $pos = strpos($key, '::')) {
            if ($pos === 0) $key = substr($key, 2);

            return $lang->translate($key, $default);
        }

        $line = $lang->translate("{$this->id}.{$key}");

        if ($line !== null) return $line;

        return $lang->translate("entities.{$key}", $default);
    }

    /**
     * Get field collection.
     *
     * @return Schema\Fields\Collection
     */
    public function getFields()
    {
        if ($this->fields === null) return $this->fields = $this->createFields();

        return $this->fields;
    }

    /**
     * Create field collection.
     *
     * @return Schema\Fields\Collection
     */
    protected function createFields()
    {
        $collection = new Schema\Fields\Collection($this);

        $factory = new Schema\Fields\InstanceFactory($this->getFieldsFactory(),
                                                     $collection);

        $this->fields($factory);

        return $collection;
    }

    /**
     * @return Schema\Layout\Layout
     */
    public function getLayout()
    {
        if ($this->layout === null) {
            return $this->layout = $this->createLayout();
        }

        return $this->layout;
    }

    /**
     * @return Schema\Layout\Layout
     */
    protected function createLayout()
    {
        $layout = new Schema\Layout\Layout;

        $this->layout($layout);

        return $layout;
    }

    /**
     * Get entity's id.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set an id.
     *
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'model_class' => $this->getModelClass(),
            'controller_class' => $this->getControllerClass(),
            'title' => $this->getTitle(),

            'fields' => $this->getFields()->toArray(),
            'layout' => $this->getLayout()
                             ->isEmpty() ? null : $this->getLayout()->toArray(),
        ];
    }

    /**
     * @return Schema\Fields\Factory
     */
    protected function getFieldsFactory()
    {
        return app('cruddy.fields');
    }

    /**
     * @return Contracts\Permissions
     */
    protected function getPermissionsDriver()
    {
        return app('cruddy.permissions');
    }

    /**
     * @param Lang $lang
     */
    public function setLang(Lang $lang)
    {
        $this->lang = $lang;
    }

    /**
     * @return Lang
     */
    public function getLang()
    {
        if ($this->lang) return $this->lang;

        return $this->lang = app('cruddy.lang');
    }

}