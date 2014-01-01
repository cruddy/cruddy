<?php

if (!function_exists('humanize'))
{
    function humanize($value) {

        return str_replace("_", " ", $value);
    }
}

if (!function_exists('try_trans'))
{
    function try_trans($key)
    {
        return strpos($key, '.') !== false ? trans($key) : $key;
    }
}

if (!function_exists('extract_list'))
{
    function extract_list($value, $default = array('*'))
    {
        if (empty($value)) return $default;

        return extract(',', $value);
    }
}