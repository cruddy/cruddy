<?php

namespace Kalnoy\Cruddy\Schema;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Fluent;
use Kalnoy\Cruddy\Contracts\Schema;
use Kalnoy\Cruddy\Repo\Stub as StubRepository;
use Kalnoy\Cruddy\Service\Validation\FluentValidator;
use Kalnoy\Cruddy\Entity;
use Symfony\Component\Process\Exception\RuntimeException;

/**
 * Base schema.
 *
 * @since 1.0.0
 */
abstract class BaseSchema implements Schema {

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
     * The path to the Backbone view class that will display the entity.
     *
     * @var string
     */
    protected $view = 'Cruddy.Entity.Page';

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
     * @param Filters\InstanceFactory $schema
     */
    public function filters($schema)
    {
        foreach ($this->filters as $field)
        {
            $schema->usingField($field);
        }
    }

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
     * @param Actions\Collection $actions
     */
    protected function actions($actions) {}

    /**
     * @param Model $model
     *
     * @return array
     */
    protected function exportActions(Model $model)
    {
        return $this->getActions()->export($model);
    }

    /**
     * @return Actions\Collection
     */
    protected function getActions()
    {
        $collection = new Actions\Collection;

        $this->actions($collection);

        return $collection;
    }

    public function executeAction(Model $model, $action)
    {
        return $this->getActions()->execute($model, $action);
    }

    /**
     * @param Model $model
     * @param bool $simplified
     *
     * @return array
     */
    public function meta(Model $model, $simplified)
    {
        $meta['title'] = $this->toString($model);

        if ( ! $simplified)
        {
            $meta['externalUrl'] = $this->externalUrl($model);
            $meta['actions'] = $this->exportActions($model);
        }

        return $meta;
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
     * Convert the model to string.
     *
     * @param Model $model
     *
     * @return string
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
    public function layout($l) {}

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
        return [
            'order_by' => $this->defaultOrder,
            'view' => $this->view,
            'filters' => $this->filters,
            'layout' => $this->compileLayout(),
        ];
    }
}