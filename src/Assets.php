<?php

namespace Kalnoy\Cruddy;

/**
 * Assets manager.
 */
class Assets
{
    /**
     * The list of css files.
     *
     * @var array
     */
    protected $css = [ ];

    /**
     * The list of js files.
     *
     * @var array
     */
    protected $js = [ ];

    /**
     * Add extra css files.
     *
     * @param string|array $uri
     *
     * @return $this
     */
    public function css($uri)
    {
        $uri = is_array($uri) ? $uri : func_get_args();

        $this->css = array_merge($this->css, $uri);

        return $this;
    }

    /**
     * Add extra js files.
     *
     * @param string|array $uri
     *
     * @return $this
     */
    public function js($uri)
    {
        $uri = is_array($uri) ? $uri : func_get_args();

        $this->js = array_merge($this->js, $uri);

        return $this;
    }

    /**
     * Render scripts.
     *
     * @return string
     */
    public function scripts()
    {
        return implode("\r\n", array_map(function ($uri) {
            return "<script src='{$uri}'></script>";
        }, $this->js));
    }

    /**
     * Render styles.
     *
     * @return string
     */
    public function styles()
    {
        return implode("\r\n", array_map(function ($uri) {
            return "<link rel='stylesheet' href='{$uri}'>";
        }, $this->css));
    }

}