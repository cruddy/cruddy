<?php

namespace Kalnoy\Cruddy\Service;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class FileUploader uploads a file or a set of files and returns its filename against a root.
 *
 * I.e. if the root is `/var/www/public` and the path is `files` then file with name `myfile.zip` would be
 * uploaded at `/var/www/public/files/myfile.zip` and the result would be `/files/myfile.zip`.
 *
 * @since 1.0.0
 */
class FileUploader {

    /**
     * @var Filesystem
     */
    protected $file;

    /**
     * The root path that will be stripped off the full filename.
     *
     * Default is `public_path()`.
     *
     * @var string
     */
    protected $root;

    /**
     * The path to where files are uploaded against a root.
     *
     * Default is `files`.
     *
     * @var string
     */
    protected $path = 'files';

    /**
     * Whether to keep original file names.
     *
     * @var bool
     */
    protected $keepNames = false;

    /**
     * The list of uploaded files.
     *
     * @var array
     */
    protected $uploaded = [];

    /**
     * @param Filesystem $file
     */
    function __construct(Filesystem $file)
    {
        $this->file = $file;
        $this->root = \public_path();
    }

    /**
     * Upload a file.
     *
     * `$value` can be either a string or UploadedFile.
     * The string is considered as old filename and returned as is.
     *
     * @param array|string|UploadedFile $value
     *
     * @return array|string
     */
    public function upload($value)
    {
        if (is_array($value)) return $this->uploadMany($value);

        if (empty($value)) return null;

        return is_string($value) ? $value : $this->uploadFile($value);
    }

    /**
     * Upload multiple files.
     *
     * @param UploadedFile[] $files
     *
     * @return array
     */
    protected function uploadMany(array $files)
    {
        $result = [];

        foreach ($files as $i => $file)
        {
            if ($file instanceof UploadedFile)
            {
                if ($file = $this->uploadFile($file)) $result[] = $file;
            }
            else
            {
                $result[] = $file;
            }
        }

        return $result;
    }

    /**
     * Move UploadedFile to a required directory and return path.
     *
     * @param UploadedFile $file
     *
     * @return string|null
     */
    protected function uploadFile(UploadedFile $file)
    {
        if ( ! $file->isValid()) return null;

        $path = $this->root;

        if ($this->path) $path .= '/'.$this->path;

        $ext = '.' . $file->getClientOriginalExtension();
        $name = $this->getName($file);

        // If file already exists, we force random name.
        if ($this->file->exists($path . '/' . $name . $ext)) $name = $this->getName();

        $this->uploaded[] = $target = $file->move($path, $name . $ext);

        $target = strtr($target,
        [
            $this->root => '',
            '\\' => '/',
        ]);

        return substr($target, 1);
    }

    /**
     * Remove all uploaded files.
     *
     * @return $this
     */
    public function cancel()
    {
        $this->file->delete($this->uploaded);

        return $this;
    }

    /**
     * Get a name for a file.
     *
     * @param UploadedFile $file
     *
     * @return string
     */
    protected function getName(UploadedFile $file = null)
    {
        if ($this->keepNames && $file)
        {
            $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $name = Str::slug($name);

            // After sanitation name may become empty. In this case we simply generate a random name.
            if ('' !== $name) return $name;
        }

        return Str::random(6);
    }

    /**
     * Set the path to where files are stored relatively to the root.
     *
     * @param string $path
     *
     * @return $this
     */
    public function to($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Set the root directory.
     *
     * @param string $root
     *
     * @return $this
     */
    public function relativelyTo($root)
    {
        $this->root = $root;

        return $this;
    }

    /**
     * Makes uploader to keep original file names.
     *
     * @param bool $value
     *
     * @return $this
     */
    public function keepNames($value = true)
    {
        $this->keepNames = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return bool
     */
    public function getKeepNames()
    {
        return $this->keepNames;
    }
}