<?php namespace Kalnoy\Cruddy;

function prettify_string($value) {

    return str_replace("_", " ", $value);
}

function try_trans($key)
{
    return strpos($key, '.') !== false ? trans($key) : $key;
}

function extract_list($value, $default = array('*'))
{
    if (empty($value)) return $default;

    return explode(',', $value);
}