<?php namespace Kalnoy\Cruddy;

use Kalnoy\Cruddy\Fields\Collection as FieldCollection;
use Symfony\Component\Translation\TranslatorInterface;
use Illuminate\Container\Container;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Validation\Factory as ValidationFactory;
use Kalnoy\Cruddy\Fields\Factory as FieldFactory;
use Kalnoy\Cruddy\Columns\Factory as ColumnFactory;
use Kalnoy\Cruddy\Related\Factory as RelatedFactory;
use Illuminate\Support\Collection;

class Factory implements FactoryInterface {

    protected $config;

    protected $container;

    protected $translator;

    protected $validator;

    protected $fields;

    protected $columns;

    protected $related;

    protected $entities = array();

    protected $generatedFields = array("primary", "timestamps");

    /**
     * Initialize the factory.
     *
     * @param Container           $container
     * @param TranslatorInterface $translator
     * @param ConfigRepository    $config
     * @param ValidationFactory   $validator
     * @param FieldFactory        $fields
     * @param ColumnFactory       $columns
     */
    public function __construct(Container $container, TranslatorInterface $translator, ConfigRepository $config, ValidationFactory $validator, FieldFactory $fields, ColumnFactory $columns, RelatedFactory $related)
    {
        $this->container = $container;
        $this->translator = $translator;
        $this->config = $config;
        $this->fields = $fields;
        $this->columns = $columns;
        $this->related = $related;
        $this->validator = $validator;
    }

    /**
     * Get a model by an id.
     *
     * Read model configuration from app/config/entities/{id}.php
     *
     * @param  string $id
     *
     * @return Entity
     */
    public function resolve($id)
    {
        if (isset($this->entities[$id])) return $this->entities[$id];

        $config = $this->config($id);

        if (empty($config))
        {
            throw new EntityNotFoundException("The configuration for {$id} is not exists or empty.");
        }

        $permissions = $this->container->make("cruddy.permissions");

        $entity = $this->entities[$id] = new Entity($this, $permissions, $id);

        return $entity;
    }

    /**
     * Get a form processor from configuration for an entity.
     *
     * @param  string $id
     *
     * @return mixed
     */
    public function createForm($id)
    {
        $config = $this->config("$id.form");

        if ($config === null)
        {
            throw new \RuntimeException("The entity configuration must have a form definition.");
        }

        // If user specified string it means he uses custom form processor
        // and we simple resolve it through the container.
        if (is_string($config))
        {
            return $this->container->make($config);
        }

        $model = $this->container->make(array_get($config, 'model'));
        $validator = $this->createValidator($config);

        return new Form($model, $validator);
    }

    /**
     * Get a field collection from an entity configuration.
     *
     * @param  array  $config
     *
     * @return void
     */
    public function createFields(Entity $entity)
    {
        $fields = $this->createCollection($entity, "fields", $this->fields, true);

        return $this->generateFields($entity, $fields);
    }

    protected function generateFields(Entity $entity, Collection $fields)
    {
        foreach ($this->generatedFields as $method)
        {
            $method = "generate{$method}";

            $this->$method($entity, $fields);
        }

        return $fields;
    }

    protected function generatePrimary(Entity $entity, Collection $fields)
    {
        $instance = $entity->form()->instance();
        $key = $instance->getKeyName();

        if (!$fields->has($key))
        {
            $field = $this->fields->create($entity, 'primary', $key);
            $fields->put($key, $field);
        }
    }

    protected function generateTimestamps(Entity $entity, Collection $fields)
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
                if ($fields->has($id)) continue;

                $field = $this->fields->create($entity, 'datetime', $id);

                $fields->put($id, $field);
            }
        }
    }

    /**
     * Get a column collection from an entity configuration.
     *
     * @param  array  $config
     *
     * @return void
     */
    public function createColumns(Entity $entity)
    {
        return $this->createCollection($entity, "columns", $this->columns);
    }

    public function createRelated(Entity $entity)
    {
        return $this->createCollection($entity, "related", $this->related);
    }

    /**
     * Create a new attribute collection.
     *
     * @param  Entity $entity
     * @param  string $key
     * @param  AttributeFactory $factory
     *
     * @return AttributeCollection
     */
    protected function createCollection(Entity $entity, $key, $factory, $required = false)
    {
        $items = $this->config("{$entity->getId()}.{$key}");

        if (empty($items))
        {
            if ($required)
            {
                throw new \RuntimeException("The {$entity->getId()} configuration must include at least one item in {$key} configuration.");
            }

            return $factory->newCollection();
        }

        return $factory->createFromCollection($entity, $items);
    }

    /**
     * Create a validator from a config.
     *
     * @param  array  $config
     *
     * @return \Illuminate\Validation\Validator
     */
    protected function createValidator(array $config)
    {
        $rules = array_get($config, 'rules', array());
        $messages = array_get($config, 'messages', array());
        $customAttributes = array_get($config, 'customAttributes', array());

        return $this->validator->make(array(), $rules, $messages, $customAttributes);
    }

    /**
     * Get model configuration.
     *
     * @param  string $id
     *
     * @return array
     */
    public function config($key, $default = null)
    {
        return $this->config->get("entities::$key", $default);
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getTranslator()
    {
        return $this->translator;
    }

    public function getValidator()
    {
        return $this->validator;
    }
}