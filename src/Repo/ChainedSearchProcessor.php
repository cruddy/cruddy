<?php

namespace Kalnoy\Cruddy\Repo;

use Illuminate\Database\Eloquent\Builder;
use Kalnoy\Cruddy\Contracts\SearchProcessor;

/**
 * Chained search processor for using multiple search processor.
 *
 * @since 1.0.0
 */
class ChainedSearchProcessor implements SearchProcessor
{
    /**
     * The list of processors.
     *
     * @var SearchProcessor[]
     */
    protected $processors;

    /**
     * Init object.
     *
     * @param array $processors
     */
    public function __construct(array $processors = [ ])
    {
        $this->processors = $processors;
    }

    /**
     * Add a processor to the queue.
     *
     * @param SearchProcessor $processor
     */
    public function add(SearchProcessor $processor)
    {
        $this->processors[] = $processor;
    }

    /**
     * {@inheritdoc}
     */
    public function constraintBuilder(Builder $query, array $options)
    {
        foreach ($this->processors as $processor) {
            $processor->constraintBuilder($query, $options);
        }
    }
}