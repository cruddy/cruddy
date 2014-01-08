<?php namespace Kalnoy\Cruddy\Service;

use Carbon\Carbon;
use Intervention\Image\ImageCache;

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

        $image->encode('jpg');

        $expires = Carbon::createFromTimestamp(time() + $this->lifetime * 60);

        return new Thumbnail($image->checksum(), $image->get($this->lifetime), $expires, 'image/jpeg');
    }
}