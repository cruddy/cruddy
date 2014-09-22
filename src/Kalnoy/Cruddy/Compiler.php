<?php

namespace Kalnoy\Cruddy;

use Illuminate\Filesystem\Filesystem;

class Compiler {

    /**
     * @var Repository
     */
    protected $entities;

    /**
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $file;

    /**
     * @var Lang
     */
    protected $lang;

    public function __construct(Repository $entities, Filesystem $file, Lang $lang)
    {
        $this->entities = $entities;
        $this->file = $file;
        $this->lang = $lang;
    }

    /**
     * Compile a schema.
     *
     * @return array
     */
    public function schema()
    {
        $filename = $this->filename();

        if ($this->file->exists($filename))
        {
            return unserialize($this->file->get($filename));
        }

        return $this->fresh();
    }

    /**
     * Get fresh schema.
     *
     * @return array
     */
    public function fresh($locale = null)
    {
        $currentLocale = $this->lang->getLocale();

        if ($locale !== null) $this->lang->setLocale($locale);

        $data = array_map(function ($item)
        {
            return $item->toArray();

        }, $this->entities->resolveAll());

        if ($locale !== null) $this->lang->setLocale($currentLocale);

        return $data;
    }

    /**
     * Compile a schema.
     *
     * @return void
     */
    public function compile($locales)
    {
        $locales = is_array($locales) ? $locales : func_get_args();

        if (empty($locales)) $locales = [ $this->lang->getLocale() ];

        $directory = pathinfo($this->filename(), PATHINFO_DIRNAME);

        if ( ! $this->file->isDirectory($directory))
        {
            $this->file->makeDirectory($directory);
        }

        foreach ($locales as $locale)
        {
            $filename = $this->filename($locale);

            $this->file->put($filename, serialize($this->fresh($locale)));
        }
    }

    /**
     * Clear compiled schema.
     *
     * @return void
     */
    public function clearCompiled()
    {
        $filename = $this->filename();

        $this->file->cleanDirectory(pathinfo($filename, PATHINFO_DIRNAME));
    }

    /**
     * Get a filename for compiled file.
     *
     * @return string
     */
    public function filename($locale = null)
    {
        if ($locale === null) $locale = $this->lang->getLocale();

        return base_path().'/bootstrap/cruddy/'.$locale.'.php';
    }

}