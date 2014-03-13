<?php

namespace Kalnoy\Cruddy\Repo;

use Illuminate\Database\Eloquent\Builder;

class ChainedSearchProcessor implements SearchProcessorInterface {

    /**
     * The list of processors.
     *
     * @var \Kalnoy\Cruddy\Repo\SearchProcessorInterface[]
     */
    protected $processors;

    public function __construct(array $processors = [])
    {
        $this->processors = $processors;
    }

    /**
     * Add a processor to the queue.
     *
     * @param \Kalnoy\Cruddy\Repo\SearchProcessorInterface $processor
     */
    public function add(SearchProcessorInterface $processor)
    {
        $this->processors[] = $processor;
    }

    /**
     * @inheritdoc
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array                                 $options
     *
     * @return void
     */
    public function search(Builder $query, array $options)
    {
        foreach ($this->processors as $processor)
        {
            $processor->search($query, $options);
        }
    }
}