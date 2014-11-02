<?php

namespace Kalnoy\Cruddy\Contracts;

use Illuminate\Support\Contracts\ArrayableInterface;

interface Entry extends ArrayableInterface {

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
     * @return \Kalnoy\Cruddy\Entity
     */
    public function getEntity();

}