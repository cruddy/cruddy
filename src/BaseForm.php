<?php

namespace Kalnoy\Cruddy;

use Illuminate\Contracts\Support\Arrayable;
use Kalnoy\Cruddy\Schema\AttributesCollection;
use Kalnoy\Cruddy\Service\Validation\FluentValidator;
use Illuminate\Contracts\Events\Dispatcher;

abstract class BaseForm implements Arrayable
{
    /**
     * @var Dispatcher
     */
    protected static $dispatcher;

    /**
     * @var Repository
     */
    protected $entities;

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
     * @var Contracts\Validator
     */
    private $validator;

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
     * Set up validator.
     *
     * @param FluentValidator $validate
     */
    protected function rules($validate)
    {
    }

    /**
     * Define the layout.
     *
     * @param Schema\Layout\Layout $l
     */
    protected function layout($l)
    {
    }

    /**
     * @param array $input
     *
     * @return BaseFormData
     */
    abstract public function processInput(array $input);

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
    abstract protected function modelClass();

    /**
     * @return string
     */
    abstract protected function controllerClass();

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
    public function extract($model, AttributesCollection $collection = null)
    {
        if ( ! $model) return null;

        if ($collection === null) $collection = $this->getFields();

        return $collection->extract($model);
    }

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
     * Get the validator.
     *
     * @return Contracts\Validator
     */
    public function getValidator()
    {
        if ($this->validator === null) {
            return $this->validator = $this->createValidator();
        }

        return $this->validator;
    }

    /**
     * @return FluentValidator
     */
    public function createValidator()
    {
        $validator = new FluentValidator;

        $this->rules($validator);

        return $validator;
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
     * Register saving event.
     *
     * @param string $id
     * @param mixed $callback
     *
     * @return void
     */
    public static function saving($id, $callback)
    {
        static::registerEvent($id, 'saving', $callback);
    }

    /**
     * Register saved event.
     *
     * @param string $id
     * @param mixed $callback
     *
     * @return void
     */
    public static function saved($id, $callback)
    {
        static::registerEvent($id, 'saved', $callback);
    }

    /**
     * Register entity event handler.
     *
     * @param string $id
     * @param string $event
     * @param mixed $callback
     *
     * @return void
     */
    public static function registerEvent($id, $event, $callback)
    {
        if ( ! static::$dispatcher) return;

        static::$dispatcher->listen("entity.{$event}: {$id}", $callback);
    }

    /**
     * Fire entity event.
     *
     * @param string $event
     * @param array $payload
     * @param bool $halt
     *
     * @return mixed
     */
    public function fireEvent($event, array $payload, $halt = true)
    {
        if ( ! isset(static::$dispatcher)) return null;

        $event = "entity.{$event}: {$this->id}";

        return static::$dispatcher->fire($event, $payload, $halt);
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
     * @param Repository $entities
     */
    public function setEntitiesRepository(Repository $entities)
    {
        $this->entities = $entities;
    }

    /**
     * @return Repository
     */
    public function getEntitiesRepository()
    {
        return $this->entities;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'model_class' => $this->modelClass(),
            'controller_class' => $this->controllerClass(),
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
        return $this->lang ?: app('cruddy.lang');
    }

    /**
     * @param Dispatcher $dispatcher
     */
    public static function setEventDispatcher(Dispatcher $dispatcher)
    {
        static::$dispatcher = $dispatcher;
    }

    /**
     * @return Dispatcher
     */
    public static function getEventDispatcher()
    {
        return static::$dispatcher;
    }
}