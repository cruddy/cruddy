<?php

if (!function_exists('humanize'))
{
    function humanize($value) {

        return str_replace("_", " ", $value);
    }
}