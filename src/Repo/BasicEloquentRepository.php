<?php

namespace Kalnoy\Cruddy\Repo;

/**
 * Basic repository class.
 *
 * You can provide model's default attributes and override per page value.
 *
 * @since 1.0.0
 */
class BasicEloquentRepository extends AbstractEloquentRepository
{
    /**
     * The model class name.
     *
     * @var string
     */
    protected $className;

    /**
     * Override the per page value.
     *
     * @var int
     */
    public $perPage;

    /**
     * Init repo.
     *
     * @param string $className
     * @param array $defaults
     */
    public function __construct($className)
    {
        $this->className = $className;

        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    public function getPerPage()
    {
        return $this->perPage ?: parent::getPerPage();
    }

    /**
     * @inheritdoc
     */
    public function newModel()
    {
        return new $this->className;
    }

}