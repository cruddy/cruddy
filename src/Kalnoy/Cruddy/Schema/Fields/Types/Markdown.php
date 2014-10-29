<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

/**
 * Markdown editor.
 *
 * @since 1.0.0
 */
class Markdown extends Code {

    /**
     * {@inheritdoc}
     */
    protected $class = 'Cruddy.Fields.Markdown';

    /**
     * {@inheritdoc}
     */
    protected $type = 'markdown';

}