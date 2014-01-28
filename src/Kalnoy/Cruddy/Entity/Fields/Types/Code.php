<?php

namespace Kalnoy\Cruddy\Entity\Fields\Types;

use Kalnoy\Cruddy\Entity\Fields\AbstractField;

/**
 * Code editor.
 */
class Code extends AbstractField {

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

    /**
     * @inheritdoc
     *
     * @return  string
     */
    public function getJavaScriptClass()
    {
        return 'Code';
    }

}