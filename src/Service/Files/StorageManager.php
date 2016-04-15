<?php

namespace Kalnoy\Cruddy\Service\Files;

use Illuminate\Config\Repository;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Manager;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;

class StorageManager
{
    /**
     * @var Repository
     */
    protected $config;

    /**
     * @var array
     */
    protected $storageList = [];

    /**
     * @var FilesystemManager
     */
    protected $filesystemManager;

    /**
     * @var ImageManager
     */
    protected $imageManager;

    /**
     * StorageManager constructor.
     *
     * @param Repository $config
     * @param FilesystemManager $filesystemManager
     * @param ImageManager $imageManager
     */
    public function __construct(Repository $config, 
                                FilesystemManager $filesystemManager, 
                                ImageManager $imageManager
    ) {
        $this->config = $config;
        $this->filesystemManager = $filesystemManager;
        $this->imageManager = $imageManager;
    }

    /**
     * @param string $name
     *
     * @return FileStorage
     */
    public function storage($name)
    {
        if ( ! $name) {
            throw new \InvalidArgumentException('Default storage name not specified.');
        }

        if (isset($this->storageList[$name])) {
            return $this->storageList[$name];
        }

        return $this->storageList[$name] = $this->createStorage($name);
    }

    /**
     * @param string $name
     *
     * @return FileStorage
     */
    protected function createStorage($name)
    {
        $configKey = 'cruddy.storage.'.$name;

        if ( ! $this->config->has($configKey)) {
            throw new \InvalidArgumentException("Unknown storage [{$name}].");
        }

        $config = $this->config->get($configKey);
        
        $type = Arr::pull($config, 'type', 'files');

        $storage = $this->createStorageOfType($type);

        foreach ($config as $method => $value) {
            $method = 'set'.Str::studly($method);

            $storage->$method($value);
        }

        return $storage;
    }

    /**
     * @param $type
     *
     * @return FileStorage|ImageStorage
     */
    protected function createStorageOfType($type)
    {
        switch ($type) {
            case 'files': {
                return new FileStorage($this->filesystemManager);
            }
            
            case 'images': {
                $storage = new ImageStorage($this->filesystemManager);
                
                $storage->setImageManager($this->imageManager);
                
                return $storage;
            }
        }
        
        throw new \InvalidArgumentException("Invalid file storage type [{$type}].");
    }
}