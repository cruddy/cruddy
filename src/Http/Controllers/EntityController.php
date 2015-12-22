<?php

namespace Kalnoy\Cruddy\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Kalnoy\Cruddy\BaseForm;
use Kalnoy\Cruddy\BaseFormData;
use Kalnoy\Cruddy\Entity;
use Kalnoy\Cruddy\EntityData;
use Kalnoy\Cruddy\Environment;
use Kalnoy\Cruddy\AccessDeniedException;
use Kalnoy\Cruddy\Http\Middleware\HandleCruddyExceptions;
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
     */
    public function __construct(Environment $environment)
    {
        $this->environment = $environment;

        $this->middleware(HandleCruddyExceptions::class);
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
        $entity = $this->resolve($entity, Entity::READ);

        if ( ! $input->ajax()) return $this->loadingView();

        $options = $this->prepareSearchOptions($input->all());

        return new JsonResponse($entity->index($options));
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
        $entity = $this->resolve($entity, Entity::READ);

        if ( ! $input->ajax())
        {
            return redirect()->route('cruddy.index', [ $entity->getId(), 'id' => $id ]);
        }

        $this->assertEntity($entity);

        return new JsonResponse($entity->find($id));
    }

    /**
     * Create an entity instance.
     *
     * @param Request $input
     * @param string $form
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $input, $form)
    {
        $form = $this->resolve($form, Entity::CREATE);

        $data = $form->processInput($input->all());

        return $this->validateAndSave($form, $data);
    }

    /**
     * @param BaseForm $form
     * @param BaseFormData $data
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function validateAndSave(BaseForm $form, BaseFormData $data)
    {
        $data->validate();

        $model = $data->save();

        return new JsonResponse($form->extract($model));
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
        $entity = $this->resolve($entity, Entity::UPDATE);

        $this->assertEntity($entity);

        $data = $entity->processInput($input->all());

        $data->setId($id);
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
        $entity = $this->resolve($entity, Entity::UPDATE);

        $this->assertEntity($entity);

        $data = new EntityData($entity, []);

        $data->setId($id);
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
        $entity = $this->resolve($entity, Entity::DELETE);

        $this->assertEntity($entity);

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

            throw new AccessDeniedException($message);
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

    /**
     * @param $entity
     */
    protected function assertEntity($entity)
    {
        if ( ! $entity instanceof Entity) throw new \RuntimeException;
    }
}