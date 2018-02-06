<?php

namespace Kalnoy\Cruddy\Http\Responses;

use Carbon\Carbon;
use Kalnoy\Cruddy\Service\Files\FileStream;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FileStreamResponse extends Response
{
    /**
     * @var FileStream
     */
    protected $file;

    protected $prepared = false;

    /**
     * FileStreamResponse constructor.
     *
     * @param FileStream $file
     * @param int $status
     * @param array $headers
     */
    public function __construct(FileStream $file, $status = 200, $headers = [])
    {
        parent::__construct('', $status, $headers);

        $this->file = $file;

        $this->setPublic();
    }

    /**
     * @inheritdoc
     */
    public function prepare(Request $request)
    {
        if ($this->prepared) {
            return $this;
        }

        if ($lastModified = $this->file->getLastModified()) {
            $lastModified = Carbon::createFromTimestamp($lastModified);
            $modifiedSince = $request->headers->getDate('If-Modified-Since');

            if ($modifiedSince && $lastModified <= $modifiedSince) {
                return $this->setNotModified();
            }

            $this->setLastModified($lastModified);
        }

        $this->headers->set('Content-Type', $this->getMimeType());
        $this->headers->set('Content-Length', $this->file->getSize());

        $this->prepared = true;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function sendContent()
    {
        if ( ! $this->isSuccessful()) {
            return parent::sendContent();
        }

        $dest = fopen('php://output', 'wb');
        $src = $this->file->getStream();

        stream_copy_to_stream($src, $dest);

        fclose($dest);
        fclose($src);

        return $this;
    }

    /**
     * @return string
     */
    protected function getMimeType()
    {
        return $this->file->getMime() ?: 'application/octet-stream';
    }

}