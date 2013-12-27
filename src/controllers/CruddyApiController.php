<?php namespace Kalnoy\Cruddy;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Config;
use Exception;

class CruddyApiController extends ApiController {

    const E_MODEL_ERROR = 'MODEL_ERROR';

    const E_VALIDATION = 'VALIDATION';

    /**
     * The cruddy environment.
     *
     * @var Environment
     */
    protected $cruddy;

    /**
     * Initialize the controller.
     *
     * @param Environment $cruddy
     */
    public function __construct(Environment $cruddy)
    {
        $this->cruddy = $cruddy;
    }

    public function entity($type)
    {
        return $this->resolve($type, function ($entity) {

            return $this->success($entity);
        });
    }

    public function index($type)
    {
        return $this->resolve($type, function ($entity) {

            if (!$entity->canView()) return $this->forbidden();

            $order = array();

            if (Input::has('order_by'))
            {
                $order = array(
                    Input::get('order_by') => Input::get('order_dir', 'asc'),
                );
            }

            $filters = Input::get('filters') ?: array();

            $columns = $entity->columns();
            $paginated = $entity->search($filters, $order);

            // Filter columns down to only those that are required
            $onlyColumns = Input::get('columns');

            if (!empty($onlyColumns))
            {
                $onlyColumns = explode(',', $onlyColumns);
                $onlyColumns = array_combine($onlyColumns, $onlyColumns);

                $columns = $columns->filter(function ($col) use ($onlyColumns) {

                    return isset($onlyColumns[$col->getId()]);
                });
            }

            $items = $columns->collectionData($paginated->getItems());
            $paginated->setItems($items);

            return $this->success($paginated);
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
        return $this->resolve($type, function ($entity) use ($id) {

            if (!$entity->canView()) return $this->forbidden();

            $instance = $entity->find($id);

            if ($instance === null) return $this->notFound();

            $fields = $entity->fields();
            $instanceData = $fields->data($instance);
            $related = $entity->related()->data($instance);
            $runtime = $fields->runtime($instance);

            return $this->success(compact('instanceData', 'related', 'runtime'));
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
        return $this->resolveSafe($type, function ($entity) {

            if (!$entity->canCreate()) return $this->forbidden();

            $input = Input::get($entity->getId(), array());

            $instance = $entity->create($input);

            if (false === $instance)
            {
                $errors = $entity->errors();

                return $this->failure(400, self::E_VALIDATION, $errors);
            }

            return $this->success($entity->fields()->data($instance));
        });
    }

    /**
     * Update an entity instance.
     *
     * @param  string $type
     *
     * @return Response
     */
    public function update($type, $id)
    {
        return $this->resolveSafe($type, function ($entity) use ($id) {

            if (!$entity->canUpdate()) return $this->forbidden();

            if (null === $instance = $entity->find($id))
            {
                return $this->notFound();
            }

            $input = Input::get($entity->getId(), array());

            if (false === $entity->update($instance, $input))
            {
                $errors = $entity->errors();

                return $this->failure(400, self::E_VALIDATION, $errors);
            }

            return $this->success($entity->fields()->data($instance));
        });
    }

    public function destroy($type, $id)
    {
        return $this->resolveSafe($type, function ($entity) use ($id) {

            if (!$entity->canDelete()) return $this->forbidden();

            $instance = $entity->find($id);

            if (false == $instance) return $this->notFound();

            return $instance->delete() ? $this->success() : $this->failure();
        });
    }

    /**
     * Resolve a model type and execute callback.
     *
     * @param  string   $id
     * @param  Callable $callback
     * @param  bool     $transaction
     *
     * @return Response
     */
    protected function resolve($id, Callable $callback, $transaction = false)
    {
        try
        {
            $entity = $this->cruddy->entity($id);

            if ($transaction)
            {
                $conn = $entity->form()->instance()->getConnection();

                return $conn->transaction(function ($conn) use ($entity, $callback) {

                    return $callback($entity, $conn);
                });
            }

            return $callback($entity);
        }
        catch (Exception $e)
        {
            if (Config::get('app.debug')) throw $e;

            return $this->failure(500, self::E_MODEL_ERROR, $e->getMessage());
        }
    }

    /**
     * Resolve a model type and execute callback enclosed in transaction.
     *
     * @param  string   $id
     * @param  Callable $callback
     *
     * @return Response
     */
    protected function resolveSafe($id, Callable $callback)
    {
        return $this->resolve($id, $callback);
    }
}