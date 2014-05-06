<?php

namespace Kalnoy\Cruddy\Schema;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Kalnoy\Cruddy\Repo\RepositoryInterface;
use Kalnoy\Cruddy\Repo\Stub as StubRepository;
use Kalnoy\Cruddy\Repo\BaseRepository;
use Kalnoy\Cruddy\Form\BasicForm;
use Kalnoy\Cruddy\Service\Validation\FluentValidator;

/**
 * Base schema.
 */
abstract class BaseSchema implements SchemaInterface {
    
    const WHEN_NEW = 'create';

    const WHEN_EXISTS = 'update';

    /**
     * The model class name.
     *
     * @var string
     */
    protected $model;

    /**
     * The array of default attributes.
     *
     * @var array
     */
    protected $defaults = [];

    /**
     * The list of complex filters.
     *
     * @var array
     */
    protected $filters = [];

    /**
     * The attribute that is used to convert model to a string.
     *
     * @var string
     */
    protected $titleAttribute;

    /**
     * The id of column that will be ordered by default.
     *
     * @var string
     */
    protected $defaultOrder;

    /**
     * The template JavaScript class name under `Cruddy.Entity.Templates` namespace.
     *
     * @var string
     */
    protected $template = 'Basic';

    /**
     * @inheritdoc
     *
     * @param mixed $schema
     *
     * @return void
     */
    public function columns($schema) {}

    /**
     * @inheritdoc
     *
     * @return \Kalnoy\Cruddy\Repo\RepositoryInterface
     */
    public function repository()
    {
        $repo = new StubRepository($this->model, $this->defaults());

        $this->files($repo);

        return $repo;
    }

    /**
     * Get default attributes.
     *
     * @return array
     */
    protected function defaults()
    {
        return $this->defaults;
    }

    /**
     * Specify what files repository uploads.
     *
     * @param \Kalnoy\Cruddy\Repo\BaseRepository $repo
     *
     * @return void
     */
    public function files($repo) {}

    /**
     * Create validator.
     *
     * @return \Kalnoy\Cruddy\Service\Validation\ValidableInterface
     */
    public function validator()
    {
        $validator = new FluentValidator;

        $this->rules($validator);

        return $validator;
    }

    /**
     * Set up validator.
     *
     * @param \Kalnoy\Cruddy\Service\Validation\FluentValidator $validate
     *
     * @return void
     */
    protected function rules($validate) {}

    /**
     * @inheritdoc
     *
     * Default implementation will try to get {@see $titleAttribute} attribute and if one
     * is not set will return model's key.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return string
     */
    public function toString(Eloquent $model)
    {
        return $this->titleAttribute ? $model->getAttribute($this->titleAttribute) : $model->getKey();
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
            'order_by' => $this->defaultOrder,
            'template' => $this->template,
            'filters' => $this->filters,
        ];
    }
}