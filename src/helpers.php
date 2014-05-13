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

if (!function_exists('Kalnoy\Cruddy\ucfirst'))
{
    /**
     * `uncfirst` for unicode strings.
     *
     * @param string $str
     *
     * @return string
     */
    function ucfirst($str)
    {
        $char = mb_strtoupper(mb_substr($str, 0, 1));

        return $char . mb_substr($str, 1);
    }
}