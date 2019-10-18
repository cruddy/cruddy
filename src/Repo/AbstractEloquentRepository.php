<?php

namespace Kalnoy\Cruddy\Repo;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;
use Kalnoy\Cruddy\Contracts\Repository;
use Kalnoy\Cruddy\Contracts\SearchProcessor;
use Kalnoy\Cruddy\ModelNotFoundException;
use Kalnoy\Cruddy\ModelNotSavedException;
use Kalnoy\Cruddy\Service\Files\FileStorage;
use Exception;

/**
 * Base repository class.
 *
 * @since 1.0.0
 */
abstract class AbstractEloquentRepository implements Repository
{
    /**
     * The model instance.
     *
     * @var Model
     */
    protected $model;

    /**
     * Init the repo.
     */
    public function __construct()
    {
        $this->model = $this->newModel();
    }

    /**
     * @return Model
     */
    abstract public function newModel();

    /**
     * Get new query.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function newQuery()
    {
        return $this->model->newQueryWithoutScopes();
    }

    /**
     * @inheritdoc
     */
    public function find($id)
    {
        $model = $this->newQuery()->find($id);

        if ($model === null) {
            throw new ModelNotFoundException;
        }

        return $model;
    }

    /**
     * @inheritdoc
     */
    public function search(array $options, SearchProcessor $processor = null)
    {
        $builder = $this->newQuery();

        if ($processor) {
            $processor->constraintBuilder($builder, $options);
        }

        return $this->paginate($builder, $options);
    }

    /**
     * Get per page items count.
     *
     * @return int
     */
    public function getPerPage()
    {
        return $this->model->getPerPage();
    }

    /**
     * Save a model.
     *
     * @param Model $model
     *
     * @return bool
     */
    public function save($model)
    {
        return $model->save();
    }

    /**
     * @inheritdoc
     */
    public function delete($ids)
    {
        $ids = is_array($ids) ? $ids : func_get_args();

        if (empty($ids)) return 0;

        $key = $this->model->getKeyName();

        $count = 0;

        foreach ($this->newQuery()->whereIn($key, $ids)->get() as $item) {
            if ($item->delete()) $count++;
        }

        return $count;
    }

    /**
     * @param $builder
     * @param array $options
     *
     * @return array
     */
    protected function paginate(Builder $builder, array $options)
    {
        $query = $builder->getQuery();
        $total = $query->getCountForPagination();

        $perPage = Arr::get($options, 'per_page', $this->getPerPage());
        $lastPage = (int)ceil($total / $perPage);
        $page = max(1, min($lastPage, (int)Arr::get($options, 'page', 1)));

        $query->forPage($page, $perPage);

        /** @var \Illuminate\Support\Collection $items */
        $items = $builder->get();

        $from = ($page - 1) * $perPage + 1;
        $to = $from + $items->count() - 1;

        return compact('total', 'page', 'perPage', 'lastPage', 'from', 'to', 'items');
    }

    /**
     * @return void
     */
    public function startTransaction()
    {
        $this->newModel()->getConnection()->beginTransaction();
    }

    /**
     * @return void
     */
    public function commitTransaction()
    {
        $this->newModel()->getConnection()->commit();
    }

}