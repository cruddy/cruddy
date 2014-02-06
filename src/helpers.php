<?php

namespace Kalnoy\Cruddy;

if (!function_exists('Kalnoy\Cruddy\prettify_string'))
{
    /**
     * @param $value
     *
     * @return mixed
     */
    function prettify_string($value) {

        return str_replace("_", " ", $value);
    }
}

if (!function_exists('Kalnoy\Cruddy\try_trans'))
{
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
}

if (!function_exists('Kalnoy\Cruddy\extract_list'))
{
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
}