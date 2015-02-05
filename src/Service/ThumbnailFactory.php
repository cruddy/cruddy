<?php

namespace Kalnoy\Cruddy\Service;

use Carbon\Carbon;
use Intervention\Image\ImageManager;
use Illuminate\Cache\Repository;

/**
 * Thumbnail factory for creating smaller images.
 * 
 * @since 1.0.0
 */
class ThumbnailFactory {

    /**
     * The cache lifetime in minutes.
     *
     * @var int
     */
    public $lifetime = 43200;

    /**
     * @var \Intervention\Image\ImageManager
     */
    protected $image;

    /**
     * @var \Illuminate\Cache\Repository
     */
    protected $cache;

    /**
     * Init factory.
     *
     * @param \Intervention\Image\ImageManager $image
     */
    public function __construct(ImageManager $image, Repository $cache)
    {
        $this->image = $image;
        $this->cache = $cache;
    }

    /**
     * Generate thumbnail.
     *
     * @param $src
     * @param $width
     * @param $height
     *
     * @throws \RuntimeException
     * @return Thumbnail
     */
    public function make($src, $width, $height)
    {
        if ($src === null || $width === null && $height === null) throw new \RuntimeException;

        $key = md5($src.'w'.$width.'h'.$height);

        if ( ! $image = $this->cache->get($key))
        {
            $image = $this->image->make($src);

            $image->resize($width, $height, function ($constraint)
            {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            if ($width === null) $width = $image->getWidth();
            if ($height === null) $height = $image->getHeight();

            $image->resizeCanvas($width, $height, 'center', false, 'ffffff');
            
            $image = (string)$image->encode('jpg');

            $this->cache->put($key, $image, $this->lifetime);
        }

        return new Thumbnail($key, $image, $this->expires(), 'image/jpeg');
    }

    /**
     * Get expires time.
     *
     * @return \Carbon\Carbon
     */
    public function expires()
    {
        return Carbon::now()->addMinutes($this->lifetime);
    }
}