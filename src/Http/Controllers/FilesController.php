<?php

namespace Kalnoy\Cruddy\Http\Controllers;

use Kalnoy\Cruddy\Http\Responses\FileStreamResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Kalnoy\Cruddy\Http\Responses\ResizedImageResponse;
use Kalnoy\Cruddy\Service\Files\ResizedImageStream;
use Kalnoy\Cruddy\Service\Files\StorageManager;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;

class FilesController extends Controller
{
    /**
     * @var StorageManager
     */
    protected $storageManager;

    /**
     * FilesController constructor.
     *
     * @param StorageManager $storageManager
     */
    public function __construct(StorageManager $storageManager)
    {
        $this->storageManager = $storageManager;
    }

    /**
     * @param string $path
     *
     * @return mixed
     */
    public function index($path)
    {
        $storage = $this->parsePath($path);

        $storageInstance = $this->storageManager->storage($storage);

        $files = $storageInstance->getDiskInstance()->getDriver()->listContents($path);

        return response()->json(compact('storage', 'files'));
    }

    /**
     * @param Request $input
     * @param $path
     *
     * @return mixed
     */
    public function store(Request $input, $path)
    {
        $storage = $this->parsePath($path);

        $storageInstance = $this->storageManager->storage($storage);

        try {
            $path = $storageInstance->upload($input->file('file'), $path)->getPath();

            return response()->json(compact('storage', 'path'));
        } catch (UploadException $e) {
            $data = [ 'message' => $e->getMessage(), 'code' => $e->getCode() ];

            return response()->json($data, 422);
        }
    }

    /**
     * @param Request $input
     * @param string $path
     * @param string $file
     *
     * @return Response
     */
    public function show(Request $input, $path, $file)
    {
        $storage = $this->parsePath($path);

        $storageInstance = $this->storageManager->storage($storage);

        if ( ! $file = $storageInstance->get($path.'/'.$file, $input->all())) {
            return abort(404);
        }

        if ($file instanceof ResizedImageStream) {
            return new ResizedImageResponse($file);
        }

        return new FileStreamResponse($file);
    }

    /**
     * @param string $path
     *
     * @return \Kalnoy\Cruddy\Service\Files\FileStorage
     */
    protected function parsePath(&$path)
    {
        if (false !== $pos = strpos($path, '/')) {
            $storage = substr($path, 0, $pos);
            $path = substr($path, $pos + 1);
        } else {
            $storage = $path;
            $path = '';
        }

        return $storage;
    }
}