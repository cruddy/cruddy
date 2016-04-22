<?php

namespace Kalnoy\Cruddy;

use Illuminate\Filesystem\Filesystem;

class Compiler
{
    /**
     * @var Repository
     */
    protected $entities;

    /**
     * @var Filesystem
     */
    protected $file;

    /**
     * @var Lang
     */
    protected $lang;

    /**
     * @var string
     */
    protected $basePath;

    /**
     * @param Repository $entities
     * @param Filesystem $file
     * @param Lang $lang
     * @param $basePath
     */
    public function __construct(Repository $entities, Filesystem $file,
                                Lang $lang, $basePath
    ) {
        $this->entities = $entities;
        $this->file = $file;
        $this->lang = $lang;
        $this->basePath = $basePath;

        $this->ensureDirectory();
    }

    /**
     * Compile a schema.
     *
     * @return array
     */
    public function schema()
    {
        $filename = $this->filename();

        if ($this->file->exists($filename)) {
            return unserialize($this->file->get($filename));
        }

        return $this->fresh();
    }

    /**
     * Get fresh schema.
     *
     * @param string $locale
     *
     * @return array
     */
    public function fresh($locale = null)
    {
        if ($locale !== null) {
            $currentLocale = $this->lang->getLocale();
            
            $this->lang->setLocale($locale);
        }

        $data = $this->entities->resolveAll();

        $data = array_map(function ($item) { return $item->toArray(); }, $data);

        if ($locale !== null) {
            $this->lang->setLocale($currentLocale);
        }

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

        foreach ($locales as $locale) {
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
        if ($this->file->isDirectory($this->basePath)) {
            $this->file->cleanDirectory($this->basePath);
        }
    }

    /**
     * Get a filename for compiled file.
     *
     * @param string $locale
     *
     * @return string
     */
    public function filename($locale = null)
    {
        if ($locale === null) $locale = $this->lang->getLocale();

        return $this->basePath.'/'.$locale.'.php';
    }

    /**
     * Make sure that base path exists.
     */
    protected function ensureDirectory()
    {
        if ( ! $this->file->isDirectory($this->basePath)) {
            $this->file->makeDirectory($this->basePath);
        }
    }
}