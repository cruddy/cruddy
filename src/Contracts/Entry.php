<?php

namespace Kalnoy\Cruddy\Contracts;

use Illuminate\Contracts\Support\Arrayable;

interface Entry extends Arrayable {

    /**
     * Get the attribute identifier.
     *
     * @return string
     */
    public function getId();

    /**
     * @return string
     */
    public function getFullyQualifiedId();

    /**
     * Get an owning entity.
     *
     * @return \Kalnoy\Cruddy\BaseForm
     */
    public function getEntity();

}