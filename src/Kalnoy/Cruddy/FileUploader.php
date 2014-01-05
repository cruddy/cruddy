<?php  namespace Kalnoy\Cruddy;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class FileUploader uploads a file or a set of files and returns its filename against a root.
 *
 * I.e. if the root is `/var/www/public` and the path is `files` then file with name `myfile.zip` would be
 * uploaded at `/var/www/public/files/myfile.zip` and the result would be `/files/myfile.zip`.
 *
 * @package Kalnoy\Cruddy
 */
class FileUploader {

    /**
     * @var \Illuminate\Filesystem\Filesystem
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
    protected $path;

    /**
     * Whether it's going to be a few files.
     *
     * @var bool
     */
    protected $multiple = false;

    /**
     * Whether to keep original file names.
     *
     * @var bool
     */
    protected $keepNames = false;

    /**
     * @param $root
     * @param $path
     * @param $keepNames
     * @param $multiple
     */
    function __construct(Filesystem $file, $root, $path, $keepNames, $multiple)
    {
        $this->file = $file;
        $this->root = $root;
        $this->keepNames = $keepNames;
        $this->multiple = $multiple;
        $this->path = $path;
    }

    /**
     * Upload a file.
     *
     * $value can be either a string or UploadedFile.
     * The string is considered as old filename and returned as is.
     *
     * @param array|string|UploadedFile $value
     *
     * @return array|string
     */
    public function upload($value)
    {
        if ($this->multiple) return $this->uploadMany($value);

        return is_string($value) ? $value : $this->uploadFile($value);
    }

    /**
     * Upload multiple files.
     *
     * @param $files
     *
     * @return array
     */
    protected function uploadMany($files)
    {
        $files = (array)$files;

        foreach ($files as $i => $file)
        {
            if ($file instanceof UploadedFile) $files[$i] = $this->uploadFile($file);
        }

        return $files;
    }

    /**
     * Move UploadedFile to a required directory and return path.
     *
     * @param UploadedFile $file
     *
     * @return string
     */
    protected function uploadFile(UploadedFile $file)
    {
        $path = $this->root.'/'.$this->path;
        $name = $this->getName($file).'.'.$file->getClientOriginalExtension();

        // If file already exists, we force random name.
        if ($this->file->exists($path.'/'.$name)) $name = $this->getName();

        $target = $file->move($path, $name);

        return strtr($target,
        [
            $this->root => '',
            '\\' => '/',
        ]);
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
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return bool
     */
    public function getMultiple()
    {
        return $this->multiple;
    }

    /**
     * @return bool
     */
    public function getKeepNames()
    {
        return $this->keepNames;
    }
}