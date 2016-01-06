<?php

namespace Kalnoy\Cruddy\Schema\Actions;

use Illuminate\Contracts\Support\Arrayable as ArrayableContract;

class Response implements ArrayableContract
{
    /**
     * @var bool
     */
    protected $successful;

    /**
     * @var string
     */
    protected $message;

    /**
     * Response constructor.
     *
     * @param string $message
     * @param bool $successful
     */
    public function __construct($message = null, $successful = true)
    {
        $this->message = $message;
        $this->successful = $successful;
    }

    /**
     * @param string $message
     *
     * @return Response
     */
    public static function failure($message = null)
    {
        return new static($message, false);
    }

    /**
     * @param string $message
     *
     * @return Response
     */
    public static function success($message = null)
    {
        return new static($message, true);
    }

    /**
     * @return bool
     */
    public function isSuccessful()
    {
        return $this->successful;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->getMessage();
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'successful' => $this->successful,
            'message' => $this->message,
        ];
    }
}