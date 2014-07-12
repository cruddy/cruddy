<?php

namespace Kalnoy\Cruddy\Repo;

use Illuminate\Database\Eloquent\Builder;

/**
 * Chained search processor for using multiple search processor.
 * 
 * @since 1.0.0
 */
class ChainedSearchProcessor implements SearchProcessorInterface {

    /**
     * The list of processors.
     *
     * @var \Kalnoy\Cruddy\Repo\SearchProcessorInterface[]
     */
    protected $processors;

    /**
     * Init object.
     * 
     * @param array $processors
     */
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
     * {@inheritdoc}
     */
    public function constraintBuilder(Builder $query, array $options)
    {
        foreach ($this->processors as $processor)
        {
            $processor->constraintBuilder($query, $options);
        }
    }
}