<?php namespace Kalnoy\Cruddy;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Config;
use Exception;
use Kalnoy\Cruddy\Entity\Entity;
use Kalnoy\Cruddy\Service\Permissions\PermissionsInterface;
use Kalnoy\Cruddy\Service\Validation\ValidationException;

class EntityApiController extends ApiController {

    const E_MODEL_ERROR = 'MODEL_ERROR';

    const E_VALIDATION = 'VALIDATION';

    /**
     * The cruddy environment.
     *
     * @var Environment
     */
    protected $cruddy;

    /**
     * @var PermissionsInterface
     */
    protected $permissions;

    /**
     * Initialize the controller.
     *
     * @param Environment          $cruddy
     * @param PermissionsInterface $permissions
     */
    public function __construct(Environment $cruddy, PermissionsInterface $permissions)
    {
        $this->cruddy = $cruddy;
        $this->permissions = $permissions;

        $this->beforeFilter('cruddy.auth');
    }

    /**
     * Get a schema of the specified entity.
     *
     * @param string $type
     *
     * @return Response
     */
    public function schema($type)
    {
        return $this->resolve($type, 'view', function ($entity) {

            return $this->success($entity);
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

            $order = array();

            if (Input::has('order_by'))
            {
                $order =
                [
                    Input::get('order_by') => Input::get('order_dir', 'asc'),
                ];
            }

            $filters = Input::get('filters') ?: array();
            $search = Input::get('q');
            $columns = extract_list(Input::get('columns'));

            $paginated = $entity->all($search, $filters, $order, $columns);

            return $this->success($paginated);
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

            $query = Input::get('q');
            $columns = extract_list(Input::get('columns'));

            return $this->success($entity->search($query, $columns));
        });
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
        return $this->resolve($type, 'view', function ($entity) use ($id) {

            $instance = $entity->findOrFail($id);

            $model = $entity->fields()->data($instance);
            $related = $entity->related()->data($instance);

            return $this->success(compact('model', 'related'));
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
        return $this->resolveSafe($type, 'create', function ($entity) {

            $model = $entity->create(Input::all());

            return $this->success($entity->fields()->data($model));
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
        return $this->resolveSafe($type, 'update', function ($entity) use ($id) {

            $model = $entity->findOrFail($id);

            $entity->update($model, Input::all());

            return $this->success($entity->fields()->data($model));
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
        return $this->resolveSafe($type, 'delete', function ($entity) use ($id) {

            $model = $entity->findOrFail($id);

            return $model->delete() ? $this->success() : $this->failure();
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

            // Check whether current user is allowed to perform specified action
            if ($this->cant($method, $entity)) return $this->forbidden();

            if ($transaction)
            {
                $conn = $entity->form()->instance()->getConnection();

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
            return $this->notFound();
        }

        catch (ModelNotFoundException $e)
        {
            return $this->notFound();
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
     * Get whether authenticated user can't access given action.
     *
     * @param        $method
     * @param Entity $entity
     * @return bool
     */
    protected function cant($method, Entity $entity)
    {
        $method = 'can'.ucfirst($method);

        return !$this->permissions->$method($entity);
    }
}