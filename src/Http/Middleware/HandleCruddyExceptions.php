<?php

namespace Kalnoy\Cruddy\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Kalnoy\Cruddy\EntityNotFoundException;
use Kalnoy\Cruddy\ModelNotFoundException;
use Kalnoy\Cruddy\AccessDeniedException;
use Kalnoy\Cruddy\Service\Validation\ValidationException;

class HandleCruddyExceptions
{
    /**
     * @param Request $request
     * @param Closure $next
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            return $next($request);
        }

        catch (ValidationException $e) {
            return response($e->getErrors(),
                            Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        catch (EntityNotFoundException $e) {
            return $this->responseError($e->getMessage(),
                                        Response::HTTP_NOT_FOUND);
        }

        catch (ModelNotFoundException $e) {
            return $this->responseError('Specified model not found.',
                                        Response::HTTP_NOT_FOUND);
        }

        catch (AccessDeniedException $e) {
            return $this->responseError($e->getMessage(),
                                        Response::HTTP_FORBIDDEN);
        }

        catch (Exception $e) {
            app('Illuminate\Contracts\Debug\ExceptionHandler')->report($e);

            return $this->responseError($this->convertException($e));
        }
    }

    /**
     * @param $error
     * @param $status
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function responseError($error, $status = 500)
    {
        return new JsonResponse(compact('error'), $status);
    }

    /**
     * @param $e
     *
     * @return string
     */
    protected function convertException($e)
    {
        return class_basename($e).': '.$e->getMessage();
    }

}