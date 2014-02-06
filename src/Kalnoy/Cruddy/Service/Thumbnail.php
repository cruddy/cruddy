<?php

namespace Kalnoy\Cruddy\Service;

use Illuminate\Http\Response;

class Thumbnail {

    /**
     * @var string
     */
    protected $data;

    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $mime;

    /**
     * @var \Carbon\Carbon
     */
    protected $expires;

    /**
     * @param $key
     * @param $data
     * @param $expires
     * @param $mime
     */
    function __construct($key, $data, $expires, $mime)
    {
        $this->data = $data;
        $this->key = $key;
        $this->mime = $mime;
        $this->expires = $expires;
    }

    /**
     * Make a response.
     *
     * @return Response
     */
    public function response()
    {
        $response = \Response::make($this->data);

        $response->setExpires($this->expires);
        $response->header('Content-type', $this->mime);

        return $response;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getMime()
    {
        return $this->mime;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return \Carbon\Carbon
     */
    public function getExpires()
    {
        return $this->expires;
    }
}