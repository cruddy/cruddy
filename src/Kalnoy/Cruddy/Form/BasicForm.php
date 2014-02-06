<?php

namespace Kalnoy\Cruddy\Form;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Kalnoy\Cruddy\Repo\RepositoryInterface;
use Kalnoy\Cruddy\Service\Validation\ValidableInterface;
use Exception;

class BasicForm implements FormInterface {

    /**
     * The repository.
     *
     * @var \Kalnoy\Cruddy\Repo\RepositoryInterface
     */
    protected $repo;

    /**
     * Validators factory.
     *
     * @var ValidableInterface
     */
    protected $validate;

    /**
     * Init a model form.
     *
     * @param \Kalnoy\Cruddy\Repo\RepositoryInterface              $repo
     * @param \Kalnoy\Cruddy\Service\Validation\ValidableInterface $validator
     */
    public function __construct(RepositoryInterface $repo, ValidableInterface $validator)
    {
        $this->repo = $repo;
        $this->validate = $validator;
    }

    /**
     * @inheritdoc
     *
     * @param array $input
     * @param bool  $dryRun
     *
     * @return \Illuminate\Database\Eloquent\Model
     * 
     * @throws \Kalnoy\Cruddy\Service\Validation\ValidationException
     */
    public function create(array $input, $dryRun = false)
    {
        $this->validate->beforeCreate($input);

        return $dryRun ?: $this->repo->create($input);
    }

    /**
     * @inheritdoc
     *
     * @param int   $id
     * @param array $input
     * @param bool  $dryRun
     *
     * @return \Illuminate\Database\Eloquent\Model
     * 
     * @throws \Kalnoy\Cruddy\Service\Validation\ValidationException
     * @throws \Kalnoy\Cruddy\ModelNotFoundException
     */
    public function update($id, array $input, $dryRun = false)
    {
        $this->validate->beforeUpdate($input);

        return $dryRun ?: $this->repo->update($id, $input);
    }

    /**
     * @inheritdoc
     *
     * @param int|array $ids
     *
     * @return int
     */
    public function delete($ids)
    {
        return $this->repo->delete($ids);
    }

    /**
     * Get error messages.
     *
     * @return null|\Illuminate\Support\MessageBag
     */
    public function errors()
    {
        return $this->validate->errors();
    }
}