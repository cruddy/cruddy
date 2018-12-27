<?php

namespace Kalnoy\Cruddy\Entity\DataSource;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

class DataSet implements Arrayable
{
    /**
     * @var array
     */
    protected $items;

    /**
     * @var int
     */
    protected $page;

    /**
     * @var int
     */
    protected $perPage;

    /**
     * @var int
     */
    protected $total;

    protected $stats;

    /**
     * DataSet constructor.
     *
     * @param array $items
     * @param $total
     * @param $page
     * @param $perPage
     */
    public function __construct(array $items, $total, $page, $perPage, $stats) {
        $this->items = $items;
        $this->page = $page;
        $this->perPage = $perPage;
        $this->total = $total;
        $this->stats = $stats;
    }

    /**
     * @return Collection
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @return int
     */
    public function getPerPage()
    {
        return $this->perPage;
    }

    /**
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @return int
     */
    public function getFrom()
    {
        return ($this->page - 1) * $this->perPage + 1;
    }

    /**
     * @return int
     */
    public function getTo()
    {
        return $this->page * $this->perPage;
    }

    /**
     * @return int
     */
    public function getLastPage()
    {
        return ceil($this->total / $this->perPage);
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'perPage' => (int)$this->perPage,
            'page' => (int)$this->page,
            'total' => (int)$this->total,
            'items' => $this->items,
            'from' => (int)$this->getFrom(),
            'to' => (int)$this->getTo(),
            'lastPage' => (int)$this->getLastPage(),
            'stats' => Arr::wrap($this->stats),
        ];
    }
}