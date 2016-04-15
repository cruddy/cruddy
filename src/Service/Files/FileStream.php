<?php

namespace Kalnoy\Cruddy\Service\Files;

class FileStream
{
    /**
     * @var int
     */
    protected $lastModified;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var int
     */
    protected $size;

    /**
     * @var resource
     */
    protected $stream;

    /**
     * @var string
     */
    protected $mime;

    /**
     * FileStream constructor.
     *
     * @param string $path
     * @param resource $stream
     * @param int $size
     * @param string $mime
     * @param int $lastModified
     */
    public function __construct($path, $stream, $size, $mime, $lastModified)
    {
        $this->lastModified = $lastModified;
        $this->path = $path;
        $this->size = $size;
        $this->stream = $stream;
        $this->mime = $mime;
    }

    /**
     * @return int
     */
    public function getLastModified()
    {
        return $this->lastModified;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return resource
     */
    public function getStream()
    {
        return $this->stream;
    }

    /**
     * @return string
     */
    public function getMime()
    {
        return $this->mime;
    }
       
}