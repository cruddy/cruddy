<?php

namespace Kalnoy\Cruddy\Service\Files;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Uploads files to the specified filesystem disk.
 *
 * @since 1.0.0
 */
class FileStorage
{
    /**
     * The file was not uploaded properly.
     */
    const ERR_INVALID_FILE = 1;

    /**
     * The file is to big.
     */
    const ERR_TOO_BIG = 2;

    /**
     * The file is not of required mime type.
     */
    const ERR_INVALID_MIME = 3;

    /**
     * File's extension is empty.
     */
    const ERR_EMPTY_EXTENSION = 4;

    /**
     * @var FilesystemManager
     */
    protected $filesystems;

    /**
     * @var FilesystemAdapter
     */
    private $diskInstance;

    /**
     * @var string
     */
    protected $disk;

    /**
     * Whether to keep original file names.
     *
     * @var bool
     */
    protected $keepNames = true;

    /**
     * @var array
     */
    protected $mime;

    /**
     * @var int
     */
    protected $size;

    /**
     * @param FilesystemManager $filesystems
     */
    public function __construct(FilesystemManager $filesystems)
    {
        $this->filesystems = $filesystems;
    }

    /**
     * Move UploadedFile to the specified filesystem disk and return a path
     * relatively to it.
     *
     * @param UploadedFile $file
     * @param string $path
     *
     * @return File
     */
    public function upload($file, $path = null)
    {
        $this->assertIsValidFile($file);

        $ext = strtolower($file->getClientOriginalExtension());

        $path = $this->getPath($path, $this->getFileName($file), $ext);

        return $this->store($file, $path);
    }

    /**
     * @param string $path
     * @param array $options
     *
     * @return bool|FileStream
     */
    public function get($path, array $options)
    {
        $disk = $this->getDiskInstance();

        if ( ! $path || ! ($stream = $disk->getDriver()->readStream($path))) {
            return false;
        }

        $size = $disk->size($path);
        $lastModified = $disk->lastModified($path);
        $mime = $disk->mimeType($path);

        return new FileStream($path, $stream, $size, $mime, $lastModified);
    }

    /**
     * @param string $fileName
     * @param $ext
     *
     * @return string
     */
    protected function getPath($path, $fileName, $ext)
    {
        $path = $path ? trim($path, '/\\').'/'.$fileName : $fileName;

        $ext = '.'.$ext;

        // Add some random letters if the file already exists
        while ($this->getDiskInstance()->exists($path.$ext)) {
            $path .= '-'. Str::random(3);
        }

        return $path.$ext;
    }

    /**
     * Get a name for a file.
     *
     * @param UploadedFile $file
     *
     * @return string
     */
    protected function getFileName(UploadedFile $file)
    {
        if ( ! $this->keepNames) {
            return Str::random(6);
        }

        $name = Str::ascii($file->getClientOriginalName());
        $name = Str::slug(pathinfo($name, PATHINFO_FILENAME));

        // After sanitation name may become empty.
        // In this case we simply generate a random name.
        return $name ? $name : Str::random(6);
    }

    /**
     * @param UploadedFile $file
     */
    protected function assertIsValidFile($file)
    {
        if ( ! $file || ! $file->isValid()) {
            throw new UploadException('Specified file is not valid.',
                                      self::ERR_INVALID_FILE);
        }

        if ($file->getError() == UPLOAD_ERR_INI_SIZE) {
            throw new UploadException('File is too big.', self::ERR_TOO_BIG);
        }

        if ($this->size && $file->getSize() > $this->size * 1024) {
            throw new UploadException('File is too big.', self::ERR_TOO_BIG);
        }

        if ( ! $this->supportsMime($file->getMimeType())) {
            throw new UploadException('Mime type is not allowed.',
                                      self::ERR_INVALID_MIME);
        }

        if ( ! $file->getClientOriginalExtension()) {
            throw new UploadException('File cannot have empty extension.',
                                      self::ERR_EMPTY_EXTENSION);
        }
    }

    /**
     * @param string $mime
     *
     * @return bool
     */
    public function supportsMime($mime)
    {
        if ( ! $this->mime) {
            return true;
        }

        if ( ! $mime) {
            return false;
        }

        return $this->validateMime($mime, $this->mime);
    }

    /**
     * @param string $mime
     * @param array $allowed
     *
     * @return bool
     */
    protected function validateMime($mime, array $allowed)
    {
        foreach ($allowed as $type) {
            if (Str::is($type, $mime)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function setDisk($value)
    {
        $this->disk = $value;

        return $this;
    }

    /**
     * Set whether uploader should keep original file names.
     *
     * @param bool $value
     *
     * @return $this
     */
    public function setKeepNames($value)
    {
        $this->keepNames = $value;

        return $this;
    }

    /**
     * Set valid mime types for this storage.
     *
     * @param string $value
     *
     * @return $this
     */
    public function setMime($value)
    {
        if (is_string($value)) {
            $value = preg_split('/\s,/', $value, -1, PREG_SPLIT_NO_EMPTY);
        }

        $this->mime = $value;

        return $this;
    }

    /**
     * Set the maximum size of the file in kbytes.
     *
     * @param int $value
     *
     * @return $this
     */
    public function setSize($value)
    {
        $this->size = $value;

        return $this;
    }

    /**
     * @return FilesystemAdapter
     */
    public function getDiskInstance()
    {
        if ($this->diskInstance) return $this->diskInstance;

        return $this->diskInstance = $this->filesystems->disk($this->disk);
    }

    /**
     * @return string
     */
    public function getDisk()
    {
        return $this->disk;
    }

    /**
     * @return bool
     */
    public function getKeepNames()
    {
        return $this->keepNames;
    }

    /**
     * @return array
     */
    public function getMime()
    {
        return $this->mime;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param UploadedFile $file
     * @param string $path
     *
     * @return File
     */
    protected function store($file, $path)
    {
        $this->getDiskInstance()->put($path,
                                      file_get_contents($file->getRealPath()));

        return new File($this, $path, $file);
    }
}