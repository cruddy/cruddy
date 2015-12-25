<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Kalnoy\Cruddy\Schema\Fields\BaseTextField;

/**
 * Basic string field type.
 *
 * @link http://digitalbush.com/projects/masked-input-plugin
 *
 * @property string $mask
 * @method $this mask(StringField $value)
 *
 * @since 1.0.0
 */
class StringField extends BaseTextField {

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'mask' => $this->get('mask'),

        ] + parent::toArray();
    }
}