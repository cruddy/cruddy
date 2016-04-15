<?php

namespace Kalnoy\Cruddy\Service\Files;

class Image extends File
{
    /**
     * @var int
     */
    protected $height;

    /**
     * @var int
     */
    protected $width;

    /**
     * Image constructor.
     *
     * @param ImageStorage $storage
     * @param string $path
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @param int $width
     * @param int $height
     */
    public function __construct($storage, $path, $file, $width, $height)
    {
        parent::__construct($storage, $path, $file);

        $this->height = $height;
        $this->width = $width;
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