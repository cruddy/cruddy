<?php

namespace Kalnoy\Cruddy\Http\Responses;

use Intervention\Image\ImageManager;
use Kalnoy\Cruddy\Service\Files\ResizedImageStream;
use Symfony\Component\HttpFoundation\Request;

class ResizedImageResponse extends FileStreamResponse
{
    /**
     * @var ResizedImageStream
     */
    protected $file;

    /**
     * ResizedImageResponse constructor.
     *
     * @param ResizedImageStream $file
     * @param int $status
     * @param array $headers
     */
    public function __construct(ResizedImageStream $file, $status = 200, $headers = [])
    {
        parent::__construct($file, $status, $headers);
    }

    /**
     * @inheritdoc
     */
    public function prepare(Request $request): static
    {
        if ($this->prepared) {
            return $this;
        }

        parent::prepare($request);

        if ( ! $this->isSuccessful() || $this->isEmpty()) {
            return $this;
        }

        $image = $this->getImageManager()->make($this->file->getStream());

        $width = $this->file->getWidth();
        $height = $this->file->getHeight();

        $image->resize($width, $height, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        $this->content = $image->encode($this->file->getMime());

        $this->headers->set('Content-Length', strlen($this->content));

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function sendContent(): static
    {
        echo $this->content;

        return $this;
    }

    /**
     * @return ImageManager
     */
    protected function getImageManager()
    {
        return app(ImageManager::class);
    }

}