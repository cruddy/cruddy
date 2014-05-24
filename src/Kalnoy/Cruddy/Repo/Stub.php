<?php

namespace Kalnoy\Cruddy\Repo;

/**
 * Basic repository class.
 * 
 * You can provide model's default attributes and override per page value.
 * 
 * @since 1.0.0
 */
class Stub extends BaseRepository {

    /**
     * The model class name.
     *
     * @var string
     */
    protected $className;

    /**
     * Default attributes.
     *
     * @var array
     */
    protected $defaults;

    /**
     * Override the per page value.
     * 
     * @var int
     */
    public $perPage;

    /**
     * Init repo.
     *
     * @param string                            $className
     * @param array                             $defaults
     * @param \Illuminate\Filesystem\Filesystem $file
     */ 
    public function __construct($className, array $defaults = [])
    {
        $this->className = $className;
        $this->defaults = $defaults;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    public function getPerPage()
    {
        return $this->perPage ?: parent::getPerPage();
    }

    /**
     * {@inheritdoc}
     */
    public function newModel()
    {
        return new $this->className($this->defaults);
    }
}