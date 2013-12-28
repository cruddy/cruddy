<?php namespace App\Service\Form;

abstract class AbstractForm {

    /**
     * Get form errors.
     *
     * @return \Illuminate\Support\MessageBag
     */
    abstract function errors();
}