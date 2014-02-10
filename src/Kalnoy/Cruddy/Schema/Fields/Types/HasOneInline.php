<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Kalnoy\Cruddy\Schema\Fields\InlineRelation;

/**
 * This field will allow to inlinely edit related model.
 */
class HasOneInline extends InlineRelation {

    /**
     * @inheritdoc
     *
     * @var string
     */
    protected $class = 'HasOne';

    /**
     * @inheritdoc
     *
     * @param array $input
     *
     * @return array
     */
    public function processInput(array $input)
    {
        extract($input);

        $action = empty($id) ? 'create' : 'update';

        list($attributes, $relatedData) = $this->reference->process($action, $attributes);

        return compact('id', 'action', 'attributes', 'relatedData');
    }

    /**
     * @inhertidoc
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param array                               $data
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function save(Eloquent $model, array $data)
    {
        $repo = $this->reference->getRepository();

        extract($data);

        $attributes += $this->getConnectingAttributes($model);

        switch ($action)
        {
            case 'create': $innerModel = $repo->create($attributes); break;
            case 'update': $innerModel = $repo->update($id, $attributes); break;
        }

        // Save related items for inner model.
        $this->reference->saveRelated($innerModel, $relatedData);
    }

    /**
     * @inheritdoc
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return array
     */
    public function getConnectingAttributes(Eloquent $model)
    {
        return [ $this->relation->getPlainForeignKey() => $model->getKey() ];
    }

}