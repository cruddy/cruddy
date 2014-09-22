<?php

namespace Kalnoy\Cruddy\Schema;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Kalnoy\Cruddy\Repo\RepositoryInterface;
use Kalnoy\Cruddy\Repo\Stub as StubRepository;
use Kalnoy\Cruddy\Repo\BaseRepository;
use Kalnoy\Cruddy\Form\BasicForm;
use Kalnoy\Cruddy\Service\Validation\FluentValidator;
use Kalnoy\Cruddy\Entity;

/**
 * Base schema.
 *
 * @since 1.0.0
 */
abstract class BaseSchema implements SchemaInterface {

    /**
     * The state of model when it is new.
     */
    const WHEN_NEW = 'create';

    /**
     * The state of model when it is exists.
     */
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
     * The number of items per page.
     *
     * Set this value to override default model's value.
     *
     * @var int
     */
    protected $perPage;

    /**
     * The template JavaScript class name under `Cruddy.Entity.Templates` namespace.
     *
     * @var string
     */
    protected $template = 'Basic';

    /**
     * {@inheritdoc}
     */
    public function entity()
    {
        return new Entity($this);
    }

    /**
     * {@inheritdoc}
     */
    public function columns($schema) {}

    /**
     * {@inheritdoc}
     */
    public function repository()
    {
        $repo = new StubRepository($this->model, $this->defaults());

        $repo->perPage = $this->perPage;

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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function extra($model, $simplified)
    {
        if ($simplified) return [];

        return [ 'external' => $this->externalUrl($model) ];
    }

    /**
     * Get the url to the model on main site.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return string
     */
    protected function externalUrl($model) {}

    /**
     * {@inheritdoc}
     *
     * Default implementation will try to get {@see $titleAttribute} attribute and if one
     * is not set will return model's key.
     */
    public function toString($model)
    {
        return $this->titleAttribute
            ? $model->getAttribute($this->titleAttribute)
            : $model->getKey();
    }

    /**
     * Define a layout.
     *
     * @param Layout\Layout $l
     *
     * @return void
     */
    public function layout($l)
    {

    }

    /**
     * Compile a layout.
     *
     * @return array|null
     */
    private function compileLayout()
    {
        $l = new Layout\Layout;

        $this->layout($l);

        return $l->isEmpty() ? null : $l->compileItems();
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return
        [
            'order_by' => $this->defaultOrder,
            'template' => $this->template,
            'filters' => $this->filters,
            'layout' => $this->compileLayout(),
        ];
    }
}