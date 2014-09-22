<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Kalnoy\Cruddy\Schema\Fields\BaseField;

/**
 * Code editor based on {@link http://http://ace.c9.io/ ACE}.
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
     * The editor height.
     *
     * @var  int
     */
    public $height = 250;

    /**
     * The editor theme.
     *
     * Default value is set globally in the package configuration.
     *
     * @var  string
     */
    public $theme;

    /**
     * The editor mode.
     *
     * @var  string
     */
    public $mode;

    /**
     * {@inheritdoc}
     */
    public function process($value)
    {
        $value = trim($value);

        return $value === '' ? null : $value;
    }

    /**
     * Set the editor mode.
     *
     * @param string $value
     *
     * @return $this
     */
    public function mode($value)
    {
        $this->mode = $value;

        return $this;
    }

    /**
     * Set the editor theme.
     *
     * @param string $value
     *
     * @return $this
     */
    public function theme($value)
    {
        $this->theme = $value;

        return $this;
    }

    /**
     * Set the height in pixels.
     *
     * @param int $value
     *
     * @return $this
     */
    public function height($value)
    {
        $this->height = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return
        [
            'height' => $this->height,
            'theme' => $this->theme,
            'mode' => $this->mode,

        ] + parent::toArray();
    }

}