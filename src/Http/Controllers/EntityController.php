<?php

namespace Kalnoy\Cruddy\Http\Controllers;

use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Kalnoy\Cruddy\AccessDeniedException;
use Kalnoy\Cruddy\BaseForm;
use Kalnoy\Cruddy\BaseFormData;
use Kalnoy\Cruddy\Entity;
use Kalnoy\Cruddy\EntityNotFoundException;
use Kalnoy\Cruddy\Environment;
use Kalnoy\Cruddy\ModelNotFoundException;
use Kalnoy\Cruddy\Service\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

/**
 * This controller handles requests to the api.
 *
 * @since 1.0.0
 */
class EntityController extends Controller
{
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
        if (isset($options['order_by']) && isset($options['order_dir'])) {
            $options['order'] = [ $options['order_by'] => $options['order_dir'] ];
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
     * @return JsonResponse
     */
    public function show(Request $input, $entity, $id)
    {
        $entity = $this->resolve($entity, Entity::READ);

        if ( ! $input->ajax()) {
            return redirect()->route('cruddy.index', [ $entity->getId(),
                                                       'id' => $id ]);
        }

        $this->assertIsEntity($entity);

        return new JsonResponse($entity->find($id));
    }

    /**
     * Create an entity instance.
     *
     * @param Request $input
     * @param string $form
     *
     * @return JsonResponse
     */
    public function store(Request $input, $form, $action = null)
    {
        $form = $this->resolve($form, Entity::CREATE);

        $data = $form->processInput($input->all());

        return $this->validateAndSave($form, $data, $action);
    }

    /**
     * @param BaseForm $form
     * @param BaseFormData $data
     * @param string $action
     *
     * @return JsonResponse
     */
    protected function validateAndSave(BaseForm $form, BaseFormData $data,
                                       $action = null
    ) {
        $data->validate();

        $model = $data->save();

        if ($form instanceof Entity && $action) {
            $actionResult = $this->executeActionOnModel($form, $action, $model);
        }

        $model = $form->extract($model);

        return new JsonResponse(compact('actionResult', 'model'));
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

        $this->assertIsEntity($entity);

        $data = $entity->processInput($input->all());

        $data->setId($id);

        return $this->validateAndSave($entity, $data, $action);
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
    public function executeCustomAction($entity, $id, $action)
    {
        $entity = $this->resolve($entity, Entity::UPDATE);

        $this->assertIsEntity($entity);

        $model = $entity->getRepository()->find($id);

        return $this->executeActionOnModel($entity, $action, $model);
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

        $this->assertIsEntity($entity);

        $status = $entity->delete($id)
            ? Response::HTTP_OK
            : Response::HTTP_NOT_FOUND;

        return new JsonResponse(null, $status);
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

        if ( ! $entity->isPermitted($action)) {
            $message = trans("cruddy::app.forbidden.{$action}",
                             [ 'entity' => $id ]);

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
    protected function assertIsEntity($entity)
    {
        if ( ! $entity instanceof Entity) {
            throw new \RuntimeException;
        }
    }

    /**
     * @param Entity $form
     * @param $action
     * @param $model
     *
     * @return mixed
     */
    protected function executeActionOnModel(Entity $form, $action, $model)
    {
        return $form->getActions()->execute($model, $action);
    }

    /**
     * @param string $method
     * @param array $parameters
     *
     * @return Response
     */
    public function callAction($method, $parameters)
    {
        try {
            return parent::callAction($method, $parameters);
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
            $this->reportException($e);

            return $this->responseError($this->convertException($e));
        }
    }

    /**
     * @param $error
     * @param $status
     *
     * @return JsonResponse
     */
    protected function responseError($error, $status = 500)
    {
        return new JsonResponse(compact('error'), $status);
    }

    /**
     * @param Exception $e
     *
     * @return string
     */
    protected function convertException($e)
    {
        return class_basename($e).': '.$e->getMessage();
    }

    /**
     * @param $e
     */
    protected function reportException($e)
    {
        if ($handler = app(ExceptionHandler::class)) {
            $handler->report($e);
        }
    }

}