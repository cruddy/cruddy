<?php

namespace Kalnoy\Cruddy\Controllers;

use Exception;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Kalnoy\Cruddy\Entity;
use Kalnoy\Cruddy\EntityNotFoundException;
use Kalnoy\Cruddy\Environment;
use Kalnoy\Cruddy\ModelNotFoundException;
use Kalnoy\Cruddy\OperationNotPermittedException;
use Kalnoy\Cruddy\Service\Validation\ValidationException;

/**
 * This controller handles requests to the api.
 *
 * @since 1.0.0
 */
class EntityController {

    /**
     * The cruddy environment.
     *
     * @var Environment
     */
    protected $cruddy;

    /**
     * @var Config
     */
    protected $config;

    /**
     * Initialize the controller.
     *
     * @param Environment $cruddy
     * @param Config $config
     */
    public function __construct(Environment $cruddy, Config $config)
    {
        $this->cruddy = $cruddy;
        $this->config = $config;
    }

    /**
     * Get a list of models of specified entity.
     *
     * @param string $entity
     *
     * @return Response
     */
    public function index(Request $request, $entity)
    {
        return $this->resolve($entity, 'view', function (Entity $entity) use ($request)
        {
            if ( ! $request->ajax()) return $this->loadingView();

            $options = $this->prepareSearchOptions($request->all());

            return response($entity->search($options));
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
    public function show(Request $request, $entity, $id)
    {
        return $this->resolve($entity, 'view', function (Entity $entity) use ($request, $id)
        {
            return $request->ajax() ? response($entity->find($id)) : $this->loadingView();
        });
    }

    /**
     * Create an entity instance.
     *
     * @param  string $entity
     *
     * @return Response
     */
    public function store(Request $request, $entity)
    {
        return $this->resolveSafe($entity, 'create', function (Entity $entity) use ($request)
        {
            return response($entity->create($request->all()));
        });
    }

    /**
     * Update an entity instance.
     *
     * @param  string $entity
     *
     * @return Response
     */
    public function update(Request $request, $entity, $id)
    {
        return $this->resolveSafe($entity, 'update', function (Entity $entity) use ($id, $request)
        {
            return response($entity->update($id, $request->all()));
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
        return $this->resolveSafe($entity, 'delete', function (Entity $entity) use ($id)
        {
            $deleted = $entity->delete($id);

            return response(compact('deleted'));
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
            $entity = $this->cruddy->entity($id);

            if ( ! $this->cruddy->isPermitted($method, $entity))
            {
                $message = $this->cruddy->translate("cruddy::app.forbidden.{$method}", [':entity' => $id]);

                throw new OperationNotPermittedException($message);
            }

            if ($transaction)
            {
                $conn = $entity->getRepository()->newModel()->getConnection();

                return $conn->transaction(function ($conn) use ($entity, $callback)
                {
                    return $callback($entity, $conn);
                });
            }

            return $callback($entity);
        }

        catch (ValidationException $e)
        {
            return response($e->getErrors(), 400);
        }

        catch (EntityNotFoundException $e)
        {
            return response($e->getMessage(), 404);
        }

        catch (ModelNotFoundException $e)
        {
            return response('Specified model not found.', 404);
        }

        catch (OperationNotPermittedException $e)
        {
            return response($e->getMessage(), 403);
        }

        catch (Exception $e)
        {
            if ($this->config->get('app.debug')) throw $e;

            return response('Internal server error.', 500);
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
     * @return \Illuminate\View\View
     */
    protected function loadingView()
    {
        return view('cruddy::loading');
    }
}