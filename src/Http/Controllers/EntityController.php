<?php

namespace Kalnoy\Cruddy\Http\Controllers;

use Exception;
use Illuminate\Config\Repository as Config;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Kalnoy\Cruddy\ActionException;
use Kalnoy\Cruddy\Data;
use Kalnoy\Cruddy\Entity;
use Kalnoy\Cruddy\EntityNotFoundException;
use Kalnoy\Cruddy\Environment;
use Kalnoy\Cruddy\ModelNotFoundException;
use Kalnoy\Cruddy\OperationNotPermittedException;
use Kalnoy\Cruddy\Service\Validation\ValidationException;
use Psy\Util\Json;
use Symfony\Component\HttpFoundation\Response;

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
     * Initialize the controller.
     *
     * @param Environment $environment
     * @param Request $request
     * @param Config $config
     */
    public function __construct(Environment $environment)
    {
        $this->environment = $environment;

        $this->middleware('cruddy.exceptions');
        $this->middleware('cruddy.transaction', [ 'except' => [ 'show', 'index' ]]);
    }

    /**
     * Get a list of models of specified entity.
     *
     * @param Request $input
     * @param string $entity
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $input, $entity)
    {
        $entity = $this->resolve($entity, 'view');

        if ( ! $input->ajax()) return $this->loadingView();

        $options = $this->prepareSearchOptions($input->all());

        return new JsonResponse($entity->search($options));
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
     * @param Request $input
     * @param string $entity
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $input, $entity, $id)
    {
        $entity = $this->resolve($entity, 'view');

        if ( ! $input->ajax())
        {
            return redirect()->route('cruddy.index', [ $entity->getId(), 'id' => $id ]);
        }

        return new JsonResponse($entity->find($id));
    }

    /**
     * Create an entity instance.
     *
     * @param Request $input
     * @param string $entity
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $input, $entity)
    {
        $entity = $this->resolve($entity, 'create');

        $data = new Data($entity, $input->all());

        return $this->validateAndSave($entity, $data);
    }

    /**
     * @param Entity $entity
     * @param Data $data
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function validateAndSave(Entity $entity, Data $data)
    {
        $data->validate();

        $model = $data->save();

        return new JsonResponse($entity->extract($model));
    }

    /**
     * Update an entity instance.
     *
     * @param Request $input
     * @param string $entity
     * @param mixed $id
     * @param string $action
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $input, $entity, $id, $action = null)
    {
        $entity = $this->resolve($entity, 'update');

        $data = new Data($entity, $input->all(), $id);

        $data->setCustomAction($action);

        return $this->validateAndSave($entity, $data);
    }

    /**
     * Execute custom action on model.
     *
     * @param string $entity
     * @param $id
     * @param string $action
     *
     * @return \Illuminate\Http\Response
     */
    public function executeCustomAction($entity, $id, $action = null)
    {
        $entity = $this->resolve($entity, 'update');

        $data = new Data($entity, [], $id);

        $data->setCustomAction($action);

        $data->save();

        return new JsonResponse;
    }

    /**
     * Destroy a model.
     *
     * @param $entity
     * @param $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($entity, $id)
    {
        $entity = $this->resolve($entity, 'delete');

        return new JsonResponse(null, $entity->delete($id) ? Response::HTTP_OK : Response::HTTP_NOT_FOUND);
    }

    /**
     * Resolve an entity.
     *
     * @param string $id
     * @param string $action
     *
     * @return \Kalnoy\Cruddy\Entity
     */
    protected function resolve($id, $action)
    {
        $entity = $this->environment->entity($id);

        if ( ! $entity->isPermitted($action))
        {
            $message = trans("cruddy::app.forbidden.{$action}", [ 'entity' => $id ]);

            throw new OperationNotPermittedException($message);
        }

        return $entity;
    }

    /**
     * @return mixed
     */
    private function loadingView()
    {
        return view('cruddy::loading');
    }

}