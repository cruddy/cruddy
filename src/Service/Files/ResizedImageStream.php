<?php

namespace Kalnoy\Cruddy\Service\Files;

class ResizedImageStream extends FileStream
{
    /**
     * @var int
     */
    protected $width;

    /**
     * @var int
     */
    protected $height;

    /**
     * ResizedImageStream constructor.
     *
     * @param string $path
     * @param resource $stream
     * @param int $size
     * @param string $mime
     * @param int $lastModified
     * @param $width
     * @param $height
     */
    public function __construct($path, $stream, $size, $mime, $lastModified,
                                   $width, $height
    ) {
        parent::__construct($path, $stream, $size, $mime, $lastModified);
        
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * @param FileStream $stream
     * @param $width
     * @param $height
     *
     * @return static
     */
    public static function fromFileStream(FileStream $stream, $width, $height)
    {
        return new static($stream->getPath(), $stream->getStream(), 
                          $stream->getSize(), $stream->getMime(), 
                          $stream->getLastModified(),
                          $width, $height);
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }
    
}