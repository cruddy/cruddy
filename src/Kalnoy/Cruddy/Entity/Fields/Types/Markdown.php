<?php

namespace Kalnoy\Cruddy\Entity\Fields\Types;

/**
 * Markdown editor.
 */
class Markdown extends Code {

    /**
     * @inheritdoc
     *
     * @return  string
     */
    public function getJavaScriptClass()
    {
        return 'Markdown';
    }

}