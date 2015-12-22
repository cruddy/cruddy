<?php

namespace Kalnoy\Cruddy;

class Helpers
{
    /**
     * @param string $value
     *
     * @return string
     */
    static public function prettifyString($value)
    {
        return str_replace("_", " ", $value);
    }

    /**
     * Try translate a key.
     *
     * @param $key
     *
     * @return string
     */
    static public function tryTranslate($key)
    {
        return app('cruddy.lang')->tryTranslate($key);
    }

    /**
     * `uncfirst` for unicode strings.
     *
     * @param string $str
     *
     * @return string
     */
    static public function ucfirst($str)
    {
        $char = mb_strtoupper(mb_substr($str, 0, 1));

        return $char.mb_substr($str, 1);
    }

    /**
     * @param string $id
     *
     * @return string
     */
    public static function labelFromId($id)
    {
        return static::ucfirst(static::prettifyString($id));
    }
}