<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Kalnoy\Cruddy\Schema\Fields\BaseField;

/**
 * Code editor based on {@link http://http://ace.c9.io/ ACE}.
 */
class Code extends BaseField {

    /**
     * @inheritdoc
     *
     * @var string
     */
    protected $class = 'Code';

    /**
     * @inheritdoc
     *
     * @var string
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
        $this->theme = $theme;

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
        $this->height = $height;

        return $this;
    }

    /**
     * @inheritdoc
     *
     * @return  array
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