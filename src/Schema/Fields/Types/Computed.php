<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Kalnoy\Cruddy\Entity;
use Kalnoy\Cruddy\Schema\ComputedTrait;
use Kalnoy\Cruddy\Schema\Fields\BaseField;

/**
 * Computed field.
 *
 * @since 1.0.0
 */
class Computed extends BaseField
{
    use ComputedTrait;

    /**
     * @param Entity $form
     * @param string $id
     * @param string $accessor
     */
    public function __construct(Entity $form, $id, $accessor = null)
    {
        parent::__construct($form, $id);

        $this->accessor = $accessor;
    }

    /**
     * The name of the JavaScript class that is used to render this field.
     *
     * @return string
     */
    protected function getModelClass()
    {
        return 'Cruddy.Fields.Computed';
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingMode()
    {
        return self::MODE_NONE;
    }

}