<?php

namespace Kalnoy\Cruddy\Service\Files;

use Intervention\Image\Image as InterventionImage;
use Intervention\Image\ImageManager;
use Intervention\Image\Size;

class ImageStorage extends FileStorage
{
    /**
     * @var array
     */
    public static $supportedImages = [
        'image/jpg', 'image/jpeg', 'image/pjpeg',
        'image/png', 'image/x-png',
        'image/gif',
    ];

    /**
     * @var ImageManager
     */
    protected $imageManager;

    /**
     * @var callback
     */
    protected $process;

    /**
     * @var int
     */
    protected $maxWidth;

    /**
     * @var int
     */
    protected $maxHeight;

    /**
     * @inheritDoc
     */
    protected function store($file, $path)
    {
        $image = $this->getImage($file->getRealPath());
        
        /** @var Size $size */
        $size = $image->getSize();
        
        $this->getDiskInstance()->put($path, $image->encode());
        
        return new Image($this, $path, $file, 
                         $size->getWidth(), $size->getHeight());
    }

    /**
     * @inheritDoc
     */
    public function get($path, array $options)
    {
        if ( ! $stream = parent::get($path, $options)) {
            return false;
        }

        $width = array_get($options, 'width');
        $height = array_get($options, 'height');

        if ( ! $width && ! $height) {
            return $stream;
        }

        return ResizedImageStream::fromFileStream($stream, $width, $height);
    }

    /**
     * @inheritDoc
     */
    public function supportsMime($mime)
    {
        if ( ! $this->validateMime($mime, static::$supportedImages)) {
            return false;
        }

        return parent::supportsMime($mime);
    }

    /**
     * @param ImageManager $imageManager
     *
     * @return $this
     */
    public function setImageManager(ImageManager $imageManager)
    {
        $this->imageManager = $imageManager;

        return $this;
    }

    /**
     * @param string $file
     *
     * @return InterventionImage
     */
    protected function getImage($file)
    {
        $image = $this->imageManager->make($file);
        
        if ($this->maxHeight || $this->maxWidth) {
            $image = $image->resize(
                $this->maxWidth, $this->maxHeight, function ($constraint) {
                    $constraint->upsize();
                    $constraint->aspectRatio();
                }
            );
        }

        if ($this->process) {
            $image = call_user_func($this->process, $image);
        }
        
        return $image;
    }

    /**
     * @return callable
     */
    public function getProcess()
    {
        return $this->process;
    }

    /**
     * @param callable $process
     */
    public function setProcess($process)
    {
        $this->process = $process;
    }

    /**
     * @return int
     */
    public function getMaxWidth()
    {
        return $this->maxWidth;
    }

    /**
     * @param int $maxWidth
     */
    public function setMaxWidth($maxWidth)
    {
        $this->maxWidth = $maxWidth;
    }

    /**
     * @return int
     */
    public function getMaxHeight()
    {
        return $this->maxHeight;
    }

    /**
     * @param int $maxHeight
     */
    public function setMaxHeight($maxHeight)
    {
        $this->maxHeight = $maxHeight;
    }
    
}