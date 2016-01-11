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
     *
     * @return $this
     */
    public function append(SearchProcessor $processor)
    {
        $this->processors[] = $processor;

        return $this;
    }

    /**
     * @param SearchProcessor $processor
     *
     * @return $this
     */
    public function prepend(SearchProcessor $processor)
    {
        array_unshift($this->processors, $processor);

        return $this;
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

    /**
     * @return array
     */
    public function getProcessors()
    {
        return $this->processors;
    }
}