<?php namespace Kalnoy\Cruddy;

/**
 * @param $value
 *
 * @return mixed
 */
function prettify_string($value) {

    return str_replace("_", " ", $value);
}

/**
 * Try translate a key.
 *
 * @param $key
 *
 * @return string
 */
function try_trans($key)
{
    return strpos($key, '.') !== false ? trans($key) : $key;
}

/**
 * Explode a list of items separated by comma.
 *
 * @param string $value
 * @param array  $default
 *
 * @return array
 */
function extract_list($value, $default = ['*'])
{
    if (empty($value)) return $default;

    return explode(',', $value);
}