<?php namespace Kalnoy\Cruddy\Entity;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Kalnoy\Cruddy\EntityNotFoundException;
use Kalnoy\Cruddy\FileUploader;
use Kalnoy\Cruddy\PermissionsInterface;
use RuntimeException;
use Symfony\Component\Translation\TranslatorInterface;
use Illuminate\Container\Container;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Validation\Factory as ValidationFactory;
use Kalnoy\Cruddy\Entity\Fields\Factory as FieldFactory;
use Kalnoy\Cruddy\Entity\Columns\Factory as ColumnFactory;
use Kalnoy\Cruddy\Entity\Related\Factory as RelatedFactory;

class Factory {

    protected $container;

    protected $files;

    protected $config;

    protected $translator;

    protected $validator;

    protected $permissions;

    protected $fields;

    protected $columns;

    protected $related;

    protected $entities = array();

    /**
     * Initialize the factory.
     *
     * @param Container                           $container
     * @param \Illuminate\Filesystem\Filesystem   $files
     * @param TranslatorInterface                 $translator
     * @param ConfigRepository                    $config
     * @param ValidationFactory                   $validator
     * @param \Kalnoy\Cruddy\PermissionsInterface $permissions
     * @param FieldFactory                        $fields
     * @param ColumnFactory                       $columns
     * @param Related\Factory                     $related
     *
     * @internal param \Illuminate\Support\Str $str
     */
    public function __construct(Container $container, Filesystem $files, TranslatorInterface $translator,
                                ConfigRepository $config, ValidationFactory $validator,
                                PermissionsInterface $permissions, FieldFactory $fields, ColumnFactory $columns,
                                RelatedFactory $related)
    {
        $this->translator = $translator;
        $this->config = $config;
        $this->fields = $fields;
        $this->columns = $columns;
        $this->related = $related;
        $this->validator = $validator;
        $this->permissions = $permissions;
        $this->container = $container;
        $this->files = $files;
    }

    /**
     * Get a model by an id.
     *
     * Read model configuration from app/config/entities/{id}.php
     *
     * @param  string $id
     *
     * @throws \Kalnoy\Cruddy\EntityNotFoundException
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

        $entity = new Entity($this, $this->permissions, $this->translator, $id);

        $entity->configure($config);

        return $this->entities[$id] = $entity;
    }

    /**
     * Get a form processor from configuration for an entity.
     *
     * @param Entity $entity
     * @throws RuntimeException
     *
     * @return mixed
     */
    public function createForm(Entity $entity)
    {
        $config = $this->config("{$entity->getId()}.form");

        if ($config === null)
        {
            throw new RuntimeException("The entity configuration must have a form definition.");
        }

        // If user specified string it means he uses custom form processor
        // and we simple resolve it through the container.
        if (is_string($config))
        {
            return $this->container->make($config);
        }

        $model = $this->container->make(array_get($config, 'model'));
        $validator = $this->createValidator($config);

        return new Form($model, $validator, $this->createFiles($config));
    }

    /**
     * @inheritdoc
     *
     * @param  array  $config
     *
     * @return Fields\Collection
     */
    public function createFields(Entity $entity)
    {
        return $this->createCollection($entity, 'fields', $this->fields, true);
    }

    /**
     * @inheritdoc
     *
     * @param  array  $config
     *
     * @return Columns\Collection
     */
    public function createColumns(Entity $entity)
    {
        return $this->createCollection($entity, 'columns', $this->columns);
    }

    public function createRelated(Entity $entity)
    {
        return $this->createCollection($entity, 'related', $this->related);
    }

    /**
     * Create a new attribute collection.
     *
     * @param  Entity           $entity
     * @param  string           $key
     * @param  Attribute\Factory $factory
     *
     * @param bool              $required
     * @throws \RuntimeException
     * @return Attribute\Collection
     */
    protected function createCollection(Entity $entity, $key, $factory, $required = false)
    {
        $items = $this->config("{$entity->getId()}.{$key}");

        if (empty($items))
        {
            if ($required)
            {
                throw new RuntimeException("The {$entity->getId()} configuration must include at least one item in {$key} configuration.");
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

    protected function createFiles(array $config)
    {
        $files = array_get($config, 'files');

        if (empty($files)) return [];

        foreach ($files as $i => $uploader)
        {
            if (is_string($uploader))
            {
                $i = $uploader;
                $uploader = [];
            }

            $files[$i] = $this->createUploader($uploader);
        }

        return $files;
    }

    protected function createUploader(array $config)
    {
        $multiple = array_get($config, 'multiple', false);
        $keepNames = array_get($config, 'keepNames', false);
        $root = array_get($config, 'root') ?: public_path();
        $path = array_get($config, 'path', 'files');

        return new FileUploader($this->files, $root, $path, $keepNames, $multiple);
    }

    protected function config($key)
    {
        return $this->config->get("entities::{$key}");
    }
}