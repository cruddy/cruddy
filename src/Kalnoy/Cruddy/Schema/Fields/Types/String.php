<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Kalnoy\Cruddy\Schema\Fields\BaseTextField;

/**
 * Basic string field type.
 *
 * @link http://digitalbush.com/projects/masked-input-plugin
 *
 * @property string $mask
 * @method $this mask(string $value)
 *
 * @since 1.0.0
 */
class String extends BaseTextField {

    /**
     * @inheritdoc
     *
     * @var string
     */
    protected $type = 'string';

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return
        [
            'mask' => $this->get('mask'),

        ] + parent::toArray();
    }
}