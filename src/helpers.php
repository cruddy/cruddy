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