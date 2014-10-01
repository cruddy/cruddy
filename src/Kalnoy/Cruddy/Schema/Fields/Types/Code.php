<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Kalnoy\Cruddy\Schema\Fields\BaseField;

/**
 * Code editor based on {@link http://http://ace.c9.io/ ACE}.
 *
 * @method $this height(int $value)
 * @method $this theme(string $value)
 * @method $this mode(string $value)
 * @property int $height
 * @property string $theme
 * @property string $mode
 *
 * @since 1.0.0
 */
class Code extends BaseField {

    /**
     * {@inheritdoc}
     */
    protected $class = 'Code';

    /**
     * {@inheritdoc}
     */
    protected $type = 'code';

    /**
     * {@inheritdoc}
     */
    public function process($value)
    {
        $value = trim($value);

        return $value === '' ? null : $value;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return
        [
            'height' => $this->get('height', 250),
            'theme' => $this->get('theme'),
            'mode' => $this->get('mode'),

        ] + parent::toArray();
    }

}