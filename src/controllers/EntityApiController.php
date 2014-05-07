<?php

namespace Kalnoy\Cruddy;

use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Config;
use Kalnoy\Cruddy\Entity;
use Kalnoy\Cruddy\Service\Validation\ValidationException;

class EntityApiController extends ApiController {

    const E_VALIDATION = 'VALIDATION';

    /**
     * The cruddy environment.
     *
     * @var \Kalnoy\Cruddy\Environment
     */
    protected $cruddy;

    /**
     * Initialize the controller.
     */
    public function __construct()
    {
        $this->cruddy = app('cruddy');
        $authFilter = $this->cruddy->config('auth_filter');

        if ($authFilter) $this->beforeFilter($authFilter);

        $this->beforeFilter(function()
        {
            \Event::fire('clockwork.controller.start');
        });

        $this->afterFilter(function()
        {
            \Event::fire('clockwork.controller.end');
        });
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
        return $this->resolve($entity, 'view', function ($entity) {

            $options = $this->prepareSearchOptions(Input::all());

            return $this->success($entity->search($options));
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
        return $this->resolve($entity, 'view', function ($entity) use ($id) 
        {
            return $this->success($entity->find($id));
        });
    }

    /**
     * Create an entity instance.
     *
     * @param  string $entity
     *
     * @return Response
     */
    public function create($entity)
    {
        return $this->resolveSafe($entity, 'create', function ($entity)
        {
            $attributes = Input::all();
            $id = null;

            return $this->success($entity->processAndSave(compact('id', 'attributes')));
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
        return $this->resolveSafe($entity, 'update', function ($entity) use ($id)
        {
            $attributes = Input::all();

            return $this->success($entity->processAndSave(compact('id', 'attributes')));
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
        return $this->resolveSafe($entity, 'delete', function ($entity) use ($id)
        {
            return $entity->delete($id) > 0 ? $this->success() : $this->failure();
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
            return $this->failure(400, self::E_VALIDATION, $e->getErrors());
        }

        catch (EntityNotFoundException $e)
        {
            return $this->notFound($e->getMessage());
        }

        catch (ModelNotFoundException $e)
        {
            return $this->notFound('ModelNotFoundException');
        }

        catch (OperationNotPermittedException $e)
        {
            return $this->forbidden($e->getMessage());
        }

        catch (Exception $e)
        {
            if (Config::get('app.debug')) throw $e;

            return $this->failure(500, self::E_EXCEPTION, $e->getMessage());
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
}