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

        $this->beforeFilter('cruddy.auth');

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
     * @param string $type
     *
     * @return Response
     */
    public function index($type)
    {
        return $this->resolve($type, 'view', function ($entity) {

            $options = $this->prepareSearchOptions(Input::all());

            return $this->success($entity->search($options));
        });
    }

    /**
     * Search models of specified entity.
     *
     * @param $type
     *
     * @return Response
     */
    public function search($type)
    {
        return $this->resolve($type, 'view', function ($entity) {

            $options = $this->prepareSearchOptions(Input::all());

            $options['simple'] = true;

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
     * @param  string $type
     * @param  int $id
     *
     * @return Response
     */
    public function show($type, $id)
    {
        return $this->resolve($type, 'view', function ($entity) use ($id) 
        {
            return $this->success($entity->find($id));
        });
    }

    /**
     * Create an entity instance.
     *
     * @param  string $type
     *
     * @return Response
     */
    public function create($type)
    {
        return $this->resolveSafe($type, 'create', function ($entity)
        {
            return $this->success($entity->create(Input::all()));
        });
    }

    /**
     * Update an entity instance.
     *
     * @param  string $type
     * @param  int    $id
     *
     * @return Response
     */
    public function update($type, $id)
    {
        return $this->resolveSafe($type, 'update', function ($entity) use ($id)
        {
            return $this->success($entity->update($id, Input::all()));
        });
    }

    /**
     * Destroy a model.
     *
     * @param $type
     * @param $id
     *
     * @return Response
     */
    public function destroy($type, $id)
    {
        return $this->resolveSafe($type, 'delete', function ($entity) use ($id)
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

            if ( ! $this->permitted($method, $entity))
            {
                $message = $this->cruddy->translate("cruddy::app.forbidden.{$method}", ['entity' => $id]);

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

    /**
     * Get whether authenticated user is permitted to execute specified action.
     *
     * @param string                $method
     * @param \Kalnoy\Cruddy\Entity $entity
     * 
     * @return bool
     */
    protected function permitted($method, Entity $entity)
    {
        $method = 'can'.ucfirst($method);

        return $this->cruddy->getPermissions()->$method($entity);
    }
}