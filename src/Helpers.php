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
     * @param string $key
     * @param string $default
     *
     * @return string
     */
    public static function translate($key, $default = null)
    {
        return app('cruddy.lang')->translate($key, $default);
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

    /**
     * @param string $value
     *
     * @return null|string
     */
    public static function processString($value)
    {
        if ( ! is_string($value)) return $value;

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    /**
     * @param array $data
     *
     * @return mixed
     */
    public static function firstOrNull(array $data)
    {
        return empty($data) ? null : reset($data);
    }

    /**
     * @param string $value
     *
     * @return array
     */
    public static function splitKeywords($value)
    {
        return preg_split('/\s/', $value, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * @param $value
     *
     * @return string
     */
    public static function simplifyRichText($value)
    {
        if ( ! $value) {
            return null;
        }

        // Strip any tags and limit length
        $value = strip_tags($value);

        if (mb_strlen($value) > 300) {
            $value = mb_substr($value, 255).'...';
        }

        return $value;
    }
}