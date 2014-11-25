<?php

namespace Kalnoy\Cruddy\Controllers;

use Exception;
use Illuminate\Config\Repository as Config;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Kalnoy\Cruddy\Entity;
use Kalnoy\Cruddy\EntityNotFoundException;
use Kalnoy\Cruddy\Environment;
use Kalnoy\Cruddy\ModelNotFoundException;
use Kalnoy\Cruddy\OperationNotPermittedException;
use Kalnoy\Cruddy\Service\Validation\ValidationException;
use Redirect;
use Response;
use Log;

/**
 * This controller handles requests to the api.
 *
 * @since 1.0.0
 */
class EntityController extends Controller {

    /**
     * The cruddy environment.
     *
     * @var Environment
     */
    protected $environment;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Request
     */
    protected $request;

    /**
     * Initialize the controller.
     *
     * @param Environment $environment
     * @param Request $request
     * @param Config $config
     */
    public function __construct(Environment $environment, Request $request, Config $config)
    {
        $this->environment = $environment;
        $this->config = $config;
        $this->request = $request;
    }

    /**
     * Get a list of models of specified entity.
     *
     * @param string $entity
     *
     * @return Response
     */
    public function index($entity)
    {
        return $this->resolve($entity, 'view', function (Request $request, Entity $entity)
        {
            if ( ! $request->ajax()) return $this->loadingView();

            $options = $this->prepareSearchOptions($request->all());

            return Response::make($entity->search($options));
        });
    }

    /**
     * Prepare search options that received form the input.
     *
     * @param array $options
     *
     * @return array
     */
    protected function prepareSearchOptions(array $options)
    {
        if (isset($options['order_by']) && isset($options['order_dir']))
        {
            $options['order'] = [$options['order_by'] => $options['order_dir']];
        }

        return $options;
    }

    /**
     * View an item of specific entity type.
     *
     * @param  string $entity
     * @param  int $id
     *
     * @return Response
     */
    public function show($entity, $id)
    {
        return $this->resolve($entity, 'view', function (Request $request, Entity $entity) use ($id)
        {
            if ( ! $request->ajax())
            {
                return Redirect::route('cruddy.index', [ $entity->getId(), 'id' => $id ]);
            }

            return Response::make($entity->find($id));
        });
    }

    /**
     * Create an entity instance.
     *
     * @param  string $entity
     *
     * @return Response
     */
    public function store($entity)
    {
        return $this->resolveSafe($entity, 'create', function (Request $request, Entity $entity)
        {
            return Response::make($entity->create($request->all()));
        });
    }

    /**
     * Update an entity instance.
     *
     * @param  string $entity
     *
     * @return Response
     */
    public function update($entity, $id)
    {
        return $this->resolveSafe($entity, 'update', function (Request $request, Entity $entity) use ($id)
        {
            return Response::make($entity->update($id, $request->all()));
        });
    }

    /**
     * Destroy a model.
     *
     * @param $entity
     * @param $id
     *
     * @return Response
     */
    public function destroy($entity, $id)
    {
        return $this->resolveSafe($entity, 'delete', function (Request $request, Entity $entity) use ($id)
        {
            return Response::make('{}', $entity->delete($id) ? 200 : 404);
        });
    }

    /**
     * Resolve a model type and execute callback.
     *
     * @param  string   $id
     * @param  string   $method
     * @param  Callable $callback
     * @param  bool     $transaction
     *
     * @throws \Exception
     * @return Response
     */
    protected function resolve($id, $method, Callable $callback, $transaction = false)
    {
        try
        {
            $entity = $this->environment->entity($id);

            if ( ! $this->environment->isPermitted($method, $entity))
            {
                $message = $this->environment->translate("cruddy::app.forbidden.{$method}", [':entity' => $id]);

                throw new OperationNotPermittedException($message);
            }

            if ($transaction)
            {
                $connection = $entity->getRepository()->newModel()->getConnection();

                return $connection->transaction(function () use ($entity, $callback)
                {
                    return $callback($this->request, $entity);
                });
            }

            return $callback($this->request, $entity);
        }

        catch (ValidationException $e)
        {
            return Response::make($e->getErrors(), 400);
        }

        catch (EntityNotFoundException $e)
        {
            return $this->responseError($e->getMessage(), 404);
        }

        catch (ModelNotFoundException $e)
        {
            return $this->responseError('Specified model not found.', 404);
        }

        catch (OperationNotPermittedException $e)
        {
            return $this->responseError($e->getMessage(), 403);
        }

        catch (Exception $e)
        {
            $message = 'Internal server error.';

            if ($this->config->get('app.debug'))
            {
                Log::error($e);

                $message = get_class($e).': '.$e->getMessage();
            }

            return $this->responseError($message);
        }
    }

    /**
     * Resolve a model type and execute callback enclosed in transaction.
     *
     * @param  string   $id
     * @param  string   $method
     * @param  Callable $callback
     *
     * @return Response
     */
    protected function resolveSafe($id, $method, Callable $callback)
    {
        return $this->resolve($id, $method, $callback, true);
    }

    /**
     * @return mixed
     */
    private function loadingView()
    {
        return Response::view('cruddy::loading');
    }

    /**
     * @param $error
     * @param $status
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function responseError($error, $status = 500)
    {
        return Response::json(compact('error'), $status);
    }

}