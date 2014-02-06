<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

/**
 * Markdown editor.
 */
class Markdown extends Code {

    /**
     * @inheritdoc
     *
     * @var string
     */
    protected $class = 'Markdown';

    /**
     * @inheritdoc
     *
     * @var string
     */
    protected $type = 'markdown';

}