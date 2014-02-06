<?php

namespace Kalnoy\Cruddy\Repo;

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
     * Init repo.
     *
     * @param string                            $className
     * @param array                             $defaults
     * @param \Illuminate\Filesystem\Filesystem $file
     */ 
    public function __construct($className, array $defaults = [], $file = null)
    {
        $this->className = $className;
        $this->defaults = $defaults;

        parent::__construct($file);
    }

    /**
     * @inheritdoc
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function newModel()
    {
        return new $this->className($this->defaults);
    }
}