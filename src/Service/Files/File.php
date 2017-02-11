<?php

namespace Kalnoy\Cruddy\Service\Files;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class File
{
    /**
     * @var UploadedFile
     */
    protected $file;

    /**
     * @var string
     */
    protected $path;

    /**
     * File constructor.
     *
     * @param FileStorage $storage
     * @param string $path
     * @param UploadedFile $file
     */
    public function __construct($storage, $path, $file)
    {
        $this->file = $file;
        $this->path = $path;
        $this->storage = $storage;
    }

    /**
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return FileStorage
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return $this->path;
    }

}