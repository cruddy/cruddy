<?php

namespace Kalnoy\Cruddy\Controllers;

use Illuminate\Contracts\Support\ArrayableInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;
use Illuminate\Routing\Controller;

/**
 * Base api controller.
 *
 * @since 1.0.0
 */
class ApiController extends Controller {

    /**
     * Everything is ok.
     */
    const S_OK = 'ok';

    /**
     * Everything is not ok.
     */
    const S_FAIL = 'fail';

    /**
     * Not found error code.
     */
    const E_NOT_FOUND = 'NOT_FOUND';

    /**
     * Missing method code.
     */
    const E_MISSING_METHOD = 'MISSING_METHOD';

    /**
     * Forbidden error code.
     */
    const E_FORBIDDEN = 'FORBIDDEN';

    /**
     * Exception thrown error code.
     */
    const E_EXCEPTION = 'EXCEPTION';

    /**
     * Get success response.
     *
     * @param  mixed $data
     *
     * @return Response
     */
    protected function success($data = null)
    {
        if ($data instanceof ArrayableInterface)
        {
            $data = $data->toArray();
        }

        $status = self::S_OK;

        return Response::json(compact('status', 'data'));
    }

    /**
     * Get a collection response.
     *
     * @param  Collection $collection
     *
     * @return Response
     */
    protected function collection(Collection $collection)
    {
        $status = self::S_OK;
        $count = count($collection);
        $data = $collection->toArray();

        return Response::json(compact('status', 'count', 'data'));
    }

    /**
     * Get failed response.
     *
     * @param  int    $code
     * @param  string $error
     * @param  mixed  $data
     *
     * @return Response
     */
    protected function failure($code = 500, $error = null, $data = null)
    {
        $status = self::S_FAIL;

        if ($data instanceof ArrayableInterface) $data = $data->toArray();

        return Response::json(compact('status', 'error', 'data'), $code);
    }

    /**
     * Get not found response.
     *
     * @param string $message
     *
     * @return Response
     */
    protected function notFound($message = null)
    {
        return $this->failure(404, self::E_NOT_FOUND, $message);
    }

    /**
     * Return 403 status.
     *
     * @param string $message
     *
     * @return Response
     */
    protected function forbidden($message = null)
    {
        return $this->failure(403, self::E_FORBIDDEN, $message);
    }

    /**
     * Handle missing method.
     *
     * @param  array $parameters
     *
     * @return Response
     */
    public function missingMethod($parameters = array())
    {
        return $this->failure(405, self::E_MISSING_METHOD);
    }
}