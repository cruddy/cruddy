<?php

namespace Kalnoy\Cruddy\Form\Fields;

use Illuminate\Support\Arr;
use Kalnoy\Cruddy\Service\Files\FileStorage;
use Kalnoy\Cruddy\Service\Files\StorageManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * File input field.
 *
 * @package Kalnoy\Cruddy\Form\Fields
 */
class File extends BaseField
{
    /**
     * @var string
     */
    public $storageName;

    /**
     * @var bool
     */
    public $multiple = false;

    /**
     * @param $value
     *
     * @return $this
     */
    public function storeTo($value)
    {
        $this->storageName = $value;

        return $this;
    }

    /**
     * @return $this
     */
    public function multiple()
    {
        $this->multiple = true;

        return $this;
    }

    /**
     * @param string $path
     * @param array $params
     *
     * @return string
     */
    public function urlToFile($path, array $params = [])
    {
        $path = $this->getStorageName().'/'.ltrim($path, '\\/');
        $info = pathinfo($path);

        $params['storage_path'] = $info['dirname'];
        $params['storage_file'] = $info['basename'];

        return url()->route('cruddy.files.show', $params);
    }

    /**
     * @inheritdoc
     *
     * @return string[]|string
     */
    public function getModelValue($model)
    {
        $value = parent::getModelValue($model);

        if ($this->isMultiple()) {
            return is_array($value) ? $value : [ ];
        }

        return $value;
    }

    /**
     * @inheritdoc
     */
    protected function processValueBeforeSetting($value)
    {
        if (empty($value)) {
            return $this->isMultiple() ? [] : null;
        }

        return $this->isMultiple() ? (array)$value : $value;
    }

    /**
     * The name of the JavaScript class that is used to render this field.
     *
     * @return string
     */
    public function getUIModelClass()
    {
        return 'Cruddy.Fields.File';
    }

    /**
     * @return bool
     */
    public function isMultiple()
    {
        return $this->multiple;
    }

    /**
     * @return FileStorage
     */
    protected function getStorage()
    {
        return app(StorageManager::class)->storage($this->getStorageName());
    }

    /**
     * @return string
     */
    public function getStorageName()
    {
        return $this->storageName ?: 'files';
    }

    /**
     * @return null|string
     */
    public function getAccept()
    {
        $mime = $this->getStorage()->getMime();

        return $mime ? implode(',', $mime) : null;
    }

    /**
     * @inheritdoc
     */
    public function getConfig()
    {
        return [
            'multiple' => $this->isMultiple(),
            'storage' => $this->getStorageName(),
            'accept' => $this->getAccept(),

        ] + parent::getConfig();
    }
}