<?php

namespace Kalnoy\Cruddy\Entity\DataSource;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Kalnoy\Cruddy\Entity\Entity;

/**
 * Class DataSource
 *
 * @package Kalnoy\Cruddy\Entity\DataSource
 */
class DataSource
{
    /**
     * @var ColumnsCollection
     */
    private $columns;

    /**
     * @var FiltersCollection
     */
    private $filters;

    /**
     * @var Entity
     */
    protected $entity;

    /**
     * @var callback
     */
    protected $columnsBuilder;

    /**
     * @var callback
     */
    protected $filtersBuilder;

    /**
     * @var int
     */
    public $perPage;

    /**
     * @var array
     */
    public $eagerLoads = [];

    /**
     * @var array
     */
    public $searchable = [];

    /**
     * @var string|array
     */
    public $orderBy;

    /**
     * DataSource constructor.
     *
     * @param Entity $entity
     */
    public function __construct(Entity $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @param string|array $value
     *
     * @return $this
     */
    public function eagerLoads($value)
    {
        $this->eagerLoads = is_array($value) ? $value : func_get_args();

        return $this;
    }

    /**
     * @param $callback
     *
     * @return $this
     */
    public function columns($callback)
    {
        $this->columnsBuilder = $callback;

        return $this;
    }

    /**
     * @param $callback
     *
     * @return $this
     */
    public function filters($callback)
    {
        $this->filtersBuilder = $callback;

        return $this;
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function paginateBy($value)
    {
        $this->perPage = $value;

        return $this;
    }

    /**
     * @param array $value
     *
     * @return $this
     */
    public function searchable($value)
    {
        $this->searchable = $value ? Arr::wrap($value) : [];

        return $this;
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function orderBy($value)
    {
        $this->orderBy = $value;

        return $this;
    }

    /**
     * @param Collection|Model $model
     *
     * @return array|null
     */
    public function data($model)
    {
        if (is_null($model)) {
            return null;
        }

        if ($model instanceof Collection) {
            return $model->map([ $this, 'modelData' ])->all();
        }

        return $this->modelData($model);
    }

    /**
     * @param Model $model
     *
     * @return array
     */
    public function modelData(Model $model)
    {
        $data = $this->entity->modelMeta($model);

        $data['id'] = $model->getKey();
        $data['attributes'] = $this->getColumns()->modelData($model);

        return $data;
    }

    /**
     * @param array $input
     * @param callable|null $constraint
     *
     * @return DataSet
     */
    public function get(array $input, callable $constraint = null)
    {
        $query = $this->entity->newIndexQuery();

        if ($constraint) {
            call_user_func($constraint, $query, $input);
        }

        $total = $query->toBase()->getCountForPagination();
        $perPage = $this->resolvePerPage($input);
        $page = $this->resolvePage($total, $perPage, $input);

        $items = $query
            ->with($this->relationships())
            ->forPage($page, $perPage)
            ->when(Arr::get($input, 'keywords'), $this->keywordsFilter($query->getModel()->getKeyName()))
            ->when(Arr::get($input, 'order', $this->orderBy), $this->order())
            ->when($this->getFilters(), function ($query, FiltersCollection $filters) use ($input) {
                return $filters->apply($query, $input);
            })
            ->get();

        return new DataSet($this->data($items), $total, $page, $perPage);
    }

    /**
     * @return \Closure
     */
    protected function order()
    {
        return function ($query, $order) {
            foreach (Arr::wrap($order) as $attr => $dir) {
                if (is_numeric($attr)) {
                    $attr = $dir;
                    $dir = 'asc';
                }

                $query->orderBy($attr, $dir);
            }
        };
    }

    /**
     * @param $primaryKey
     *
     * @return \Closure
     */
    protected function keywordsFilter($primaryKey)
    {
        return function ($query, $keywords) use ($primaryKey) {
            $query->whereNested(function ($query) use ($keywords, $primaryKey) {
                $query->orWhere($primaryKey, $keywords);

                $keywords = "%$keywords%";

                foreach ($this->searchable as $attr) {
                    $query->orWhere($attr, 'like', $keywords);
                }
            });
        };
    }

    /**
     * @return array
     */
    protected function relationships()
    {
        $relationships = $this->getColumns()->relationships();

        return array_unique(array_merge($relationships, $this->eagerLoads));
    }

    /**
     * @param array $input
     *
     * @return int
     */
    protected function resolvePerPage(array $input)
    {
        return max($this->getPerPage(), Arr::get($input, 'per_page', 0));
    }

    /**
     * @param int $total
     * @param int $perPage
     * @param array $input
     *
     * @return int
     */
    protected function resolvePage($total, $perPage, array $input)
    {
        if ( ! isset($input['page'])) {
            return 1;
        }

        $totalPages = ceil($total / $perPage);

        return max(1, min($totalPages, $input['page']));
    }

    /**
     * @return ColumnsCollection
     */
    public function getColumns()
    {
        if (is_null($this->columns)) {
            return $this->columns = $this->buildColumns();
        }

        return $this->columns;
    }

    /**
     * @return ColumnsCollection
     */
    protected function buildColumns()
    {
        $collection = new ColumnsCollection($this, $this->getColumnsFactory());

        if ($this->columnsBuilder) {
            call_user_func($this->columnsBuilder, $collection);
        }

        return $collection;
    }

    /**
     * @return FiltersCollection
     */
    public function getFilters()
    {
        if (is_null($this->filters)) {
            return $this->filters = $this->buildFilters();
        }

        return $this->filters;
    }

    /**
     * @return FiltersCollection
     */
    protected function buildFilters()
    {
        $collection = new FiltersCollection($this, $this->getFiltersFactory());

        if ($this->filtersBuilder) {
            call_user_func($this->filtersBuilder, $collection);
        }

        return $collection;
    }

    /**
     * @return int
     */
    public function getPerPage()
    {
        if ($this->perPage) {
            return $this->perPage;
        }

        return $this->entity->newModel()->getPerPage();
    }

    /**
     * @return Entity
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return [
            'per_page' => $this->getPerPage(),
            'columns' => $this->getColumns()->getConfig(),
            'filters' => $this->getFilters()->getConfig(),
        ];
    }

    /**
     * @return ColumnsFactory
     */
    public function getColumnsFactory()
    {
        return app('cruddy.entity.columns');
    }

    /**
     * @return FiltersFactory
     */
    public function getFiltersFactory()
    {
        return app('cruddy.entity.filters');
    }
}