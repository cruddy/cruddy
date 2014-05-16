<?php

namespace Kalnoy\Cruddy\Service;

use Carbon\Carbon;
use Intervention\Image\ImageCache;

/**
 * Thumbnail factory for creating smaller images.
 * 
 * @since 1.0.0
 */
class ThumbnailFactory {

    /**
     * The cache lifetime.
     *
     * One month by default.
     *
     * @var int
     */
    public $lifetime = 43200;

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

        $image = with(new ImageCache)->make($src);

        // Resize keeping aspect ratio
        $image = $image->resize($width, $height, true);

        // If both width and height provided resize canvas to this size
        if ($width !== null && $height !== null)
        {
            $image->resizeCanvas($width, $height, 'center', false, 'ffffff');
        }

        $expires = Carbon::createFromTimestamp(time() + $this->lifetime * 60);

        $image = $image->get($this->lifetime, true);

        return new Thumbnail($image->cachekey, $image->encode(), $expires, $image->mime);
    }
}