<?php

namespace Kalnoy\Cruddy\Entity\DataSource;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Collection;

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

    /**
     * DataSet constructor.
     *
     * @param array $items
     * @param $total
     * @param $page
     * @param $perPage
     */
    public function __construct(array $items, $total, $page, $perPage
    ) {
        $this->items = $items;
        $this->page = $page;
        $this->perPage = $perPage;
        $this->total = $total;
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
            'perPage' => $this->perPage,
            'page' => $this->page,
            'total' => $this->total,
            'items' => $this->items,
            'from' => $this->getFrom(),
            'to' => $this->getTo(),
            'lastPage' => $this->getLastPage(),
        ];
    }
}